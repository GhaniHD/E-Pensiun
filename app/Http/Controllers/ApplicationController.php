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

        // Filter sesuai role
        if ($user->isPensiunan()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isSdmKantor()) {
            $query->byOffice($user->office);
        }
        // SDM Kanwil dan TIK lihat semua

        // Filter tambahan dari request
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('pension_type')) {
            $query->where('pension_type_id', $request->pension_type);
        }
        if ($request->filled('office') && !$user->isSdmKantor()) {
            $query->byOffice($request->office);
        }

        $applications = $query->latest()->paginate(15)->withQueryString();
        $pensionTypes = PensionType::active()->get();
        $statuses = ApplicationStatus::allOrdered();

        return view('applications.index', compact('applications', 'pensionTypes', 'statuses'));
    }

    public function create()
    {
        // Hanya pensiunan & SDM kantor yang bisa buat pengajuan
        $pensionTypes = PensionType::active()->get();

        return view('applications.create', compact('pensionTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pension_type_id' => ['required', 'exists:pension_types,id'],
            'pension_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek apakah sudah ada pengajuan aktif
        $existing = Application::where('user_id', $user->id)
            ->whereNotIn('status', [ApplicationStatus::SK_TERBIT->value])
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda masih memiliki pengajuan yang sedang diproses.');
        }

        $application = Application::create([
            ...$data,
            'user_id' => $user->id,
            'status' => ApplicationStatus::PENGISIAN_FORM,
        ]);

        // Catat history
        $application->statusHistories()->create([
            'from_status' => ApplicationStatus::PENGISIAN_FORM->value,
            'to_status' => ApplicationStatus::PENGISIAN_FORM->value,
            'changed_by' => $user->id,
            'note' => 'Pengajuan baru dibuat.',
        ]);

        return redirect()->route('applications.show', $application)
            ->with('success', 'Pengajuan berhasil dibuat. Silakan lengkapi berkas persyaratan.');
    }

    public function show(Application $application)
    {
        $this->authorizeView($application);

        $application->load([
            'user',
            'pensionType.documentTemplates',
            'documents.uploader',
            'documents.verifier',
            'verifier',
            'statusHistories.actor',
        ]);

        $allStatuses = ApplicationStatus::allOrdered();

        return view('applications.show', compact('application', 'allStatuses'));
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

    // Advance ke tahap berikutnya (SDM Kanwil)
    public function advance(Request $request, Application $application)
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->canVerify()) {
            abort(403, 'Hanya Staff SDM Kanwil yang dapat memajukan status pengajuan.');
        }

        if (!$application->canAdvance()) {
            return back()->with('error', 'Pengajuan sudah pada tahap akhir.');
        }

        $application->advanceStatus($user, $request->note);

        return back()->with('success', 'Status pengajuan berhasil dimajukan ke: ' . $application->fresh()->status->label());
    }

    // Tolak pengajuan (SDM Kanwil)
    public function reject(Request $request, Application $application)
    {
        $request->validate([
            'rejection_note' => ['required', 'string', 'max:1000'],
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->canVerify()) {
            abort(403, 'Hanya Staff SDM Kanwil yang dapat menolak pengajuan.');
        }

        $application->rejectApplication($user, $request->rejection_note);

        return back()->with('success', 'Pengajuan berhasil dikembalikan untuk perbaikan.');
    }

    private function authorizeView(Application $application): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isPensiunan() && $application->user_id !== $user->id) {
            abort(403);
        }

        if ($user->isSdmKantor() && $application->user->office !== $user->office) {
            abort(403);
        }
    }
}
