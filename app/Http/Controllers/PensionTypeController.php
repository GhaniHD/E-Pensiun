<?php

namespace App\Http\Controllers;

use App\Models\PensionType;
use Illuminate\Http\Request;

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

    // ── CRUD untuk TIK / SDM Kanwil ───────────────────────

    public function create()
    {
        return view('pension-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'slug'         => ['required', 'string', 'unique:pension_types,slug'],
            'description'  => ['nullable', 'string'],
            'icon'         => ['nullable', 'string', 'max:100'],
            'requirements' => ['nullable', 'array'],
            'is_active'    => ['boolean'],
        ]);

        PensionType::create($data);

        return redirect()->route('pension-types.index')
                         ->with('success', 'Jenis pensiun berhasil ditambahkan.');
    }

    public function edit(PensionType $pensionType)
    {
        return view('pension-types.edit', compact('pensionType'));
    }

    public function update(Request $request, PensionType $pensionType)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'icon'         => ['nullable', 'string', 'max:100'],
            'requirements' => ['nullable', 'array'],
            'is_active'    => ['boolean'],
        ]);

        $pensionType->update($data);

        return redirect()->route('pension-types.index')
                         ->with('success', 'Jenis pensiun berhasil diperbarui.');
    }

    public function destroy(PensionType $pensionType)
    {
        if ($pensionType->applications()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus jenis pensiun yang sudah memiliki pengajuan.');
        }

        $pensionType->delete();

        return redirect()->route('pension-types.index')
                         ->with('success', 'Jenis pensiun berhasil dihapus.');
    }
}
