<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // ── Upload berkas (bulk) ────────────────────────────────
    public function store(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKantor()) {
            abort(403, 'Hanya Staff KPKNL Pelayanan yang dapat mengunggah dokumen.');
        }

        if (
            !in_array($application->status->value, [
                ApplicationStatus::PEMBERKASAN->value,
                ApplicationStatus::UPLOAD->value,
                ApplicationStatus::PENGISIAN_FORM->value,
            ])
        ) {
            return back()->with('error', 'Berkas tidak dapat diupload pada tahap ini.');
        }

        $request->validate([
            'documents' => ['required', 'array', 'max:20'],
            'documents.*' => ['required', 'file', 'max:5120'],
            'document_names' => ['required', 'array'],
            'document_names.*' => ['required', 'string', 'max:255'],
        ]);

        $uploaded = 0;

        foreach ($request->file('documents') as $index => $file) {
            $docName = $request->document_names[$index] ?? $file->getClientOriginalName();
            $path = $file->store('documents/' . $application->id, 'local');

            Document::create([
                'application_id' => $application->id,
                'uploaded_by' => $user->id,
                'document_name' => $docName,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'kanwil_status' => 'pending',
            ]);

            $uploaded++;
        }

        if ($application->status === ApplicationStatus::PEMBERKASAN) {
            $application->advanceStatus($user, "Upload {$uploaded} berkas.");
        }

        return back()->with('success', "{$uploaded} berkas berhasil diupload.");
    }

    // ── Upload satu berkas (form individual) ───────────────
    public function storeSingle(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKantor()) {
            abort(403, 'Hanya Staff KPKNL Pelayanan yang dapat mengunggah dokumen.');
        }

        $request->validate([
            'document_name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $application->id, 'local');
        $existing = Document::where('application_id', $application->id)
            ->where('document_name', $request->document_name)
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($existing->file_path);
            $existing->update([
                'uploaded_by' => $user->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                // Reset semua status verifikasi saat re-upload
                'is_verified' => null,
                'verified_by' => null,
                'verified_at' => null,
                'rejection_note' => null,
                'kantor_check_status' => null,
                'kantor_check_note' => null,
                'kantor_checked_by' => null,
                'kantor_checked_at' => null,
                'kanwil_status' => 'pending',
            ]);
        } else {
            Document::create([
                'application_id' => $application->id,
                'uploaded_by' => $user->id,
                'document_name' => $request->document_name,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'kanwil_status' => 'pending',
            ]);
        }

        return back()->with('success', 'Berkas "' . $request->document_name . '" berhasil diupload.');
    }

    // ── Preview berkas ─────────────────────────────────────
    public function preview(Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isPensiunan() && $document->application->user_id !== $user->id) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file(Storage::disk('local')->path($document->file_path));
    }

    // ── Download berkas ────────────────────────────────────
    public function download(Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isPensiunan() && $document->application->user_id !== $user->id) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download(
            Storage::disk('local')->path($document->file_path),
            $document->original_filename
        );
    }

    // ── Hapus berkas ───────────────────────────────────────
    public function destroy(Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Hanya sdm_kantor yang boleh hapus berkas
        if (!$user->isSdmKantor()) {
            abort(403, 'Hanya Staff KPKNL Pelayanan yang dapat menghapus berkas.');
        }

        if ($document->is_verified) {
            return back()->with('error', 'Berkas yang sudah diverifikasi tidak dapat dihapus.');
        }

        $status = $document->application->status->value;
        $bisaEdit = in_array($status, [
            ApplicationStatus::PENGISIAN_FORM->value,
            ApplicationStatus::PEMBERKASAN->value,
            ApplicationStatus::UPLOAD->value,
        ]);

        if (!$bisaEdit) {
            return back()->with('error', 'Berkas tidak dapat dihapus karena pengajuan sudah dalam proses verifikasi.');
        }

        if ($document->uploaded_by !== $user->id) {
            abort(403);
        }

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }

    // ── [BULK] Simpan semua checklist sekaligus — KPKNL Pelayanan ──────
    public function bulkKantorCheck(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKantor()) {
            abort(403, 'Hanya Staff KPKNL Pelayanan yang dapat melakukan pengecekan.');
        }

        if ($application->status !== ApplicationStatus::VERIFIKASI_KPKNL) {
            return back()->with('error', 'Pengecekan hanya bisa dilakukan saat tahap Verifikasi KPKNL Pelayanan.');
        }

        $validated = $request->validate([
            'checks' => ['required', 'array', 'min:1'],
            'checks.*.document_id' => ['required', 'integer'],
            'checks.*.kantor_check_status' => ['required', 'in:lengkap_sesuai,lengkap_tidak_sesuai,tidak_lengkap'],
            'checks.*.kantor_check_note' => ['nullable', 'string', 'max:500'],
        ]);

        $errors = [];
        $saved = 0;

        foreach ($validated['checks'] as $check) {
            // Catatan wajib jika bukan "Lengkap & Sesuai"
            if ($check['kantor_check_status'] !== 'lengkap_sesuai' && empty($check['kantor_check_note'])) {
                $errors[] = 'Catatan wajib diisi untuk berkas yang berstatus bukan "Lengkap & Sesuai".';
                continue;
            }

            // Pastikan dokumen milik pengajuan ini
            $document = Document::where('id', $check['document_id'])
                ->where('application_id', $application->id)
                ->first();

            if (!$document)
                continue;

            $document->update([
                'kantor_check_status' => $check['kantor_check_status'],
                'kantor_check_note' => $check['kantor_check_note'] ?? null,
                'kantor_checked_by' => $user->id,
                'kantor_checked_at' => now(),
            ]);

            $saved++;
        }

        if (!empty($errors)) {
            return back()
                ->with('warning', implode(' ', $errors))
                ->with('info', "{$saved} berkas berhasil disimpan, beberapa dilewati karena catatan kosong.");
        }

        return back()->with('success', "Status {$saved} berkas berhasil disimpan sekaligus.");
    }

    // ── [BULK] Simpan semua verifikasi sekaligus — DJKN Kanwil ──────────
    public function bulkKanwilCheck(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKanwil()) {
            abort(403, 'Hanya Staff DJKN Kanwil yang dapat melakukan verifikasi.');
        }

        if ($application->status !== ApplicationStatus::VERIFIKASI_KANWIL) {
            return back()->with('error', 'Verifikasi hanya bisa dilakukan saat tahap Verifikasi DJKN Kanwil.');
        }

        $validated = $request->validate([
            'checks' => ['required', 'array', 'min:1'],
            'checks.*.document_id' => ['required', 'integer'],
            'checks.*.kanwil_status' => ['required', 'in:sesuai,tidak_sesuai'],
            'checks.*.rejection_note' => ['nullable', 'string', 'max:500'],
        ]);

        $errors = [];
        $saved = 0;

        foreach ($validated['checks'] as $check) {
            if ($check['kanwil_status'] === 'tidak_sesuai' && empty($check['rejection_note'])) {
                $errors[] = 'Catatan wajib diisi untuk berkas dengan status "Tidak Sesuai".';
                continue;
            }

            $document = Document::where('id', $check['document_id'])
                ->where('application_id', $application->id)
                ->first();

            if (!$document)
                continue;

            $document->update([
                'kanwil_status' => $check['kanwil_status'],
                'rejection_note' => $check['rejection_note'] ?? null,
                'verified_by' => $user->id,
                'verified_at' => now(),
                'is_verified' => $check['kanwil_status'] === 'sesuai',
            ]);

            $saved++;
        }

        if (!empty($errors)) {
            return back()
                ->with('warning', implode(' ', $errors))
                ->with('info', "{$saved} berkas berhasil diverifikasi, beberapa dilewati karena catatan kosong.");
        }

        return back()->with('success', "Verifikasi {$saved} berkas berhasil disimpan sekaligus.");
    }

    // ── [BARU] Checklist per dokumen — KPKNL Pelayanan (sdm_kantor) ──
    public function kantorCheck(Request $request, Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKantor()) {
            abort(403, 'Hanya Staff KPKNL Pelayanan yang dapat melakukan pengecekan tahap 1.');
        }

        $application = $document->application;

        if ($application->status !== ApplicationStatus::VERIFIKASI_KPKNL) {
            return back()->with('error', 'Pengecekan hanya bisa dilakukan saat tahap Verifikasi KPKNL Pelayanan.');
        }

        $validated = $request->validate([
            'kantor_check_status' => ['required', 'in:lengkap_sesuai,lengkap_tidak_sesuai,tidak_lengkap'],
            'kantor_check_note' => [
                'nullable',
                'string',
                'max:500',
                // Wajib diisi jika bukan "Lengkap & Sesuai"
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->kantor_check_status !== 'lengkap_sesuai' && empty($value)) {
                        $fail('Catatan wajib diisi jika status bukan "Lengkap & Sesuai".');
                    }
                },
            ],
        ]);

        $document->update([
            'kantor_check_status' => $validated['kantor_check_status'],
            'kantor_check_note' => $validated['kantor_check_note'] ?? null,
            'kantor_checked_by' => $user->id,
            'kantor_checked_at' => now(),
        ]);

        return back()->with('success', 'Status berkas "' . $document->document_name . '" berhasil diperbarui.');
    }

    // ── [BARU] Double-check per dokumen — DJKN Kanwil (sdm_kanwil) ──
    public function kanwilCheck(Request $request, Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKanwil()) {
            abort(403, 'Hanya Staff DJKN Kanwil yang dapat melakukan verifikasi tahap 2.');
        }

        $application = $document->application;

        if ($application->status !== ApplicationStatus::VERIFIKASI_KANWIL) {
            return back()->with('error', 'Verifikasi hanya bisa dilakukan saat tahap Verifikasi DJKN Kanwil.');
        }

        $validated = $request->validate([
            'kanwil_status' => ['required', 'in:sesuai,tidak_sesuai'],
            'rejection_note' => [
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->kanwil_status === 'tidak_sesuai' && empty($value)) {
                        $fail('Catatan wajib diisi jika status "Tidak Sesuai".');
                    }
                },
            ],
        ]);

        $document->update([
            'kanwil_status' => $validated['kanwil_status'],
            'rejection_note' => $validated['rejection_note'] ?? null,
            'verified_by' => $user->id,
            'verified_at' => now(),
            // Sinkronkan is_verified dengan hasil kanwil
            'is_verified' => $validated['kanwil_status'] === 'sesuai',
        ]);

        return back()->with('success', 'Status verifikasi berkas "' . $document->document_name . '" berhasil diperbarui.');
    }

    // ── [LAMA — dipertahankan untuk kompatibilitas, tidak dipakai aktif] ──
    public function verify(Request $request, Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->canVerify())
            abort(403);
        $document->verify($user);
        return back()->with('success', 'Berkas berhasil diverifikasi.');
    }

    public function reject(Request $request, Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->canVerify())
            abort(403);
        $request->validate(['rejection_note' => ['required', 'string', 'max:500']]);
        $document->reject($user, $request->rejection_note);
        return back()->with('success', 'Berkas ditolak.');
    }
}
