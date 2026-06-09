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
    // Upload berkas untuk sebuah pengajuan
    public function store(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek akses
        if ($user->isPensiunan() && $application->user_id !== $user->id) {
            abort(403);
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
            'documents.*' => ['required', 'file', 'max:5120'], // 5MB per file
            'documents.*.extension' => ['mimes:pdf,jpg,jpeg,png'],
            'document_names' => ['required', 'array'],
            'document_names.*' => ['required', 'string', 'max:255'],
        ]);

        $uploaded = 0;

        foreach ($request->file('documents') as $index => $file) {
            $docName = $request->document_names[$index] ?? $file->getClientOriginalName();
            $path = $file->store(
                'documents/' . $application->id,
                'local'
            );

            Document::create([
                'application_id' => $application->id,
                'uploaded_by' => $user->id,
                'document_name' => $docName,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $uploaded++;
        }

        // Auto advance ke status UPLOAD jika masih di PEMBERKASAN
        if ($application->status === ApplicationStatus::PEMBERKASAN) {
            $application->advanceStatus($user, "Upload {$uploaded} berkas.");
        }

        return back()->with('success', "{$uploaded} berkas berhasil diupload.");
    }

    // Upload satu berkas saja (form individual)
    public function storeSingle(Request $request, Application $application)
    {
        $user = Auth::user();

        $request->validate([
            'document_name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $application->id, 'local');

        // Cek apakah dokumen dengan nama yang sama sudah ada (replace)
        $existing = Document::where('application_id', $application->id)
            ->where('document_name', $request->document_name)
            ->first();

        if ($existing) {
            // Hapus file lama
            Storage::disk('local')->delete($existing->file_path);
            $existing->update([
                'uploaded_by' => $user->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'is_verified' => null,
                'verified_by' => null,
                'verified_at' => null,
                'rejection_note' => null,
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
            ]);
        }

        return back()->with('success', 'Berkas "' . $request->document_name . '" berhasil diupload.');
    }

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

        $fullPath = Storage::disk('local')->path($document->file_path);
        return response()->file($fullPath); // preview, bukan download
    }

    // Verifikasi berkas (SDM Kanwil)
    public function verify(Request $request, Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->canVerify()) {
            abort(403, 'Hanya Staff SDM Kanwil yang dapat memverifikasi berkas.');
        }

        $document->verify($user);

        return back()->with('success', 'Berkas berhasil diverifikasi.');
    }

    // Tolak berkas (SDM Kanwil)
    public function reject(Request $request, Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->canVerify()) {
            abort(403);
        }

        $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ]);

        $document->reject($user, $request->rejection_note);

        return back()->with('success', 'Berkas ditolak. Catatan telah dikirim.');
    }

    // Download berkas
    public function download(Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pensiunan hanya bisa download berkasnya sendiri
        if ($user->isPensiunan() && $document->application->user_id !== $user->id) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($document->file_path);
        return response()->download($fullPath, $document->original_filename);
    }

    // Hapus berkas
    public function destroy(Document $document)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Hanya uploader atau SDM Kanwil yang bisa hapus
        if ($document->uploaded_by !== $user->id && !$user->canVerify()) {
            abort(403);
        }

        // Tidak bisa hapus berkas yang sudah diverifikasi
        if ($document->is_verified) {
            return back()->with('error', 'Berkas yang sudah diverifikasi tidak dapat dihapus.');
        }

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }
}
