<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Article;
use App\Models\Regulation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ── Role: Pensiunan ──────────────────────────────────────
        if ($user->isPensiunan()) {
            return $this->dashboardPensiunan($user);
        }

        // ── Role: SDM Kantor ─────────────────────────────────────
        if ($user->isSdmKantor()) {
            return $this->dashboardSdmKantor($user);
        }

        // ── Role: SDM Kanwil & TIK ───────────────────────────────
        return $this->dashboardAnalytics($user);
    }

    // ─────────────────────────────────────────────────────────────
    // PENSIUNAN: hanya status pengajuan milik sendiri
    // ─────────────────────────────────────────────────────────────
    private function dashboardPensiunan(User $user)
    {
        $application = Application::with([
            'pensionType.documentTemplates',
            'documents',
            'statusHistories.actor',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $allStatuses = ApplicationStatus::allOrdered();
        $latestArticles = Article::latest()->take(5)->get();
        $latestRegulations = Regulation::latest()->take(5)->get();

        return view('dashboard.index', compact(
            'application',
            'allStatuses',
            'latestArticles',
            'latestRegulations'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // SDM KANTOR: stats + daftar pengajuan kantornya sendiri
    // ─────────────────────────────────────────────────────────────
    private function dashboardSdmKantor(User $user)
    {
        $query = Application::with([
            'user',
            'pensionType.documentTemplates',
            'documents',
        ])->byOffice($user->office);

        $stats = $this->buildStats($query);

        // 10 pengajuan terbaru untuk ditampilkan di tabel dashboard
        $recentApplications = (clone $query)->latest()->take(10)->get();

        $latestArticles = Article::latest()->take(5)->get();
        $latestRegulations = Regulation::latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentApplications', 'latestArticles', 'latestRegulations'));
    }

    // ─────────────────────────────────────────────────────────────
    // SDM KANWIL & TIK: analytics penuh — chart, rekap, semua kantor
    // ─────────────────────────────────────────────────────────────
    private function dashboardAnalytics(User $user)
    {
        $stats = $this->buildStats(Application::query());
        $monthlyData = $this->getMonthlyData();
        $officeData = $this->getOfficeData();
        $statusData = $this->getStatusData();

        $latestArticles = Article::latest()->take(5)->get();
        $latestRegulations = Regulation::latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'monthlyData', 'officeData', 'statusData', 'latestArticles', 'latestRegulations'));
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Hitung stats dari query yang diberikan (bisa difilter per kantor atau global).
     */
    private function buildStats($query): array
    {
        $total = (clone $query)->count();
        $thisMonth = (clone $query)->thisMonth()->count();
        $completed = (clone $query)->byStatus(ApplicationStatus::SK_TERBIT)->count();
        $pending = (clone $query)->whereNotIn('status', [
            ApplicationStatus::SK_TERBIT->value,
            ApplicationStatus::ACC->value,
        ])->count();

        $percentCompleted = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return compact('total', 'thisMonth', 'completed', 'pending', 'percentCompleted');
    }

    private function getMonthlyData(): array
    {
        $data = Application::select(
            DB::raw('EXTRACT(YEAR FROM created_at) as year'),
            DB::raw('EXTRACT(MONTH FROM created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at)'), DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy(DB::raw('EXTRACT(YEAR FROM created_at)'))
            ->orderBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->get();

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            $found = $data->first(
                fn($d) => $d->year == $date->year && $d->month == $date->month
            );
            $values[] = $found ? $found->total : 0;
        }

        return compact('labels', 'values');
    }

    private function getOfficeData(): array
    {
        return Application::select('users.office', DB::raw('COUNT(*) as total'))
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->whereNotNull('users.office')
            ->groupBy('users.office')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    private function getStatusData(): array
    {
        $data = Application::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return collect(ApplicationStatus::allOrdered())->map(fn($s) => [
            'label' => $s->label(),
            'value' => $s->value,
            'total' => $data->get($s->value)?->total ?? 0,
            'color' => $s->badgeColor(),
        ])->toArray();
    }
}
