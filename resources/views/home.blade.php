@extends('layouts.app')

@section('title', 'داشبورد اتوماسیون')

@push('styles')
    <style>
        .automation-dashboard {
            width: 100%;
            padding-top: 1.25rem;
            padding-bottom: 2rem;
        }

        .dashboard-hero {
            padding: 1.25rem 1.5rem !important;
            border: 0 !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #123b59 0%, #176b9d 55%, #2185d0 100%) !important;
            box-shadow: 0 12px 30px rgba(27, 79, 114, 0.18) !important;
        }

        .dashboard-hero .header,
        .dashboard-hero .sub.header,
        .dashboard-refresh-info {
            color: #fff !important;
        }

        .dashboard-hero .sub.header {
            margin-top: 0.45rem !important;
            opacity: 0.9;
            line-height: 1.8;
        }

        .dashboard-hero-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .dashboard-refresh-info {
            margin-top: 0.85rem;
            font-size: 12px;
            opacity: 0.9;
        }

        .dashboard-live-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            background: #dcfce7;
            color: #166534;
        }

        .dashboard-live-status.is-loading {
            background: #fef3c7;
            color: #92400e;
        }

        .dashboard-live-status.is-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .dashboard-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            flex: 0 0 8px;
        }

        .dashboard-stat-card {
            min-height: 120px;
            border: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 5px 18px rgba(34, 36, 38, 0.08) !important;
        }

        .dashboard-stat-icon {
            margin-bottom: 0.45rem;
            font-size: 1.45rem;
        }

        .dashboard-section {
            border: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 5px 18px rgba(34, 36, 38, 0.08) !important;
        }

        .dashboard-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(34, 36, 38, 0.08);
        }

        .dashboard-section-header .ui.header {
            margin: 0;
        }

        .dashboard-table-wrapper {
            width: 100%;
        }

        .automation-dashboard table {
            width: 100%;
            table-layout: fixed;
            border-radius: 8px !important;
            overflow: hidden;
        }

        .automation-dashboard table thead th {
            background: #f8fafc;
            color: #4b5563;
            font-size: 0.84rem;
            white-space: nowrap;
            text-align: right !important;
        }

        .automation-dashboard table th,
        .automation-dashboard table td {
            vertical-align: middle !important;
        }

        .automation-dashboard table tbody tr:hover {
            background-color: #f8fafc !important;
        }

        .run-id {
            color: #176b9d;
            font-family: monospace;
            font-size: 0.92rem;
            font-weight: bold;
            white-space: nowrap;
        }

        .progress-cell small {
            display: block;
            margin-top: 0.4rem;
            color: #6b7280;
            line-height: 1.7;
            text-align: right;
        }

        .run-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .run-actions .button {
            margin: 0 !important;
        }

        .retry-run-btn.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .empty-state {
            padding: 1.25rem !important;
            text-align: center;
            border-radius: 8px !important;
            line-height: 1.8;
        }

        .dashboard-toast-container {
            position: fixed;
            z-index: 9999;
            top: 1rem;
            left: 1rem;
            right: 1rem;
            display: flex;
            justify-content: center;
            pointer-events: none;
        }

        .dashboard-toast {
            width: min(420px, 100%);
            padding: 0.9rem 1rem;
            border-radius: 8px;
            color: #fff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.18);
            text-align: center;
            font-size: 0.9rem;
            line-height: 1.7;
            pointer-events: auto;
        }

        .dashboard-toast.success {
            background: #21ba45;
        }

        .dashboard-toast.error {
            background: #db2828;
        }

        @media only screen and (max-width: 991px) {
            .dashboard-section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .dashboard-stat-card {
                min-height: 100px;
            }

            .automation-dashboard table th,
            .automation-dashboard table td {
                padding: 0.65rem 0.55rem !important;
                font-size: 0.84rem;
            }
        }

        @media only screen and (max-width: 767px) {
            .automation-dashboard {
                padding-top: 0.75rem;
                padding-bottom: 1.25rem;
            }

            .dashboard-hero {
                padding: 0.95rem !important;
            }

            .dashboard-hero .ui.header {
                font-size: 1.1rem !important;
                line-height: 1.6;
            }

            .dashboard-hero .sub.header {
                font-size: 0.8rem !important;
            }

            .dashboard-hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .dashboard-hero-actions .button,
            .dashboard-hero-actions a.button {
                width: 100%;
                margin: 0 !important;
            }

            .dashboard-live-status {
                width: 100%;
                box-sizing: border-box;
            }

            .dashboard-refresh-info {
                font-size: 11px;
            }

            .dashboard-section {
                padding: 0.9rem !important;
            }

            .automation-dashboard table,
            .automation-dashboard table tbody,
            .automation-dashboard table tr,
            .automation-dashboard table td {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }

            .automation-dashboard table thead {
                display: none;
            }

            .automation-dashboard table {
                border: 0 !important;
                background: transparent !important;
            }

            .automation-dashboard table tbody {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .automation-dashboard table tr {
                padding: 0.85rem !important;
                border: 1px solid rgba(34, 36, 38, 0.12) !important;
                border-radius: 10px !important;
                background: #fff !important;
                box-shadow: 0 3px 12px rgba(34, 36, 38, 0.07);
            }

            .automation-dashboard table td {
                display: flex !important;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.45rem 0 !important;
                border: 0 !important;
                border-bottom: 1px solid rgba(34, 36, 38, 0.08) !important;
                text-align: right !important;
                direction: rtl;
                word-break: break-word;
            }

            .automation-dashboard table td:last-child {
                border-bottom: 0 !important;
                padding-bottom: 0 !important;
            }

            .automation-dashboard table td::before {
                content: attr(data-label);
                flex: 0 0 42%;
                color: #6b7280;
                font-size: 0.74rem;
                font-weight: bold;
                text-align: right;
                line-height: 1.8;
                order: 2;
            }

            .automation-dashboard table td > * {
                max-width: 58%;
                width: 58%;
                text-align: right !important;
                margin-left: 0 !important;
                margin-right: auto !important;
                order: 1;
            }

            .progress-cell {
                display: flex !important;
                flex-direction: column;
                align-items: flex-start !important;
                text-align: right !important;
            }

            .progress-cell .ui.progress {
                width: 100%;
                margin: 0 !important;
            }

            .progress-cell small {
                font-size: 0.68rem;
                max-width: 100%;
                width: 100%;
                margin-top: 0.35rem;
                text-align: right !important;
                white-space: normal;
            }

            .run-actions {
                width: 58% !important;
                max-width: 58% !important;
                justify-content: flex-start;
                direction: rtl;
            }

            .run-actions .button,
            .run-actions a.button,
            .run-actions button.button {
                margin: 0 !important;
                font-size: 0.72rem;
            }

            .run-id {
                font-size: 0.8rem;
                text-align: right !important;
                display: inline-block;
                width: 100%;
            }

            .ui.label {
                font-size: 0.68rem;
            }
        }

        @media only screen and (max-width: 420px) {
            .automation-dashboard table td::before {
                flex-basis: 38%;
                font-size: 0.68rem;
            }

            .automation-dashboard table td > * {
                max-width: 62%;
                width: 62%;
            }

            .run-actions {
                width: 62% !important;
                max-width: 62% !important;
            }

            .automation-dashboard .ui.statistic > .value {
                font-size: 1.35rem !important;
            }

            .automation-dashboard .ui.statistic > .label {
                font-size: 0.72rem !important;
            }
        }
    </style>
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
        <div class="ui segment dashboard-hero">
            <div class="ui stackable middle aligned grid">
                <div class="ten wide column">
                    <h2 class="ui inverted header">
                        <i class="dashboard icon"></i>
                        <div class="content">
                            داشبورد اتوماسیون
                            <div class="sub header">
                                پایش زنده صف، وضعیت اجراها، خطاها و فرآیندهای مشکوک به توقف
                            </div>
                        </div>
                    </h2>
                </div>

                <div class="six wide column">
                    <span class="dashboard-live-status" id="dashboard-live-status">
                        <span class="dashboard-live-dot"></span>
                        اتصال فعال
                    </span>

                    <div class="dashboard-hero-actions">
                        <button type="button" class="ui inverted basic button" id="refresh-page">
                            <i class="sync alternate icon"></i>
                            بروزرسانی
                        </button>

                        <a href="{{ route('automation.runs.index') }}" class="ui inverted button">
                            <i class="list icon"></i>
                            همه فرآیندها
                        </a>
                    </div>

                    <div class="dashboard-refresh-info">
                        آخرین بروزرسانی:
                        <strong id="last-refresh-at">-</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui stackable five column grid" style="margin-top: 1.25rem;">
            <div class="column">
                <div class="ui fluid segment center aligned dashboard-stat-card">
                    <div class="dashboard-stat-icon" style="color: #176b9d;">
                        <i class="layer group icon"></i>
                    </div>
                    <div class="ui mini statistic">
                        <div class="value" id="stat-total-runs">{{ $stats['total_runs'] ?? 0 }}</div>
                        <div class="label">کل فرآیندها</div>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="ui fluid segment center aligned dashboard-stat-card">
                    <div class="dashboard-stat-icon" style="color: #2185d0;">
                        <i class="calendar check outline icon"></i>
                    </div>
                    <div class="ui mini blue statistic">
                        <div class="value" id="stat-today-runs">{{ $stats['today_runs'] ?? 0 }}</div>
                        <div class="label">فرآیندهای امروز</div>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="ui fluid segment center aligned dashboard-stat-card">
                    <div class="dashboard-stat-icon" style="color: #767676;">
                        <i class="hourglass half icon"></i>
                    </div>
                    <div class="ui mini grey statistic">
                        <div class="value" id="stat-pending-runs">{{ $stats['pending_runs'] ?? 0 }}</div>
                        <div class="label">در صف انتظار</div>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="ui fluid segment center aligned dashboard-stat-card">
                    <div class="dashboard-stat-icon" style="color: #fbbd08;">
                        <i class="spinner loading icon"></i>
                    </div>
                    <div class="ui mini yellow statistic">
                        <div class="value" id="stat-running-runs">{{ $stats['running_runs'] ?? 0 }}</div>
                        <div class="label">در حال اجرا</div>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="ui fluid segment center aligned dashboard-stat-card">
                    <div class="dashboard-stat-icon" style="color: #db2828;">
                        <i class="exclamation circle icon"></i>
                    </div>
                    <div class="ui mini red statistic">
                        <div class="value" id="stat-failed-runs">{{ $stats['failed_runs'] ?? 0 }}</div>
                        <div class="label">خطادار</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui raised segment dashboard-section" style="margin-top: 1.5rem;">
            <div class="dashboard-section-header">
                <h3 class="ui grey header">
                    <i class="hourglass half icon"></i>
                    <div class="content">
                        صف انتظار
                        <div class="sub header">فرآیندهایی که هنوز شروع نشده‌اند</div>
                    </div>
                </h3>

                <span class="ui circular grey label" id="badge-pending-count">
                    {{ $pendingRuns->count() }}
                </span>
            </div>

            <div id="pending-runs-container">
                @if ($pendingRuns->isEmpty())
                    <div class="ui positive message empty-state">
                        <i class="check circle icon"></i>
                        در حال حاضر فرآیندی در صف انتظار وجود ندارد.
                    </div>
                @else
                    <div class="dashboard-table-wrapper">
                        <table class="ui celled striped compact table">
                            <thead>
                            <tr>
                                <th>Run ID</th>
                                <th>نوع مراقبت</th>
                                <th>تعداد کاربران</th>
                                <th>تعداد مراقبت‌ها</th>
                                <th>زمان ثبت</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($pendingRuns as $pendingRun)
                                <tr>
                                    <td data-label="شناسه اجرا">
                                        <span class="run-id">#{{ $pendingRun->id }}</span>
                                    </td>
                                    <td data-label="نوع مراقبت">{{ $pendingRun->input['care_type'] ?? '-' }}</td>
                                    <td data-label="تعداد کاربران">{{ $pendingRun->total_users ?? 0 }}</td>
                                    <td data-label="تعداد مراقبت‌ها">{{ $pendingRun->total_cares ?? 0 }}</td>
                                    <td data-label="زمان ثبت">{{ $jalaliDate($pendingRun->created_at) }}</td>
                                    <td data-label="عملیات">
                                        <div class="run-actions">
                                            <a href="{{ route('automation.runs.show', $pendingRun) }}" class="ui mini basic button">
                                                مشاهده
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="ui raised segment dashboard-section" style="margin-top: 1.5rem;">
            <div class="dashboard-section-header">
                <h3 class="ui blue header">
                    <i class="calendar alternate outline icon"></i>
                    <div class="content">
                        فرآیندهای امروز
                        <div class="sub header">تمام فرآیندهای ایجادشده در تاریخ امروز</div>
                    </div>
                </h3>

                <span class="ui circular blue label" id="badge-today-count">
                    {{ $todayRuns->count() }}
                </span>
            </div>

            <div id="today-runs-container">
                @if ($todayRuns->isEmpty())
                    <div class="ui info message empty-state">
                        <i class="info circle icon"></i>
                        امروز فرآیند جدیدی ثبت نشده است.
                    </div>
                @else
                    <div class="dashboard-table-wrapper">
                        <table class="ui celled striped compact table">
                            <thead>
                            <tr>
                                <th>Run ID</th>
                                <th>نوع مراقبت</th>
                                <th>وضعیت</th>
                                <th>پیشرفت کاربران</th>
                                <th>پیشرفت مراقبت‌ها</th>
                                <th>آخرین بروزرسانی</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($todayRuns as $todayRun)
                                @php
                                    $meta = $statusMeta[$todayRun->status] ?? ['label' => $todayRun->status, 'color' => 'grey'];
                                    $userPercent = $todayRun->total_users > 0
                                        ? (int) round(($todayRun->processed_users / $todayRun->total_users) * 100)
                                        : 0;
                                    $carePercent = $todayRun->total_cares > 0
                                        ? (int) round(($todayRun->processed_cares / $todayRun->total_cares) * 100)
                                        : 0;
                                @endphp
                                <tr class="{{ $getRowClass($todayRun->status) }}">
                                    <td data-label="شناسه اجرا">
                                        <span class="run-id">#{{ $todayRun->id }}</span>
                                    </td>
                                    <td data-label="نوع مراقبت">{{ $todayRun->input['care_type'] ?? '-' }}</td>
                                    <td data-label="وضعیت">
                                        <span class="ui mini {{ $meta['color'] }} label">{{ $meta['label'] }}</span>
                                    </td>
                                    <td data-label="پیشرفت کاربران" class="progress-cell">
                                        <div class="ui tiny progress">
                                            <div class="bar" style="width: {{ $userPercent }}%;"></div>
                                        </div>
                                        <small>
                                            {{ $todayRun->processed_users ?? 0 }}
                                            از
                                            {{ $todayRun->total_users ?? 0 }}
                                            کاربر
                                        </small>
                                    </td>
                                    <td data-label="پیشرفت مراقبت‌ها" class="progress-cell">
                                        <div class="ui tiny progress">
                                            <div class="bar" style="width: {{ $carePercent }}%;"></div>
                                        </div>
                                        <small>
                                            {{ $todayRun->processed_cares ?? 0 }}
                                            از
                                            {{ $todayRun->total_cares ?? 0 }}
                                            مراقبت
                                        </small>
                                    </td>
                                    <td data-label="آخرین بروزرسانی">
                                        {{ $jalaliDate($todayRun->updated_at, 'Y/m/d H:i:s') }}
                                    </td>
                                    <td data-label="عملیات">
                                        <div class="run-actions">
                                            <a href="{{ route('automation.runs.show', $todayRun) }}" class="ui mini blue button">
                                                مانیتور
                                            </a>

                                            @if ($canRetry($todayRun->status))
                                                <button
                                                    type="button"
                                                    class="ui mini yellow icon button retry-run-btn"
                                                    data-id="{{ $todayRun->id }}"
                                                    title="اجرای مجدد"
                                                >
                                                    <i class="sync alternate icon"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div id="stuck-runs-section" style="display: {{ $stuckRuns->isEmpty() ? 'none' : 'block' }}; margin-top: 1.5rem;">
            <div class="ui negative raised segment dashboard-section">
                <div class="dashboard-section-header">
                    <h3 class="ui red header">
                        <i class="exclamation triangle icon"></i>
                        <div class="content">
                            فرآیندهای مشکوک به توقف
                            <div class="sub header">
                                بدون بروزرسانی بیش از {{ $stuckMinutes }} دقیقه
                            </div>
                        </div>
                    </h3>

                    <span class="ui circular red label" id="badge-stuck-count">
                        {{ $stuckRuns->count() }}
                    </span>
                </div>

                <div class="dashboard-table-wrapper">
                    <table class="ui celled compact table">
                        <thead>
                        <tr>
                            <th>Run ID</th>
                            <th>نوع مراقبت</th>
                            <th>آخرین فعالیت</th>
                            <th>مدت عدم فعالیت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody id="stuck-runs-tbody">
                        @foreach ($stuckRuns as $stuckRun)
                            @php
                                $inactiveMinutes = $stuckRun->updated_at
                                    ? now()->diffInMinutes($stuckRun->updated_at)
                                    : null;
                            @endphp
                            <tr class="negative">
                                <td data-label="شناسه اجرا">
                                    <span class="run-id">#{{ $stuckRun->id }}</span>
                                </td>
                                <td data-label="نوع مراقبت">{{ $stuckRun->input['care_type'] ?? '-' }}</td>
                                <td data-label="آخرین فعالیت">
                                    {{ $jalaliDate($stuckRun->updated_at, 'Y/m/d H:i:s') }}
                                </td>
                                <td data-label="مدت عدم فعالیت">
                                        <span class="ui mini orange label">
                                            {{ $inactiveMinutes ?? '-' }} دقیقه
                                        </span>
                                </td>
                                <td data-label="عملیات">
                                    <div class="run-actions">
                                        <a href="{{ route('automation.runs.show', $stuckRun) }}" class="ui mini red button">
                                            بررسی
                                        </a>

                                        @if ($canRetry($stuckRun->status))
                                            <button
                                                type="button"
                                                class="ui mini yellow icon button retry-run-btn"
                                                data-id="{{ $stuckRun->id }}"
                                                title="اجرای مجدد"
                                            >
                                                <i class="sync alternate icon"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ui raised segment dashboard-section" style="margin-top: 1.5rem;">
            <div class="dashboard-section-header">
                <h3 class="ui header">
                    <i class="history icon"></i>
                    <div class="content">
                        آخرین فرآیندها
                        <div class="sub header">نمایش محدود آخرین اجراهای ثبت‌شده</div>
                    </div>
                </h3>

                <a href="{{ route('automation.runs.index') }}" class="ui small primary button">
                    <i class="list icon"></i>
                    مشاهده بیشتر
                </a>
            </div>

            <div id="recent-runs-container">
                @if ($runs->isEmpty())
                    <div class="ui warning message empty-state">
                        فرآیندی برای نمایش وجود ندارد.
                    </div>
                @else
                    <div class="dashboard-table-wrapper">
                        <table class="ui celled striped compact table">
                            <thead>
                            <tr>
                                <th>Run ID</th>
                                <th>نوع مراقبت</th>
                                <th>وضعیت</th>
                                <th>پیشرفت کاربران</th>
                                <th>پیشرفت مراقبت‌ها</th>
                                <th>زمان ثبت</th>
                                <th>پایان عملیات</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($runs as $run)
                                @php
                                    $meta = $statusMeta[$run->status] ?? ['label' => $run->status, 'color' => 'grey'];
                                    $userPercent = $run->total_users > 0
                                        ? (int) round(($run->processed_users / $run->total_users) * 100)
                                        : 0;
                                    $carePercent = $run->total_cares > 0
                                        ? (int) round(($run->processed_cares / $run->total_cares) * 100)
                                        : 0;
                                @endphp
                                <tr class="{{ $getRowClass($run->status) }}">
                                    <td data-label="شناسه اجرا">
                                        <span class="run-id">#{{ $run->id }}</span>
                                    </td>
                                    <td data-label="نوع مراقبت">{{ $run->input['care_type'] ?? '-' }}</td>
                                    <td data-label="وضعیت">
                                        <span class="ui mini {{ $meta['color'] }} label">{{ $meta['label'] }}</span>
                                    </td>
                                    <td data-label="پیشرفت کاربران" class="progress-cell">
                                        <div class="ui tiny progress">
                                            <div class="bar" style="width: {{ $userPercent }}%;"></div>
                                        </div>
                                        <small>
                                            {{ $run->processed_users ?? 0 }}
                                            از
                                            {{ $run->total_users ?? 0 }}
                                            کاربر
                                        </small>
                                    </td>
                                    <td data-label="پیشرفت مراقبت‌ها" class="progress-cell">
                                        <div class="ui tiny progress">
                                            <div class="bar" style="width: {{ $carePercent }}%;"></div>
                                        </div>
                                        <small>
                                            {{ $run->processed_cares ?? 0 }}
                                            از
                                            {{ $run->total_cares ?? 0 }}
                                            مراقبت
                                        </small>
                                    </td>
                                    <td data-label="زمان ثبت">{{ $jalaliDate($run->created_at) }}</td>
                                    <td data-label="پایان عملیات">{{ $jalaliDate($run->finished_at) }}</td>
                                    <td data-label="عملیات">
                                        <div class="run-actions">
                                            <a href="{{ route('automation.runs.show', $run) }}" class="ui mini basic primary button">
                                                مشاهده
                                            </a>

                                            @if ($canRetry($run->status))
                                                <button
                                                    type="button"
                                                    class="ui mini yellow icon button retry-run-btn"
                                                    data-id="{{ $run->id }}"
                                                    title="اجرای مجدد"
                                                >
                                                    <i class="sync alternate icon"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="dashboard-toast-container" class="dashboard-toast-container" aria-live="polite"></div>
@endsection

@push('scripts')
    <script>
        (function () {
            const pollUrl = @json(route('dashboard.poll'));
            const runsIndexUrl = @json(route('automation.runs.index'));
            const retryUrlTemplate = @json($retryUrlTemplate);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const statusMeta = {
                pending: { label: 'در انتظار', color: 'grey' },
                running: { label: 'در حال اجرا', color: 'blue' },
                done: { label: 'تکمیل شده', color: 'green' },
                failed: { label: 'خطادار', color: 'red' },
                partial_failed: { label: 'خطای جزئی', color: 'orange' },
                cancelled: { label: 'لغو شده', color: 'black' },
                paused: { label: 'متوقف شده', color: 'yellow' },
                skipped: { label: 'رد شده', color: 'teal' }
            };

            const retryableStatuses = ['failed', 'partial_failed', 'cancelled'];

            let isPolling = false;

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '-';
                return div.innerHTML;
            }

            function isRetryable(status) {
                return retryableStatuses.includes(status);
            }

            function getRetryUrl(runId) {
                return retryUrlTemplate.replace('__RUN_ID__', encodeURIComponent(runId));
            }

            function getRowClass(status) {
                if (status === 'failed') {
                    return 'negative';
                }

                if (status === 'running' || status === 'partial_failed') {
                    return 'warning';
                }

                if (status === 'cancelled') {
                    return 'active';
                }

                return '';
            }

            function showToast(message, type = 'success') {
                const container = document.getElementById('dashboard-toast-container');

                if (!container) {
                    return;
                }

                const toast = document.createElement('div');
                toast.className = `dashboard-toast ${type}`;
                toast.textContent = message;

                container.innerHTML = '';
                container.appendChild(toast);

                window.setTimeout(function () {
                    toast.remove();
                }, 4000);
            }

            function setLiveStatus(status) {
                const element = document.getElementById('dashboard-live-status');

                if (!element) {
                    return;
                }

                const statuses = {
                    connected: { className: '', label: 'اتصال فعال' },
                    loading: { className: 'is-loading', label: 'در حال بروزرسانی' },
                    error: { className: 'is-error', label: 'خطا در ارتباط' }
                };

                const currentStatus = statuses[status] || statuses.connected;

                element.className = `dashboard-live-status ${currentStatus.className}`;
                element.innerHTML = `
                    <span class="dashboard-live-dot"></span>
                    ${currentStatus.label}
                `;
            }

            function updateRefreshTime() {
                const element = document.getElementById('last-refresh-at');

                if (!element) {
                    return;
                }

                element.textContent = new Date().toLocaleTimeString('fa-IR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            function renderEmptyState(type, message) {
                return `
                    <div class="ui ${type} message empty-state">
                        <i class="info circle icon"></i>
                        ${message}
                    </div>
                `;
            }

            function renderProgressCell(percent, processed, total, label, dataLabel) {
                return `
                    <td data-label="${escapeHtml(dataLabel)}" class="progress-cell">
                        <div class="ui tiny progress">
                            <div class="bar" style="width: ${Number(percent) || 0}%"></div>
                        </div>
                        <small>
                            ${escapeHtml(processed ?? 0)}
                            از
                            ${escapeHtml(total ?? 0)}
                            ${escapeHtml(label)}
                        </small>
                    </td>
                `;
            }

            function renderActions(run, buttonClass = 'blue') {
                const retryButton = isRetryable(run.status)
                    ? `
                        <button
                            type="button"
                            class="ui mini yellow icon button retry-run-btn"
                            data-id="${escapeHtml(run.id)}"
                            title="اجرای مجدد"
                        >
                            <i class="sync alternate icon"></i>
                        </button>
                    `
                    : '';

                const viewLabel = buttonClass.includes('blue') ? 'مانیتور' : 'مشاهده';

                return `
                    <td data-label="عملیات">
                        <div class="run-actions">
                            <a
                                href="${escapeHtml(run.show_url)}"
                                class="ui mini ${buttonClass} button"
                            >
                                ${viewLabel}
                            </a>
                            ${retryButton}
                        </div>
                    </td>
                `;
            }

            function renderPendingRuns(runs) {
                const container = document.getElementById('pending-runs-container');

                if (!container) {
                    return;
                }

                if (!runs || runs.length === 0) {
                    container.innerHTML = renderEmptyState('positive', 'در حال حاضر فرآیندی در صف انتظار وجود ندارد.');
                    return;
                }

                let html = `
                    <div class="dashboard-table-wrapper">
                        <table class="ui celled striped compact table">
                            <thead>
                                <tr>
                                    <th>Run ID</th>
                                    <th>نوع مراقبت</th>
                                    <th>تعداد کاربران</th>
                                    <th>تعداد مراقبت‌ها</th>
                                    <th>زمان ثبت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                runs.forEach(function (run) {
                    html += `
                        <tr>
                            <td data-label="شناسه اجرا">
                                <span class="run-id">#${escapeHtml(run.id)}</span>
                            </td>
                            <td data-label="نوع مراقبت">${escapeHtml(run.care_type)}</td>
                            <td data-label="تعداد کاربران">${escapeHtml(run.total_users ?? 0)}</td>
                            <td data-label="تعداد مراقبت‌ها">${escapeHtml(run.total_cares ?? 0)}</td>
                            <td data-label="زمان ثبت">${escapeHtml(run.created_at)}</td>
                            <td data-label="عملیات">
                                <div class="run-actions">
                                    <a href="${escapeHtml(run.show_url)}" class="ui mini basic button">
                                        مشاهده
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                container.innerHTML = html;
            }

            function renderTodayRuns(runs) {
                const container = document.getElementById('today-runs-container');

                if (!container) {
                    return;
                }

                if (!runs || runs.length === 0) {
                    container.innerHTML = renderEmptyState('info', 'امروز فرآیند جدیدی ثبت نشده است.');
                    return;
                }

                let html = `
                    <div class="dashboard-table-wrapper">
                        <table class="ui celled striped compact table">
                            <thead>
                                <tr>
                                    <th>Run ID</th>
                                    <th>نوع مراقبت</th>
                                    <th>وضعیت</th>
                                    <th>پیشرفت کاربران</th>
                                    <th>پیشرفت مراقبت‌ها</th>
                                    <th>آخرین بروزرسانی</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                runs.forEach(function (run) {
                    const meta = statusMeta[run.status] || {
                        label: run.status,
                        color: 'grey'
                    };

                    html += `
                        <tr class="${getRowClass(run.status)}">
                            <td data-label="شناسه اجرا">
                                <span class="run-id">#${escapeHtml(run.id)}</span>
                            </td>
                            <td data-label="نوع مراقبت">${escapeHtml(run.care_type)}</td>
                            <td data-label="وضعیت">
                                <span class="ui mini ${meta.color} label">
                                    ${escapeHtml(meta.label)}
                                </span>
                            </td>
                            ${renderProgressCell(
                        run.user_percent,
                        run.processed_users,
                        run.total_users,
                        'کاربر',
                        'پیشرفت کاربران'
                    )}
                            ${renderProgressCell(
                        run.care_percent,
                        run.processed_cares,
                        run.total_cares,
                        'مراقبت',
                        'پیشرفت مراقبت‌ها'
                    )}
                            <td data-label="آخرین بروزرسانی">${escapeHtml(run.updated_at)}</td>
                            ${renderActions(run, 'blue')}
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                container.innerHTML = html;
            }

            function renderStuckRuns(runs) {
                const section = document.getElementById('stuck-runs-section');
                const tbody = document.getElementById('stuck-runs-tbody');

                if (!section || !tbody) {
                    return;
                }

                if (!runs || runs.length === 0) {
                    section.style.display = 'none';
                    tbody.innerHTML = '';
                    return;
                }

                section.style.display = 'block';

                let html = '';

                runs.forEach(function (run) {
                    html += `
                        <tr class="negative">
                            <td data-label="شناسه اجرا">
                                <span class="run-id">#${escapeHtml(run.id)}</span>
                            </td>
                            <td data-label="نوع مراقبت">${escapeHtml(run.care_type)}</td>
                            <td data-label="آخرین فعالیت">${escapeHtml(run.updated_at)}</td>
                            <td data-label="مدت عدم فعالیت">
                                <span class="ui mini orange label">
                                    ${escapeHtml(run.inactive_minutes ?? '-')} دقیقه
                                </span>
                            </td>
                            <td data-label="عملیات">
                                <div class="run-actions">
                                    <a href="${escapeHtml(run.show_url)}" class="ui mini red button">
                                        بررسی
                                    </a>
                                    ${
                        isRetryable(run.status)
                            ? `
                                                <button
                                                    type="button"
                                                    class="ui mini yellow icon button retry-run-btn"
                                                    data-id="${escapeHtml(run.id)}"
                                                    title="اجرای مجدد"
                                                >
                                                    <i class="sync alternate icon"></i>
                                                </button>
                                            `
                            : ''
                    }
                                </div>
                            </td>
                        </tr>
                    `;
                });

                tbody.innerHTML = html;
            }

            function renderRecentRuns(runs) {
                const container = document.getElementById('recent-runs-container');

                if (!container) {
                    return;
                }

                if (!runs || runs.length === 0) {
                    container.innerHTML = renderEmptyState('warning', 'فرآیندی برای نمایش وجود ندارد.');
                    return;
                }

                let html = `
                    <div class="dashboard-table-wrapper">
                        <table class="ui celled striped compact table">
                            <thead>
                                <tr>
                                    <th>Run ID</th>
                                    <th>نوع مراقبت</th>
                                    <th>وضعیت</th>
                                    <th>پیشرفت کاربران</th>
                                    <th>پیشرفت مراقبت‌ها</th>
                                    <th>زمان ثبت</th>
                                    <th>پایان عملیات</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                runs.forEach(function (run) {
                    const meta = statusMeta[run.status] || {
                        label: run.status,
                        color: 'grey'
                    };

                    html += `
                        <tr class="${getRowClass(run.status)}">
                            <td data-label="شناسه اجرا">
                                <span class="run-id">#${escapeHtml(run.id)}</span>
                            </td>
                            <td data-label="نوع مراقبت">${escapeHtml(run.care_type)}</td>
                            <td data-label="وضعیت">
                                <span class="ui mini ${meta.color} label">
                                    ${escapeHtml(meta.label)}
                                </span>
                            </td>
                            ${renderProgressCell(
                        run.user_percent,
                        run.processed_users,
                        run.total_users,
                        'کاربر',
                        'پیشرفت کاربران'
                    )}
                            ${renderProgressCell(
                        run.care_percent,
                        run.processed_cares,
                        run.total_cares,
                        'مراقبت',
                        'پیشرفت مراقبت‌ها'
                    )}
                            <td data-label="زمان ثبت">${escapeHtml(run.created_at)}</td>
                            <td data-label="پایان عملیات">${escapeHtml(run.finished_at)}</td>
                            ${renderActions(run, 'basic primary')}
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                container.innerHTML = html;
            }

            function updateStats(stats) {
                document.getElementById('stat-total-runs').textContent = stats.total_runs ?? 0;
                document.getElementById('stat-today-runs').textContent = stats.today_runs ?? 0;
                document.getElementById('stat-pending-runs').textContent = stats.pending_runs ?? 0;
                document.getElementById('stat-running-runs').textContent = stats.running_runs ?? 0;
                document.getElementById('stat-failed-runs').textContent = stats.failed_runs ?? 0;
            }

            function updateBadges(data) {
                const pending = document.getElementById('badge-pending-count');
                const today = document.getElementById('badge-today-count');
                const stuck = document.getElementById('badge-stuck-count');

                if (pending) {
                    pending.textContent = data.pendingRuns?.length ?? 0;
                }

                if (today) {
                    today.textContent = data.todayRuns?.length ?? 0;
                }

                if (stuck) {
                    stuck.textContent = data.stuckRuns?.length ?? 0;
                }
            }

            async function pollDashboardData() {
                if (isPolling) {
                    return;
                }

                isPolling = true;
                setLiveStatus('loading');

                try {
                    const response = await fetch(pollUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Polling failed');
                    }

                    const data = await response.json();

                    updateStats(data.stats || {});
                    updateBadges(data);
                    renderPendingRuns(data.pendingRuns || []);
                    renderTodayRuns(data.todayRuns || []);
                    renderStuckRuns(data.stuckRuns || []);
                    renderRecentRuns(data.runs || []);
                    updateRefreshTime();
                    setLiveStatus('connected');
                } catch (error) {
                    console.error(error);
                    setLiveStatus('error');
                } finally {
                    isPolling = false;
                }
            }

            async function retryRun(button) {
                const runId = button.dataset.id;

                if (!runId || button.disabled) {
                    return;
                }

                const originalHtml = button.innerHTML;

                button.disabled = true;
                button.classList.add('loading');
                button.innerHTML = '<i class="spinner loading icon"></i>';

                try {
                    const response = await fetch(getRetryUrl(runId), {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({})
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'اجرای مجدد انجام نشد.');
                    }

                    showToast(data.message || 'فرآیند برای اجرای مجدد ثبت شد.', 'success');
                    await pollDashboardData();
                } catch (error) {
                    showToast(error.message || 'خطا در ارتباط با سرور.', 'error');
                    button.disabled = false;
                    button.classList.remove('loading');
                    button.innerHTML = originalHtml;
                }
            }

            document.addEventListener('click', function (event) {
                const retryButton = event.target.closest('.retry-run-btn');

                if (retryButton) {
                    event.preventDefault();
                    retryRun(retryButton);
                    return;
                }

                const refreshButton = event.target.closest('#refresh-page');

                if (refreshButton) {
                    event.preventDefault();
                    pollDashboardData();
                }
            });

            updateRefreshTime();
            window.setInterval(pollDashboardData, 5000);
        })();
    </script>
@endpush
