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
        // ── Validasi field dasar ───────────────────────────────────
        $rules = [
            'user_id' => ['required', 'exists:users,id'],
            'pension_type_id' => ['required', 'exists:pension_types,id'],
            'pension_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // ── Validasi field tambahan (hanya KPKNL & Kanwil) ────────
        /** @var \App\Models\User $operator */
        $operator = Auth::user();

        if ($operator->isSdmKantor() || $operator->isSdmKanwil()) {
            $rules = array_merge($rules, [
                'nama_calon_pensiunan' => ['nullable', 'string', 'max:255'],
                'unit_kerja' => ['nullable', 'string', 'max:255'],
                'nip_calon_pensiunan' => ['nullable', 'string', 'max:25'],
                'tanggal_lahir' => ['nullable', 'date'],
                'jenis_pensiun_bkn' => ['nullable', 'string', 'in:BUP,APS,Janda/Duda,Cacat,Meninggal'],
                'kenaikan_pangkat' => ['nullable', 'boolean'],
                'usia_pensiun' => ['nullable', 'integer', 'in:58,60'],
                'tmt_cpns' => ['nullable', 'date'],
                'tmt_pns' => ['nullable', 'date'],
                'tmt_pangkat_terakhir' => ['nullable', 'date'],
                'mk_kp_terakhir_tahun' => ['nullable', 'integer', 'min:0', 'max:99'],
                'mk_kp_terakhir_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'mk_pensiun_tahun' => ['nullable', 'integer', 'min:0'],
                'mk_pensiun_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'mk_pns_tahun' => ['nullable', 'integer', 'min:0'],
                'mk_pns_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'mk_golongan_tahun' => ['nullable', 'integer', 'min:0'],
                'mk_golongan_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
            ]);
        }

        $data = $request->validate($rules);

        // ── Cek duplikat pengajuan aktif ───────────────────────────
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

        // ── Bangun payload ─────────────────────────────────────────
        $payload = [
            'user_id' => $targetUserId,
            'pension_type_id' => $data['pension_type_id'],
            'pension_date' => $data['pension_date'],
            'notes' => $data['notes'] ?? null,
            'status' => ApplicationStatus::PENGISIAN_FORM,
        ];

        if ($operator->isSdmKantor() || $operator->isSdmKanwil()) {
            $payload = array_merge($payload, [
                'nama_calon_pensiunan' => $data['nama_calon_pensiunan'] ?? null,
                'unit_kerja' => $data['unit_kerja'] ?? null,
                'nip_calon_pensiunan' => $data['nip_calon_pensiunan'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_pensiun_bkn' => $data['jenis_pensiun_bkn'] ?? null,
                'kenaikan_pangkat' => isset($data['kenaikan_pangkat']) ? (bool) $data['kenaikan_pangkat'] : null,
                'usia_pensiun' => $data['usia_pensiun'] ?? 58,
                'tmt_cpns' => $data['tmt_cpns'] ?? null,
                'tmt_pns' => $data['tmt_pns'] ?? null,
                'tmt_pangkat_terakhir' => $data['tmt_pangkat_terakhir'] ?? null,
                'mk_kp_terakhir_tahun' => $data['mk_kp_terakhir_tahun'] ?? null,
                'mk_kp_terakhir_bulan' => $data['mk_kp_terakhir_bulan'] ?? null,
                'mk_pensiun_tahun' => $data['mk_pensiun_tahun'] ?? null,
                'mk_pensiun_bulan' => $data['mk_pensiun_bulan'] ?? null,
                'mk_pns_tahun' => $data['mk_pns_tahun'] ?? null,
                'mk_pns_bulan' => $data['mk_pns_bulan'] ?? null,
                'mk_golongan_tahun' => $data['mk_golongan_tahun'] ?? null,
                'mk_golongan_bulan' => $data['mk_golongan_bulan'] ?? null,
            ]);
        }

        $application = Application::create($payload);

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

        // ── Validasi field dasar ───────────────────────────────────
        $rules = [
            'pension_type_id' => ['required', 'exists:pension_types,id'],
            'pension_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // ── Validasi field tambahan (hanya KPKNL & Kanwil) ────────
        /** @var \App\Models\User $operator */
        $operator = Auth::user();

        if ($operator->isSdmKantor() || $operator->isSdmKanwil()) {
            $rules = array_merge($rules, [
                'nama_calon_pensiunan' => ['nullable', 'string', 'max:255'],
                'unit_kerja' => ['nullable', 'string', 'max:255'],
                'nip_calon_pensiunan' => ['nullable', 'string', 'max:25'],
                'tanggal_lahir' => ['nullable', 'date'],
                'jenis_pensiun_bkn' => ['nullable', 'string', 'in:BUP,APS,Janda/Duda,Cacat,Meninggal'],
                'kenaikan_pangkat' => ['nullable', 'boolean'],
                'usia_pensiun' => ['nullable', 'integer', 'in:58,60'],
                'tmt_cpns' => ['nullable', 'date'],
                'tmt_pns' => ['nullable', 'date'],
                'tmt_pangkat_terakhir' => ['nullable', 'date'],
                'mk_kp_terakhir_tahun' => ['nullable', 'integer', 'min:0', 'max:99'],
                'mk_kp_terakhir_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'mk_pensiun_tahun' => ['nullable', 'integer', 'min:0'],
                'mk_pensiun_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'mk_pns_tahun' => ['nullable', 'integer', 'min:0'],
                'mk_pns_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
                'mk_golongan_tahun' => ['nullable', 'integer', 'min:0'],
                'mk_golongan_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
            ]);
        }

        $data = $request->validate($rules);

        // ── Bangun payload ─────────────────────────────────────────
        $payload = [
            'pension_type_id' => $data['pension_type_id'],
            'pension_date' => $data['pension_date'],
            'notes' => $data['notes'] ?? null,
        ];

        if ($operator->isSdmKantor() || $operator->isSdmKanwil()) {
            $payload = array_merge($payload, [
                'nama_calon_pensiunan' => $data['nama_calon_pensiunan'] ?? null,
                'unit_kerja' => $data['unit_kerja'] ?? null,
                'nip_calon_pensiunan' => $data['nip_calon_pensiunan'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_pensiun_bkn' => $data['jenis_pensiun_bkn'] ?? null,
                'kenaikan_pangkat' => isset($data['kenaikan_pangkat']) ? (bool) $data['kenaikan_pangkat'] : null,
                'usia_pensiun' => $data['usia_pensiun'] ?? 58,
                'tmt_cpns' => $data['tmt_cpns'] ?? null,
                'tmt_pns' => $data['tmt_pns'] ?? null,
                'tmt_pangkat_terakhir' => $data['tmt_pangkat_terakhir'] ?? null,
                'mk_kp_terakhir_tahun' => $data['mk_kp_terakhir_tahun'] ?? null,
                'mk_kp_terakhir_bulan' => $data['mk_kp_terakhir_bulan'] ?? null,
                'mk_pensiun_tahun' => $data['mk_pensiun_tahun'] ?? null,
                'mk_pensiun_bulan' => $data['mk_pensiun_bulan'] ?? null,
                'mk_pns_tahun' => $data['mk_pns_tahun'] ?? null,
                'mk_pns_bulan' => $data['mk_pns_bulan'] ?? null,
                'mk_golongan_tahun' => $data['mk_golongan_tahun'] ?? null,
                'mk_golongan_bulan' => $data['mk_golongan_bulan'] ?? null,
            ]);
        }

        $application->update($payload);

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

        if ($user->isSdmKantor()) {
            $allowedStages = [
                ApplicationStatus::PEMBERKASAN->value,
                ApplicationStatus::UPLOAD->value,
                ApplicationStatus::VERIFIKASI_KPKNL->value,
            ];
            if (!in_array($status, $allowedStages)) {
                abort(403, 'Anda tidak memiliki akses untuk memajukan tahap ini.');
            }
        } elseif ($user->isSdmKanwil()) {
            // sdm_kanwil bisa advance semua tahap
        } elseif ($user->isTik()) {
            if (!$application->canAdvance()) {
                return back()->with('error', 'Pengajuan sudah pada tahap akhir.');
            }
            $application->advanceStatus($user, $request->note);
            return back()->with('success', 'Status berhasil dimajukan ke: ' . $application->fresh()->status->label());
        } else {
            abort(403);
        }

        if ($status === ApplicationStatus::UPLOAD->value) {
            $totalTemplate = $application->pensionType->documentTemplates()->count();
            $totalUploaded = $application->documents()->whereNotNull('file_path')->count();

            if ($totalUploaded < $totalTemplate) {
                return back()->with(
                    'error',
                    "Semua berkas harus diunggah terlebih dahulu ({$totalUploaded}/{$totalTemplate})."
                );
            }
        }

        if ($status === ApplicationStatus::VERIFIKASI_KPKNL->value) {
            $application->load('documents');
            if (!$application->allDocumentsCheckedByKantor()) {
                return back()->with(
                    'error',
                    'Semua berkas harus dicek KPKNL terlebih dahulu sebelum diajukan ke DJKN Kanwil.'
                );
            }
        }

        if ($status === ApplicationStatus::VERIFIKASI_KANWIL->value) {
            $application->load('documents');
            if (!$application->allDocumentsApprovedByKanwil()) {
                return back()->with(
                    'error',
                    'Semua berkas harus berstatus "Sesuai" sebelum pengajuan dapat di-ACC.'
                );
            }
        }

        if (!$application->canAdvance()) {
            return back()->with('error', 'Pengajuan sudah pada tahap akhir.');
        }

        $application->advanceStatus($user, $request->note);

        return back()->with('success', 'Status berhasil dimajukan ke: ' . $application->fresh()->status->label());
    }

    // ── Kembalikan ke Upload ───────────────────────────────
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
