<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegulationController extends Controller
{
    public function index(Request $request)
    {
        $query = Regulation::active();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('number', 'like', '%' . $request->search . '%');
            });
        }

        $regulations = $query->orderByDesc('year')->paginate(15)->withQueryString();
        $categories = Regulation::active()->select('category')->distinct()->pluck('category');

        return view('regulations.index', compact('regulations', 'categories'));
    }

    public function show(Regulation $regulation)
    {
        return view('regulations.show', compact('regulation'));
    }

    public function create()
    {
        $categories = Regulation::active()->select('category')->distinct()->pluck('category');

        return view('regulations.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('regulations', 'public');
        }

        unset($data['file']);
        Regulation::create($data);

        return redirect()->route('regulations.index')
            ->with('success', 'Peraturan/UU berhasil ditambahkan.');
    }

    public function edit(Regulation $regulation)
    {
        return view('regulations.edit', compact('regulation'));
    }

    public function update(Request $request, Regulation $regulation)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('file')) {
            if ($regulation->file_path) {
                Storage::disk('public')->delete($regulation->file_path);
            }
            $data['file_path'] = $request->file('file')->store('regulations', 'public');
        }

        unset($data['file']);
        $regulation->update($data);

        return redirect()->route('regulations.index')
            ->with('success', 'Peraturan/UU berhasil diperbarui.');
    }

    public function destroy(Regulation $regulation)
    {
        if ($regulation->file_path) {
            Storage::disk('public')->delete($regulation->file_path);
        }

        $regulation->delete();

        return redirect()->route('regulations.index')
            ->with('success', 'Peraturan/UU berhasil dihapus.');
    }

    public function preview(Regulation $regulation)
    {
        if (!$regulation->file_path || !Storage::disk('public')->exists($regulation->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($regulation->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $regulation->title . '.pdf"',
        ]);
    }

    public function download(Regulation $regulation)
    {
        if (!$regulation->file_path || !Storage::disk('public')->exists($regulation->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $fullPath = Storage::disk('public')->path($regulation->file_path);
        return response()->download($fullPath, $regulation->title . '.pdf');
    }
}
