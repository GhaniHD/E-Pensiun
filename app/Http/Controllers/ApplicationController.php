<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\PensionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApplicationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Application::with(['user', 'pensionType', 'verifier']);

        if ($user->isPensiunan()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isSdmKantor()) {
            $query->byOffice($user->office);
        }

        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('pension_type'))
            $query->where('pension_type_id', $request->pension_type);
        if ($request->filled('office') && !$user->isSdmKantor())
            $query->byOffice($request->office);

        $applications = $query->latest()->paginate(15)->withQueryString();
        $pensionTypes = PensionType::active()->get();
        $statuses = ApplicationStatus::allOrdered();

        return view('applications.index', compact('applications', 'pensionTypes', 'statuses'));
    }

    public function create()
    {
        $pensionTypes = PensionType::active()->get();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $pensiunanUsers = \App\Models\User::where('role', UserRole::PENSIUNAN)
            ->where('office', $user->office)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('applications.create', compact('pensionTypes', 'pensiunanUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'pension_type_id' => ['required', 'exists:pension_types,id'],
            'pension_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var \App\Models\User $operator */
        $operator = Auth::user();
        $targetUserId = $data['user_id'];

        $existing = Application::where('user_id', $targetUserId)
            ->whereNotIn('status', [
                ApplicationStatus::SK_TERBIT->value,
                ApplicationStatus::DIBATALKAN->value,
            ])
            ->first();

        if ($existing) {
            return back()->with('error', 'Pegawai ini masih memiliki pengajuan yang sedang diproses.');
        }

        $application = Application::create([
            'user_id' => $targetUserId,
            'pension_type_id' => $data['pension_type_id'],
            'pension_date' => $data['pension_date'],
            'notes' => $data['notes'] ?? null,
            'status' => ApplicationStatus::PENGISIAN_FORM,
        ]);

        $application->statusHistories()->create([
            'from_status' => ApplicationStatus::PENGISIAN_FORM->value,
            'to_status' => ApplicationStatus::PENGISIAN_FORM->value,
            'changed_by' => $operator->id,
            'note' => 'Pengajuan dibuat oleh ' . $operator->name . ' (Staff KPKNL Pelayanan).',
        ]);

        $application->advanceStatus($operator, 'Pengajuan dibuat, masuk tahap pemberkasan.');

        return redirect()->route('applications.show', $application)
            ->with('success', 'Pengajuan berhasil dibuat untuk ' . $application->user->name);
    }

    public function show(Application $application)
    {
        $this->authorizeView($application);

        $application->load([
            'user',
            'pensionType.documentTemplates',
            'documents.uploader',
            'documents.verifier',
            'documents.kantorChecker',
            'verifier',
            'statusHistories.actor',
        ]);

        $allStatuses = ApplicationStatus::allOrdered();
        $siAsnBknUrl = config('services.bkn.siasn_url', 'https://siasn.bkn.go.id');
        $teamsFolderUrl = Auth::user()->teams_folder_url;

        return view('applications.show', compact(
            'application',
            'allStatuses',
            'siAsnBknUrl',
            'teamsFolderUrl'
        ));
    }

    public function edit(Application $application)
    {
        $this->authorize('update', $application);

        if ($application->status !== ApplicationStatus::PENGISIAN_FORM) {
            return back()->with('error', 'Pengajuan tidak dapat diedit pada tahap ini.');
        }

        $pensionTypes = PensionType::active()->get();
        return view('applications.edit', compact('application', 'pensionTypes'));
    }

    public function update(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        if ($application->status !== ApplicationStatus::PENGISIAN_FORM) {
            return back()->with('error', 'Pengajuan tidak dapat diedit pada tahap ini.');
        }

        $data = $request->validate([
            'pension_type_id' => ['required', 'exists:pension_types,id'],
            'pension_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update($data);

        return redirect()->route('applications.show', $application)
            ->with('success', 'Pengajuan berhasil diperbarui.');
    }

    // ── Advance status ─────────────────────────────────────
    public function advance(Request $request, Application $application)
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $status = $application->status->value;

        // --- SDM KANTOR ---
        if ($user->isSdmKantor()) {
            if ($status === ApplicationStatus::PEMBERKASAN->value) {
                // Konfirmasi berkas fisik → boleh langsung advance ke UPLOAD
            } elseif ($status === ApplicationStatus::UPLOAD->value) {
                // Ajukan ke Verifikasi KPKNL — semua file harus terupload
                $totalTemplate = $application->pensionType->documentTemplates()->count();
                $totalUploaded = $application->documents()->whereNotNull('file_path')->count();
                if ($totalUploaded < $totalTemplate) {
                    return back()->with('error', 'Semua berkas harus diunggah terlebih dahulu.');
                }
            } elseif ($status === ApplicationStatus::VERIFIKASI_KPKNL->value) {
                // Ajukan ke Kanwil — semua dokumen harus sudah dicek
                $application->load('documents');
                if (!$application->allDocumentsCheckedByKantor()) {
                    return back()->with('error', 'Semua berkas harus dicek terlebih dahulu sebelum diajukan ke DJKN Kanwil.');
                }
                // Jika ada dokumen bermasalah, tetap boleh diteruskan (keputusan manual)
            } else {
                abort(403, 'Anda tidak memiliki akses untuk memajukan tahap ini.');
            }
        }
        // --- SDM KANWIL ---
        elseif ($user->isSdmKanwil()) {
            if ($status === ApplicationStatus::VERIFIKASI_KANWIL->value) {
                // Advance ke ACC — semua dokumen harus berstatus "Sesuai"
                $application->load('documents');
                if (!$application->allDocumentsApprovedByKanwil()) {
                    return back()->with('error', 'Semua berkas harus berstatus "Sesuai" sebelum pengajuan dapat di-ACC.');
                }
            }
            // sdm_kanwil bisa advance tahap lain (ACC → SK Terbit, dll.)
        }
        // --- TIK ---
        elseif ($user->isTik()) {
            // TIK (admin) bisa advance semua tahap
        } else {
            abort(403);
        }

        if (!$application->canAdvance()) {
            return back()->with('error', 'Pengajuan sudah pada tahap akhir.');
        }

        $application->advanceStatus($user, $request->note);

        return back()->with('success', 'Status berhasil dimajukan ke: ' . $application->fresh()->status->label());
    }

    // ── Kembalikan ke Upload (dari VERIFIKASI_KPKNL) ──────
    public function returnToUpload(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKantor()) {
            abort(403, 'Hanya Staff KPKNL Pelayanan yang dapat mengembalikan ke tahap Upload.');
        }

        if ($application->status !== ApplicationStatus::VERIFIKASI_KPKNL) {
            return back()->with('error', 'Pengembalian hanya bisa dilakukan dari tahap Verifikasi KPKNL Pelayanan.');
        }

        $request->validate([
            'return_note' => ['required', 'string', 'max:500'],
        ]);

        $application->returnToUpload($user, $request->return_note);

        return back()->with('success', 'Pengajuan dikembalikan ke tahap Upload untuk perbaikan berkas.');
    }

    // ── Tolak pengajuan ────────────────────────────────────
    public function reject(Request $request, Application $application)
    {
        $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->canVerify()) {
            abort(403, 'Hanya Staff DJKN Kanwil yang dapat menolak pengajuan.');
        }

        $application->rejectApplication($user, $request->rejection_note);

        return back()->with('success', 'Pengajuan berhasil dikembalikan untuk perbaikan.');
    }

    public function cancel(Request $request, Application $application)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isSdmKantor() && !$user->isTik()) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan pengajuan.');
        }

        if (!$application->canBeCancelled()) {
            return back()->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah dalam proses verifikasi.');
        }

        $request->validate([
            'cancel_note' => ['required', 'string', 'max:500'],
        ]);

        $application->cancelApplication($user, $request->cancel_note);

        return redirect()->route('applications.index')
            ->with('success', 'Pengajuan #' . $application->id . ' berhasil dibatalkan.');
    }

    private function authorizeView(Application $application): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isPensiunan() && $application->user_id !== $user->id)
            abort(403);
        if ($user->isSdmKantor() && $application->user->office !== $user->office)
            abort(403);
    }
}
