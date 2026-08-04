@extends('layouts.app')

@section('title', 'داشبورد اتوماسیون')

@push('styles')
    @include('dashboard.partials.styles')
@endpush

@section('content')
    @php
        $statusMeta = [
            \App\Support\AutomationStatuses::RUN_PENDING => ['label' => 'در انتظار', 'color' => 'grey'],
            \App\Support\AutomationStatuses::RUN_RUNNING => ['label' => 'در حال اجرا', 'color' => 'blue'],
            \App\Support\AutomationStatuses::RUN_DONE => ['label' => 'تکمیل شده', 'color' => 'green'],
            \App\Support\AutomationStatuses::RUN_FAILED => ['label' => 'خطادار', 'color' => 'red'],
            \App\Support\AutomationStatuses::RUN_PARTIAL_FAILED => ['label' => 'خطای جزئی', 'color' => 'orange'],
            \App\Support\AutomationStatuses::RUN_CANCELLED => ['label' => 'لغو شده', 'color' => 'black'],
            \App\Support\AutomationStatuses::USER_PAUSED => ['label' => 'متوقف شده', 'color' => 'yellow'],
            \App\Support\AutomationStatuses::CARE_SKIPPED => ['label' => 'رد شده', 'color' => 'teal'],
        ];

        $retryableStatuses = [
            \App\Support\AutomationStatuses::RUN_FAILED,
            \App\Support\AutomationStatuses::RUN_PARTIAL_FAILED,
            \App\Support\AutomationStatuses::RUN_CANCELLED,
        ];

        $stuckMinutes = $stats['stuck_minutes'] ?? 30;

        $canRetry = static function (?string $status) use ($retryableStatuses): bool {
            return in_array($status, $retryableStatuses, true);
        };

        $jalaliDate = static function ($date, string $format = 'Y/m/d H:i'): string {
            if ($date === null) {
                return '-';
            }

            return \Morilog\Jalali\Jalalian::fromCarbon($date)->format($format);
        };

        $getRowClass = static function (?string $status): string {
            return match ($status) {
                \App\Support\AutomationStatuses::RUN_FAILED => 'negative',
                \App\Support\AutomationStatuses::RUN_PARTIAL_FAILED => 'warning',
                \App\Support\AutomationStatuses::RUN_RUNNING => 'warning',
                \App\Support\AutomationStatuses::RUN_CANCELLED => 'active',
                default => '',
            };
        };

        $retryUrlTemplate = route('automation.runs.retry', ['run' => '__RUN_ID__']);
    @endphp

    <div class="ui container automation-dashboard">
        @include('dashboard.partials.hero')

        <div class="ui stackable five column grid" style="margin-top: 1.25rem;">
            @include('dashboard.partials.stat-card', [
                'id' => 'stat-total-runs',
                'value' => $stats['total_runs'] ?? 0,
                'label' => 'کل فرآیندها',
                'icon' => 'layer group',
                'iconColor' => '#176b9d',
                'statClass' => '',
            ])

            @include('dashboard.partials.stat-card', [
                'id' => 'stat-today-runs',
                'value' => $stats['today_runs'] ?? 0,
                'label' => 'فرآیندهای امروز',
                'icon' => 'calendar check outline',
                'iconColor' => '#2185d0',
                'statClass' => 'blue',
            ])

            @include('dashboard.partials.stat-card', [
                'id' => 'stat-pending-runs',
                'value' => $stats['pending_runs'] ?? 0,
                'label' => 'در صف انتظار',
                'icon' => 'hourglass half',
                'iconColor' => '#767676',
                'statClass' => 'grey',
            ])

            @include('dashboard.partials.stat-card', [
                'id' => 'stat-running-runs',
                'value' => $stats['running_runs'] ?? 0,
                'label' => 'در حال اجرا',
                'icon' => 'spinner loading',
                'iconColor' => '#fbbd08',
                'statClass' => 'yellow',
            ])

            @include('dashboard.partials.stat-card', [
                'id' => 'stat-failed-runs',
                'value' => $stats['failed_runs'] ?? 0,
                'label' => 'خطادار',
                'icon' => 'exclamation circle',
                'iconColor' => '#db2828',
                'statClass' => 'red',
            ])
        </div>

        @include('dashboard.partials.tables.pending-runs', [
            'pendingRuns' => $pendingRuns,
            'jalaliDate' => $jalaliDate,
        ])

        @include('dashboard.partials.tables.today-runs', [
            'todayRuns' => $todayRuns,
            'statusMeta' => $statusMeta,
            'canRetry' => $canRetry,
            'jalaliDate' => $jalaliDate,
            'getRowClass' => $getRowClass,
        ])

        @include('dashboard.partials.tables.stuck-runs', [
            'stuckRuns' => $stuckRuns,
            'stuckMinutes' => $stuckMinutes,
            'canRetry' => $canRetry,
            'jalaliDate' => $jalaliDate,
        ])

        @include('dashboard.partials.tables.recent-runs', [
            'runs' => $runs,
            'statusMeta' => $statusMeta,
            'canRetry' => $canRetry,
            'jalaliDate' => $jalaliDate,
            'getRowClass' => $getRowClass,
        ])
    </div>

    <div id="dashboard-toast-container" class="dashboard-toast-container" aria-live="polite"></div>
@endsection

@push('scripts')
    @include('dashboard.partials.scripts', [
        'retryUrlTemplate' => $retryUrlTemplate,
    ])
@endpush
