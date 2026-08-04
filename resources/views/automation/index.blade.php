@extends('layouts.app')

@section('title', 'داشبورد جاب‌های اتوماسیون')

@push('styles')
    <style>
        :root {
            --automation-primary: #2185d0;
            --automation-dark: #173f5f;
            --automation-success: #21ba45;
            --automation-danger: #db2828;
            --automation-warning: #f2c037;
            --automation-border: #e5e7eb;
            --automation-muted: #6b7280;
            --automation-background: #f5f7fa;
        }

        body {
            background-color: var(--automation-background);
            font-family: Tahoma, Geneva, sans-serif;
        }

        .automation-page {
            width: 100%;
            padding-top: 1.5rem;
            padding-bottom: 2rem;
            direction: rtl;
        }

        .automation-section {
            padding: 1.25rem !important;
            border: 0 !important;
            border-radius: 14px !important;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.07) !important;
        }

        .automation-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--automation-border);
        }

        .automation-section-title .ui.header {
            margin: 0;
        }

        .automation-section-title .sub.header {
            margin-top: 0.35rem !important;
            color: var(--automation-muted) !important;
            font-size: 0.78rem !important;
            font-weight: normal;
            line-height: 1.8;
        }

        .automation-table-wrapper {
            width: 100%;
        }

        .automation-table {
            width: 100%;
            table-layout: fixed;
            border: 1px solid var(--automation-border) !important;
            border-radius: 10px !important;
            overflow: hidden;
        }

        .automation-table thead th {
            background: #f8fafc !important;
            color: #374151 !important;
            font-size: 0.8rem !important;
            font-weight: bold !important;
            text-align: right !important;
            white-space: nowrap;
        }

        .automation-table th,
        .automation-table td {
            vertical-align: middle !important;
            text-align: right !important;
        }

        .automation-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .automation-table tbody tr:hover {
            background-color: #f8fbff !important;
        }

        .automation-table tbody tr.row-failed {
            background-color: #fff7f7;
        }

        .automation-table tbody tr.row-running {
            background-color: #fffdf2;
        }

        .run-id {
            display: inline-block;
            color: var(--automation-dark);
            direction: ltr;
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .care-type-label {
            display: inline-block !important;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .progress-wrapper {
            min-width: 120px;
        }

        .progress-wrapper .ui.progress {
            margin: 0 0 0.35rem !important;
        }

        .progress-info {
            display: block;
            color: var(--automation-muted);
            font-size: 0.7rem;
            line-height: 1.8;
            white-space: nowrap;
        }

        .run-actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .run-actions .button {
            margin: 0 !important;
        }

        .retry-run-button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .retry-run-button:disabled {
            cursor: not-allowed;
        }

        .empty-state {
            padding: 2rem 1rem !important;
            border-radius: 10px !important;
            text-align: center;
            line-height: 2;
        }

        .automation-pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.25rem;
        }

        .automation-toast-container {
            position: fixed;
            z-index: 9999;
            top: 1rem;
            left: 1rem;
            right: 1rem;
            display: flex;
            justify-content: center;
            pointer-events: none;
        }

        .automation-toast {
            width: min(430px, 100%);
            padding: 0.9rem 1rem;
            border-radius: 9px;
            color: #fff;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.2);
            font-size: 0.85rem;
            line-height: 1.8;
            text-align: center;
            pointer-events: auto;
        }

        .automation-toast.success {
            background-color: var(--automation-success);
        }

        .automation-toast.error {
            background-color: var(--automation-danger);
        }

        .automation-toast.warning {
            background-color: #f2711c;
        }

        @media only screen and (max-width: 991px) {
            .automation-table th,
            .automation-table td {
                padding: 0.65rem 0.5rem !important;
                font-size: 0.78rem !important;
            }

            .progress-wrapper {
                min-width: 100px;
            }
        }

        @media only screen and (max-width: 767px) {
            .automation-page {
                padding-top: 0.75rem;
                padding-bottom: 1rem;
            }

            .automation-section {
                padding: 0.8rem !important;
            }

            .automation-section-title {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.65rem;
            }

            .automation-section-title .ui.header {
                font-size: 1rem !important;
                line-height: 1.8;
            }

            .automation-table-wrapper {
                overflow: visible;
            }

            .automation-table,
            .automation-table tbody,
            .automation-table tr,
            .automation-table td {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }

            .automation-table {
                border: 0 !important;
                background-color: transparent !important;
            }

            .automation-table thead {
                display: none;
            }

            .automation-table tbody {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }

            .automation-table tbody tr {
                padding: 0.85rem !important;
                border: 1px solid var(--automation-border) !important;
                border-radius: 12px !important;
                background-color: #fff !important;
                box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06);
            }

            .automation-table tbody tr.row-failed {
                border-right: 4px solid var(--automation-danger) !important;
            }

            .automation-table tbody tr.row-running {
                border-right: 4px solid var(--automation-warning) !important;
            }

            .automation-table td {
                display: flex !important;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
                min-height: 34px;
                padding: 0.5rem 0 !important;
                border: 0 !important;
                border-bottom: 1px solid #f0f1f3 !important;
                direction: rtl;
                text-align: right !important;
                word-break: break-word;
            }

            .automation-table td:last-child {
                padding-bottom: 0 !important;
                border-bottom: 0 !important;
            }

            .automation-table td::before {
                flex: 0 0 38%;
                color: var(--automation-muted);
                content: attr(data-label);
                font-size: 0.7rem;
                font-weight: bold;
                line-height: 1.8;
                text-align: right;
            }

            .automation-table td > * {
                width: 62%;
                max-width: 62%;
                margin: 0 !important;
                text-align: right !important;
            }

            .automation-table .run-id {
                width: auto;
                max-width: 100%;
                direction: ltr;
                text-align: right !important;
            }

            .automation-table .care-type-label {
                width: 62%;
                max-width: 62%;
            }

            .progress-wrapper {
                width: 62% !important;
                min-width: 0;
                max-width: 62% !important;
            }

            .progress-wrapper .ui.progress {
                width: 100%;
            }

            .progress-info {
                overflow: hidden;
                font-size: 0.66rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .run-actions {
                width: 62% !important;
                max-width: 62% !important;
                justify-content: flex-start;
                direction: rtl;
            }

            .run-actions .button {
                font-size: 0.7rem !important;
            }

            .automation-pagination {
                overflow-x: auto;
                justify-content: flex-start;
                padding-bottom: 0.3rem;
            }
        }

        @media only screen and (max-width: 420px) {
            .automation-table td::before {
                flex-basis: 34%;
                font-size: 0.66rem;
            }

            .automation-table td > * {
                width: 66%;
                max-width: 66%;
            }

            .progress-wrapper,
            .run-actions {
                width: 66% !important;
                max-width: 66% !important;
            }

            .run-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .run-actions .button {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $statusMeta = [
            'pending' => [
                'label' => 'در انتظار',
                'color' => 'grey',
                'rowClass' => '',
            ],
            'running' => [
                'label' => 'در حال اجرا',
                'color' => 'yellow',
                'rowClass' => 'row-running',
            ],
            'done' => [
                'label' => 'تکمیل شده',
                'color' => 'green',
                'rowClass' => '',
            ],
            'failed' => [
                'label' => 'خطادار',
                'color' => 'red',
                'rowClass' => 'row-failed',
            ],
            'partial_failed' => [
                'label' => 'خطای جزئی',
                'color' => 'orange',
                'rowClass' => 'row-failed',
            ],
            'cancelled' => [
                'label' => 'لغو شده',
                'color' => 'black',
                'rowClass' => 'row-failed',
            ],
            'paused' => [
                'label' => 'متوقف شده',
                'color' => 'orange',
                'rowClass' => 'row-running',
            ],
        ];

        $retryableStatuses = [
            'failed',
            'partial_failed',
            'cancelled',
        ];

        $canRetry = static function (?string $status) use ($retryableStatuses): bool {
            return in_array($status, $retryableStatuses, true);
        };
    @endphp

    <div class="ui container automation-page">
        <div class="ui raised segment automation-section">
            <div class="automation-section-title">
                <div>
                    <h2 class="ui header">
                        <i class="history icon"></i>

                        <div class="content">
                            تاریخچه اجرای اتوماسیون‌ها

                            <div class="sub header">
                                آخرین اجراهای ثبت‌شده در سیستم
                            </div>
                        </div>
                    </h2>
                </div>

                <div class="ui small blue label">
                    {{ $runs->total() }} اجرا
                </div>
            </div>

            <div id="runs-table-container">
                @if ($runs->isEmpty())
                    <div class="ui placeholder segment empty-state">
                        <div class="ui icon header">
                            <i class="search icon"></i>
                            هیچ رکورد اتوماسیونی یافت نشد.
                        </div>
                    </div>
                @else
                    <div class="automation-table-wrapper">
                        <table class="ui celled striped compact table automation-table">
                            <thead>
                            <tr>
                                <th>شناسه Run</th>
                                <th>نوع مراقبت</th>
                                <th>وضعیت کلی</th>
                                <th>پیشرفت کاربران</th>
                                <th>پیشرفت مراقبت‌ها</th>
                                <th>تاریخ شروع</th>
                                <th>تاریخ پایان</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($runs as $run)
                                @php
                                    $meta = $statusMeta[$run->status] ?? [
                                        'label' => $run->status ?? 'نامشخص',
                                        'color' => 'grey',
                                        'rowClass' => '',
                                    ];

                                    $userPercent = $run->total_users > 0
                                        ? min(
                                            100,
                                            (int) round(
                                                ($run->processed_users / $run->total_users) * 100
                                            )
                                        )
                                        : 0;

                                    $carePercent = $run->total_cares > 0
                                        ? min(
                                            100,
                                            (int) round(
                                                ($run->processed_cares / $run->total_cares) * 100
                                            )
                                        )
                                        : 0;
                                @endphp

                                <tr
                                    class="{{ $meta['rowClass'] }}"
                                    data-run-id="{{ $run->id }}"
                                >
                                    <td data-label="شناسه Run">
                                            <span class="run-id">
                                                #{{ $run->id }}
                                            </span>
                                    </td>

                                    <td data-label="نوع مراقبت">
                                            <span class="ui basic blue label care-type-label">
                                                {{ $run->input['care_type'] ?? 'نامشخص' }}
                                            </span>
                                    </td>

                                    <td data-label="وضعیت کلی">
                                            <span class="ui mini {{ $meta['color'] }} label">
                                                {{ $meta['label'] }}
                                            </span>
                                    </td>

                                    <td data-label="پیشرفت کاربران">
                                        <div class="progress-wrapper">
                                            <div
                                                class="ui tiny progress {{ $meta['color'] }}"
                                                data-percent="{{ $userPercent }}"
                                            >
                                                <div
                                                    class="bar"
                                                    style="width: {{ $userPercent }}%;"
                                                ></div>
                                            </div>

                                            <span class="progress-info">
                                                    {{ $run->processed_users ?? 0 }}
                                                    از
                                                    {{ $run->total_users ?? 0 }}
                                                    کاربر
                                                </span>
                                        </div>
                                    </td>

                                    <td data-label="پیشرفت مراقبت‌ها">
                                        <div class="progress-wrapper">
                                            <div
                                                class="ui tiny progress {{ $meta['color'] }}"
                                                data-percent="{{ $carePercent }}"
                                            >
                                                <div
                                                    class="bar"
                                                    style="width: {{ $carePercent }}%;"
                                                ></div>
                                            </div>

                                            <span class="progress-info">
                                                    {{ $run->processed_cares ?? 0 }}
                                                    از
                                                    {{ $run->total_cares ?? 0 }}
                                                    مراقبت
                                                </span>
                                        </div>
                                    </td>

                                    <td data-label="تاریخ شروع">
                                        {{ $run->started_at? \Morilog\Jalali\Jalalian::fromCarbon($run->started_at)->format('Y/m/d H:i'): '-'}}
                                    </td>
                                    <td data-label="تاریخ پایان">
                                        {{ $run->finished_at? \Morilog\Jalali\Jalalian::fromCarbon($run->finished_at)->format('Y/m/d H:i'): '-'}}
                                    </td>

                                    <td data-label="عملیات">
                                        <div class="run-actions">
                                            <a
                                                href="{{ route('automation.runs.show', $run->id) }}"
                                                class="ui mini primary button"
                                            >
                                                <i class="eye icon"></i>
                                                مانیتور
                                            </a>

                                            @if ($canRetry($run->status))
                                                <button
                                                    type="button"
                                                    class="ui mini yellow button retry-run-button"
                                                    data-run-id="{{ $run->id }}"
                                                >
                                                    <i class="sync alternate icon"></i>
                                                    Retry
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="automation-pagination">
                        {{ $runs->links('pagination::semantic-ui') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div
        class="automation-toast-container"
        id="automation-toast-container"
        aria-live="polite"
    ></div>
@endsection

@push('scripts')
    <script>
        (function () {
            const retryUrlTemplate = @json(
                route('automation.runs.retry', ['run' => '__RUN_ID__'])
            );

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '';

            function getRetryUrl(runId) {
                return retryUrlTemplate.replace(
                    '__RUN_ID__',
                    encodeURIComponent(runId)
                );
            }

            function showToast(message, type = 'success') {
                const container = document.getElementById(
                    'automation-toast-container'
                );

                if (!container) {
                    return;
                }

                const toast = document.createElement('div');

                toast.className = `automation-toast ${type}`;
                toast.textContent = message;

                container.innerHTML = '';
                container.appendChild(toast);

                window.setTimeout(function () {
                    toast.remove();
                }, 4500);
            }

            function updateRowAfterRetry(button) {
                const row = button.closest('tr');

                if (!row) {
                    return;
                }

                row.classList.remove('row-failed');
                row.classList.add('row-running');

                const statusCell = row.querySelector(
                    'td[data-label="وضعیت کلی"]'
                );

                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="ui mini yellow label">
                            در صف اجرای مجدد
                        </span>
                    `;
                }

                button.remove();
            }

            async function retryRun(button) {
                const runId = button.dataset.runId;

                if (!runId || button.disabled) {
                    return;
                }

                const originalHtml = button.innerHTML;

                button.disabled = true;
                button.classList.add('loading');
                button.innerHTML = `
                    <i class="spinner loading icon"></i>
                    در حال ارسال
                `;

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

                    let data = {};

                    try {
                        data = await response.json();
                    } catch (error) {
                        data = {};
                    }

                    if (!response.ok) {
                        throw new Error(
                            data.message || 'اجرای مجدد جاب انجام نشد.'
                        );
                    }

                    showToast(
                        data.message || 'جاب برای اجرای مجدد ثبت شد.',
                        'success'
                    );

                    updateRowAfterRetry(button);

                    window.setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } catch (error) {
                    console.error(error);

                    showToast(
                        error.message || 'خطا در ارتباط با سرور.',
                        'error'
                    );

                    button.disabled = false;
                    button.classList.remove('loading');
                    button.innerHTML = originalHtml;
                }
            }

            document.addEventListener('click', function (event) {
                const retryButton = event.target.closest(
                    '.retry-run-button'
                );

                if (!retryButton) {
                    return;
                }

                event.preventDefault();
                retryRun(retryButton);
            });
        })();
    </script>
@endpush
