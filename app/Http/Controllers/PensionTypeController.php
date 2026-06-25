<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\PensionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PensionTypeController extends Controller
{
    public function index()
    {
        $pensionTypes = PensionType::active()->get();

        return view('pension-types.index', compact('pensionTypes'));
    }

    public function show(PensionType $pensionType)
    {
        $templates = $pensionType->documentTemplates()
            ->orderBy('sort_order')
            ->get();

        return view('pension-types.show', compact('pensionType', 'templates'));
    }

    public function create()
    {
        return view('pension-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:pension_types,slug'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'templates' => ['nullable', 'array'],
            'templates.*.document_name' => ['required', 'string', 'max:255'],
            'templates.*.description' => ['nullable', 'string', 'max:500'],
            'templates.*.is_required' => ['nullable', 'boolean'],
        ]);

        $pensionType = PensionType::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'icon' => $request->icon,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Simpan berkas persyaratan
        $this->saveTemplates($pensionType, $request->input('templates', []));

        return redirect()->route('pension-types.show', $pensionType)
            ->with('success', 'Jenis pensiun berhasil ditambahkan.');
    }

    public function edit(PensionType $pensionType)
    {
        $templates = $pensionType->documentTemplates()->orderBy('sort_order')->get();

        return view('pension-types.edit', compact('pensionType', 'templates'));
    }

    public function update(Request $request, PensionType $pensionType)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'templates' => ['nullable', 'array'],
            'templates.*.document_name' => ['required', 'string', 'max:255'],
            'templates.*.description' => ['nullable', 'string', 'max:500'],
            'templates.*.is_required' => ['nullable', 'boolean'],
        ]);

        $pensionType->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Hapus semua template lama, ganti dengan yang baru
        $pensionType->documentTemplates()->delete();
        $this->saveTemplates($pensionType, $request->input('templates', []));

        return redirect()->route('pension-types.show', $pensionType)
            ->with('success', 'Jenis pensiun berhasil diperbarui.');
    }

    public function destroy(PensionType $pensionType)
    {
        if ($pensionType->applications()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus jenis pensiun yang sudah memiliki pengajuan.');
        }

        $pensionType->documentTemplates()->delete();
        $pensionType->delete();

        return redirect()->route('pension-types.index')
            ->with('success', 'Jenis pensiun berhasil dihapus.');
    }

    public function uploadTemplate(Request $request, DocumentTemplate $documentTemplate)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($documentTemplate->file_path) {
            Storage::disk('public')->delete($documentTemplate->file_path);
        }

        $documentTemplate->update([
            'file_path' => $request->file('file')->store('document-templates', 'public'),
        ]);

        return back()->with('success', 'Contoh format berhasil diunggah.');
    }

    public function previewTemplate(DocumentTemplate $documentTemplate)
    {
        if (!$documentTemplate->file_path || !Storage::disk('public')->exists($documentTemplate->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($documentTemplate->file_path);
        $filename = Str::slug($documentTemplate->document_name) . '.pdf';

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function downloadTemplate(DocumentTemplate $documentTemplate)
    {
        if (!$documentTemplate->file_path || !Storage::disk('public')->exists($documentTemplate->file_path)) {
            abort(404, 'File contoh tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($documentTemplate->file_path);
        $filename = Str::slug($documentTemplate->document_name) . '.pdf';

        return response()->download($fullPath, $filename);
    }

    // ── Helper: simpan daftar template berkas ─────────────
    private function saveTemplates(PensionType $pensionType, array $templates): void
    {
        foreach ($templates as $i => $tpl) {
            if (empty(trim($tpl['document_name'] ?? '')))
                continue;

            DocumentTemplate::create([
                'pension_type_id' => $pensionType->id,
                'document_name' => trim($tpl['document_name']),
                'description' => trim($tpl['description'] ?? ''),
                'is_required' => isset($tpl['is_required']) ? (bool) $tpl['is_required'] : true,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
