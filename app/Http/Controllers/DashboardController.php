<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Data statistik umum
        $stats = $this->getStats($user);

        // Data chart bulanan (12 bulan terakhir)
        $monthlyData = $this->getMonthlyData();

        // Data per kantor
        $officeData = $this->getOfficeData();

        // Data per status
        $statusData = $this->getStatusData();

        return view('dashboard.index', compact(
            'user',
            'stats',
            'monthlyData',
            'officeData',
            'statusData'
        ));
    }


    private function getStats(User $user): array
    {
        $query = Application::query();

        // SDM Kantor hanya lihat data kantornya sendiri
        if ($user->isSdmKantor()) {
            $query->byOffice($user->office);
        }

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
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format untuk chart (12 bulan terakhir)
        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            $found = $data->first(
                fn($d) =>
                $d->year == $date->year && $d->month == $date->month
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
