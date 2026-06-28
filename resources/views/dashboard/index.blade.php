@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ══════════════════════════════════════════════════════════
     ROUTING PER ROLE — masing-masing render partial berbeda
     ══════════════════════════════════════════════════════════ --}}

@php $user = auth()->user(); @endphp

@if($user->isPensiunan())
    @include('dashboard.partials.pensiunan', ['myApplication' => $application ?? null])

@elseif($user->isSdmKantor())
    @include('dashboard.partials.sdm-kantor')

@else
    {{-- SDM Kanwil & TIK: dashboard analytics lengkap --}}
    @include('dashboard.partials.analytics')
@endif

@endsection

@section('scripts')
@if(!auth()->user()->isPensiunan())
<script>
    @if(!auth()->user()->isSdmKantor())
    {{-- Chart hanya untuk SDM Kanwil & TIK --}}
    const monthlyLabels = @json($monthlyData['labels'] ?? []);
    const monthlyValues = @json($monthlyData['values'] ?? []);
    const officeLabels  = @json(array_column($officeData ?? [], 'office'));
    const officeValues  = @json(array_column($officeData ?? [], 'total'));

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: monthlyValues,
                backgroundColor: '#27AE60',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });

    @if(isset($officeData) && count($officeData) > 0)
    new Chart(document.getElementById('officeChart'), {
        type: 'doughnut',
        data: {
            labels: officeLabels,
            datasets: [{
                data: officeValues,
                backgroundColor: ['#1A5632','#27AE60','#1E8449','#D4AC0D','#C0392B','#7D3C98'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10, boxWidth: 14 } } },
            cutout: '60%'
        }
    });
    @endif
    @endif
</script>
@endif
@endsection
