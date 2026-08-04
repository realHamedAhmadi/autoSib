@extends('layouts.app')

@section('title', "جزئیات اتوماسیون شماره #{$run->id}")

@php
    $jalaliStartedAt = $run->started_at
        ? \Morilog\Jalali\Jalalian::fromCarbon($run->started_at)->format('Y/m/d H:i')
        : 'هنوز شروع نشده';

    $jalaliFinishedAt = $run->finished_at
        ? \Morilog\Jalali\Jalalian::fromCarbon($run->finished_at)->format('Y/m/d H:i')
        : 'در حال پردازش...';
@endphp

@push('styles')
    <style>
        body {
            background-color: #f9fafb;
            font-family: Tahoma, Geneva, sans-serif;
            padding: 20px;
            direction: rtl;
        }

        .ui.progress .bar {
            min-width: 0 !important;
            transition: width 0.5s ease;
        }

        .automation-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .automation-header .ui.header {
            margin: 0;
        }

        .automation-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .automation-actions .button {
            margin: 0 !important;
        }

        .retry-run-button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .automation-statistics {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            width: 100%;
        }

        .automation-statistics .statistic {
            margin: 0 !important;
        }

        .users-table {
            width: 100%;
            table-layout: fixed;
        }

        .users-table th,
        .users-table td {
            vertical-align: middle !important;
            text-align: right !important;
            word-break: break-word;
        }

        .users-table .progress-wrapper {
            min-width: 110px;
        }

        .users-table .progress-wrapper .ui.progress {
            margin: 0 0 0.35rem !important;
        }

        .users-table .progress-info {
            display: block;
            color: #6b7280;
            font-size: 0.7rem;
            white-space: nowrap;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .user-actions .button {
            margin: 0 !important;
        }

        .cares-panel-content {
            width: 100%;
        }

        .cares-steps {
            width: 100% !important;
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
            padding: 0.85rem 1rem;
            border-radius: 8px;
            color: #fff;
            font-size: 0.85rem;
            line-height: 1.8;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            pointer-events: auto;
        }

        .automation-toast.success {
            background-color: #21ba45;
        }

        .automation-toast.error {
            background-color: #db2828;
        }

        @media only screen and (max-width: 991px) {
            body {
                padding: 12px;
            }

            .automation-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .automation-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .automation-statistics {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media only screen and (max-width: 767px) {
            body {
                padding: 8px;
            }

            .ui.segment {
                padding: 0.8rem !important;
            }

            .automation-header .ui.header {
                font-size: 1.05rem !important;
                line-height: 1.8;
            }

            .automation-header .sub.header {
                font-size: 0.72rem !important;
                line-height: 1.8;
            }

            .automation-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .automation-actions .button,
            .automation-actions .label {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .automation-statistics {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .automation-statistics .statistic {
                padding: 0.6rem !important;
            }

            .automation-statistics .value {
                font-size: 1.45rem !important;
            }

            .automation-statistics .label {
                font-size: 0.68rem !important;
                line-height: 1.7;
            }

            .users-table,
            .users-table tbody,
            .users-table tr,
            .users-table td {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }

            .users-table {
                border: 0 !important;
                background: transparent !important;
            }

            .users-table thead {
                display: none;
            }

            .users-table tbody {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .users-table tbody tr {
                padding: 0.8rem !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 10px !important;
                background: #fff !important;
                box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06);
            }

            .users-table tbody tr.warning {
                border-right: 4px solid #f2c037 !important;
            }

            .users-table tbody tr.negative {
                border-right: 4px solid #db2828 !important;
            }

            .users-table tbody tr.positive {
                border-right: 4px solid #21ba45 !important;
            }

            .users-table td {
                display: flex !important;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.7rem;
                min-height: 34px;
                padding: 0.45rem 0 !important;
                border: 0 !important;
                border-bottom: 1px solid #f0f1f3 !important;
                text-align: right !important;
            }

            .users-table td:last-child {
                border-bottom: 0 !important;
            }

            .users-table td::before {
                flex: 0 0 35%;
                color: #6b7280;
                content: attr(data-label);
                font-size: 0.7rem;
                font-weight: bold;
                line-height: 1.8;
            }

            .users-table td > * {
                width: 65%;
                max-width: 65%;
                margin: 0 !important;
            }

            .users-table .progress-wrapper {
                width: 65%;
                min-width: 0;
                max-width: 65%;
            }

            .users-table .progress-info {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .user-actions {
                width: 65%;
                max-width: 65%;
                align-items: stretch;
                flex-direction: column;
            }

            .user-actions .button {
                width: 100%;
            }

            #cares-detail-panel .ui.message {
                font-size: 0.75rem;
                line-height: 1.8;
            }

            .cares-steps .step {
                padding: 0.8rem !important;
            }

            .cares-steps .title {
                font-size: 0.8rem !important;
            }

            .cares-steps .description {
                font-size: 0.7rem !important;
                line-height: 1.8;
                word-break: break-word;
            }
        }

        @media only screen and (max-width: 420px) {
            .automation-statistics {
                grid-template-columns: 1fr;
            }

            .users-table td::before {
                flex-basis: 32%;
                font-size: 0.65rem;
            }

            .users-table td > *,
            .users-table .progress-wrapper,
            .user-actions {
                width: 68%;
                max-width: 68%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="ui breadcrumb" style="margin-bottom: 20px;">
        <a href="{{ route('automation.runs.index') }}" class="section">
            داشبورد اتوماسیون‌ها
        </a>
        <i class="left angle icon divider"></i>
        <div class="active section">
            جزئیات اجرای جاب #{{ $run->id }}
        </div>
    </div>

    <div class="automation-header">
        <h2 class="ui header">
            <i class="cogs icon"></i>
            <div class="content">
                مانیتورینگ پروسه اتوماسیون
                <span dir="ltr">(Run #{{ $run->id }})</span>
                <div class="sub header">
                    مشاهده وضعیت کاربران، خطاها و مراحل اجرای اتوماسیون مراقبت
                </div>
            </div>
        </h2>

        <div class="automation-actions">
            <button
                type="button"
                class="ui yellow button retry-run-button"
                id="retry-run-button"
                data-run-id="{{ $run->id }}"
                style="display: none;"
            >
                <i class="sync alternate icon"></i>
                اجرای مجدد
            </button>

            <span class="ui basic blue label" id="live-indicator">
                <i class="circle icon"></i>
                زنده
            </span>
        </div>
    </div>

    <div class="ui segment raised" id="run-summary-panel">
        <h4 class="ui dividing header">خلاصه وضعیت جاب</h4>

        <div class="ui grid stackable">
            <div class="eight wide column">
                <div class="ui cards">
                    <div class="card fluid">
                        <div class="content">
                            <div
                                class="right floated ui label"
                                id="run-status-badge"
                            >
                                ...
                            </div>

                            <div class="header">اطلاعات جاب</div>

                            <div class="meta" style="margin-top: 5px;">
                                شناسه سیستمی: Run_{{ $run->id }}
                            </div>

                            <div class="description" style="margin-top: 10px;">
                                <p>
                                    <strong>نوع مراقبت:</strong>
                                    {{ $run->input['care_type'] ?? 'نامشخص' }}
                                </p>

                                <p>
                                    <strong>شروع شده در:</strong>
                                    <span id="run-started-at">
                                        {{ $jalaliStartedAt }}
                                    </span>
                                </p>

                                <p>
                                    <strong>پایان یافته در:</strong>
                                    <span id="run-finished-at">
                                        {{ $jalaliFinishedAt }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="eight wide column">
                <h5>پیشرفت کلی کاربران:</h5>
                <div class="ui indicating progress" id="user-progress-bar">
                    <div class="bar">
                        <div class="progress">0%</div>
                    </div>
                    <div class="label" id="user-progress-label">
                        در حال آماده‌سازی...
                    </div>
                </div>

                <h5>پیشرفت کلی مراقبت‌ها:</h5>
                <div class="ui indicating progress" id="care-progress-bar">
                    <div class="bar">
                        <div class="progress">0%</div>
                    </div>
                    <div class="label" id="care-progress-label">
                        در حال آماده‌سازی...
                    </div>
                </div>
            </div>
        </div>

        <div
            class="ui small statistics automation-statistics"
            style="margin-top: 20px;"
        >
            <div class="statistic blue">
                <div class="value" id="stat-total-users">0</div>
                <div class="label">کل کاربران</div>
            </div>

            <div class="statistic yellow">
                <div class="value" id="stat-processed-users">0</div>
                <div class="label">کاربران پردازش شده</div>
            </div>

            <div class="statistic teal">
                <div class="value" id="stat-total-cares">0</div>
                <div class="label">کل مراقبت‌ها</div>
            </div>

            <div class="statistic green">
                <div class="value" id="stat-processed-cares">0</div>
                <div class="label">مراقبت‌های موفق/پردازش شده</div>
            </div>
        </div>
    </div>

    <div class="ui grid stackable">
        <div class="ten wide column">
            <div class="ui segment raised">
                <h3 class="ui header">
                    <i class="users icon"></i>
                    لیست کاربران
                </h3>

                <table
                    class="ui celled striped compact table users-table"
                    id="users-table"
                >
                    <thead>
                    <tr>
                        <th>شناسه سیب</th>
                        <th>نام کاربر</th>
                        <th>وضعیت</th>
                        <th>پیشرفت مراقبت‌ها</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>

                    <tbody id="users-tbody">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="six wide column">
            <div
                class="ui segment raised card fluid"
                id="cares-detail-panel"
                style="display: none;"
            >
                <h3 class="ui header">
                    <i class="tasks icon"></i>
                    وضعیت تک‌تک مراقبت‌ها
                </h3>

                <div class="ui message info mini">
                    <p>
                        کاربر انتخاب شده:
                        <strong id="selected-user-name">...</strong>
                        (<span id="selected-user-sib-id"></span>)
                    </p>
                </div>

                <div
                    class="ui vertical steps fluid small cares-steps"
                    id="cares-steps-container"
                >
                </div>
            </div>

            <div class="ui placeholder segment" id="cares-placeholder">
                <div class="ui icon header">
                    <i class="tasks icon"></i>
                    برای مشاهده جزئیات مراقبت‌ها، روی دکمه «مشاهده جزئیات» یک کاربر کلیک کنید.
                </div>
            </div>
        </div>
    </div>

    <div
        class="automation-toast-container"
        id="automation-toast-container"
        aria-live="polite"
    ></div>
@stop

@push('scripts')
    <script>
        const runId = @json($run->id);
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

        let activeUserId = null;
        let autoPollInterval = null;

        const retryableRunStatuses = [
            'failed',
            'partial_failed',
            'cancelled'
        ];

        const statusHelper = {
            run: {
                pending: {
                    label: 'در انتظار',
                    color: 'grey'
                },
                running: {
                    label: 'در حال اجرا',
                    color: 'yellow'
                },
                done: {
                    label: 'تکمیل شده',
                    color: 'green'
                },
                failed: {
                    label: 'خطا خورده',
                    color: 'red'
                },
                partial_failed: {
                    label: 'خطای جزئی',
                    color: 'orange'
                },
                cancelled: {
                    label: 'لغو شده',
                    color: 'black'
                },
                paused: {
                    label: 'متوقف شده',
                    color: 'orange'
                }
            },

            user: {
                pending: {
                    badge: 'mini ui label grey',
                    text: 'در انتظار',
                    trClass: ''
                },
                running: {
                    badge: 'mini ui label yellow',
                    text: 'در حال اجرا',
                    trClass: 'warning'
                },
                done: {
                    badge: 'mini ui label green',
                    text: 'پایان یافته',
                    trClass: 'positive'
                },
                failed: {
                    badge: 'mini ui label red',
                    text: 'خطا خورده',
                    trClass: 'negative'
                },
                paused: {
                    badge: 'mini ui label orange',
                    text: 'متوقف',
                    trClass: 'warning'
                }
            },

            care: {
                pending: {
                    icon: 'hourglass start icon',
                    color: 'grey',
                    desc: 'در انتظار اجرا'
                },
                running: {
                    icon: 'spinner loading blue icon',
                    color: 'blue',
                    desc: 'در حال ارسال درخواست...'
                },
                done: {
                    icon: 'check circle green icon',
                    color: 'green',
                    desc: 'موفق'
                },
                skipped: {
                    icon: 'minus circle orange icon',
                    color: 'orange',
                    desc: 'نادیده گرفته شد'
                },
                failed: {
                    icon: 'exclamation circle red icon',
                    color: 'red',
                    desc: 'خطا در اجرا'
                }
            }
        };

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function showToast(message, type = 'success') {
            const container = $('#automation-toast-container');

            if (!container.length) {
                return;
            }

            const toast = $('<div>')
                .addClass(`automation-toast ${type}`)
                .text(message);

            container.empty().append(toast);

            window.setTimeout(function () {
                toast.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 4000);
        }

        function updateRetryButton(status) {
            const button = $('#retry-run-button');

            if (!button.length) {
                return;
            }

            const canRetry = retryableRunStatuses.includes(status);

            button.toggle(canRetry);
            button.prop('disabled', false);
            button.removeClass('loading');

            if (canRetry) {
                button.html(`
                    <i class="sync alternate icon"></i>
                    اجرای مجدد
                `);
            }
        }

        function updateRunSummary(data) {
            const runConfig = statusHelper.run[data.status] || {
                label: data.status || 'نامشخص',
                color: 'grey'
            };

            $('#run-status-badge')
                .text(runConfig.label)
                .attr('class', `right floated ui label ${runConfig.color}`);

            $('#stat-total-users').text(data.total_users ?? 0);
            $('#stat-processed-users').text(data.processed_users ?? 0);
            $('#stat-total-cares').text(data.total_cares ?? 0);
            $('#stat-processed-cares').text(data.processed_cares ?? 0);

            $('#user-progress-bar').progress({
                percent: data.user_percentage ?? 0
            });

            $('#user-progress-label').text(
                `${data.processed_users ?? 0} از ${data.total_users ?? 0} کاربر پردازش شده‌اند`
            );

            $('#care-progress-bar').progress({
                percent: data.care_percentage ?? 0
            });

            $('#care-progress-label').text(
                `${data.processed_cares ?? 0} از ${data.total_cares ?? 0} مراقبت تکمیل شده است`
            );

            if (data.started_at) {
                $('#run-started-at').text(data.started_at);
            }

            if (data.finished_at) {
                $('#run-finished-at').text(data.finished_at);
            }

            $('#live-indicator')
                .removeClass('red')
                .addClass('blue')
                .html('<i class="circle icon"></i> زنده');

            updateRetryButton(data.status);
        }

        function renderUsers(users) {
            let usersHtml = '';

            users.forEach(function (user) {
                const userConfig = statusHelper.user[user.status] || {
                    badge: 'mini ui label grey',
                    text: user.status || 'نامشخص',
                    trClass: ''
                };

                let errorButton = '';

                if (user.status === 'failed' && user.error_message) {
                    errorButton = `
                        <button
                            type="button"
                            class="ui mini red basic button popup-trigger"
                            data-content="خطا: ${escapeHtml(user.error_message)}"
                        >
                            <i class="bug icon"></i>
                            نمایش خطا
                        </button>
                    `;
                }

                const userName = user.name || user.user_name || user.sib_user_id;
                const progressPercent = user.progress_percent ?? 0;
                const progressColor = userConfig.trClass === 'negative'
                    ? 'red'
                    : userConfig.trClass === 'positive'
                        ? 'green'
                        : userConfig.trClass === 'warning'
                            ? 'yellow'
                            : 'blue';

                usersHtml += `
                    <tr class="${userConfig.trClass}">
                        <td data-label="شناسه سیب">
                            ${escapeHtml(user.sib_user_id)}
                        </td>

                        <td data-label="نام کاربر">
                            ${escapeHtml(userName)}
                        </td>

                        <td data-label="وضعیت">
                            <span class="${userConfig.badge}">
                                ${escapeHtml(userConfig.text)}
                            </span>
                        </td>

                        <td data-label="پیشرفت مراقبت‌ها">
                            <div class="progress-wrapper">
                                <div
                                    class="ui tiny progress ${progressColor}"
                                    data-percent="${progressPercent}"
                                >
                                    <div
                                        class="bar"
                                        style="width: ${progressPercent}%;"
                                    ></div>
                                </div>

                                <span class="progress-info">
                                    ${user.processed_cares ?? 0}
                                    از
                                    ${user.total_cares ?? 0}
                                    مراقبت
                                </span>
                            </div>
                        </td>

                        <td data-label="عملیات">
                            <div class="user-actions">
                                ${errorButton}

                                <button
                                    type="button"
                                    class="ui mini blue button btn-view-cares"
                                    data-user-id="${user.id}"
                                >
                                    <i class="eye icon"></i>
                                    مشاهده جزئیات
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#users-tbody').html(usersHtml);

            $('.popup-trigger').popup({
                position: 'top center'
            });
        }

        function fetchStatus() {
            $.ajax({
                url: `/automation/runs/${runId}/status`,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'Accept': 'application/json'
                },
                success: function (data) {
                    updateRunSummary(data);
                    renderUsers(data.users || []);

                    if (activeUserId) {
                        fetchUserCares(activeUserId);
                    }
                },
                error: function () {
                    $('#live-indicator')
                        .removeClass('blue')
                        .addClass('red')
                        .html('<i class="circle icon"></i> خطا در دریافت اطلاعات');
                }
            });
        }

        function fetchUserCares(userId) {
            activeUserId = userId;

            $.ajax({
                url: `/automation/users/${userId}/cares`,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'Accept': 'application/json'
                },
                success: function (data) {
                    $('#cares-placeholder').hide();
                    $('#cares-detail-panel').show();

                    $('#selected-user-name').text(data.user_name || '-');
                    $('#selected-user-sib-id').text(data.sib_user_id || '-');

                    let stepsHtml = '';

                    (data.cares || []).forEach(function (care) {
                        const careConfig = statusHelper.care[care.status] || {
                            icon: 'question icon',
                            color: 'grey',
                            desc: care.status || 'نامشخص'
                        };

                        let stepClass = 'step';

                        if (care.status === 'done' || care.status === 'skipped') {
                            stepClass = 'completed step';
                        }

                        if (care.status === 'running') {
                            stepClass = 'active step';
                        }

                        if (care.status === 'pending') {
                            stepClass = 'disabled step';
                        }

                        let errorDescription = '';

                        if (care.status === 'failed' && care.error_message) {
                            errorDescription = `
                                <div style="color:#db2828;font-size:11px;margin-top:5px;">
                                    <strong>خطا:</strong>
                                    ${escapeHtml(care.error_message)}
                                </div>
                            `;
                        }

                        stepsHtml += `
                            <div class="${stepClass}">
                                <i class="${careConfig.icon}"></i>

                                <div class="content">
                                    <div class="title">
                                        ${escapeHtml(care.name)}
                                    </div>

                                    <div class="description">
                                        تعداد تلاش: ${care.attempts ?? 0}
                                        |
                                        ترتیب: ${care.sort_order ?? '-'}
                                        <br>
                                        ${escapeHtml(careConfig.desc)}
                                        ${errorDescription}
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#cares-steps-container').html(stepsHtml);
                },
                error: function () {
                    showToast('خطا در دریافت جزئیات مراقبت‌های کاربر.', 'error');
                }
            });
        }

        function retryRun(button) {
            const originalHtml = button.html();

            button
                .prop('disabled', true)
                .addClass('loading')
                .html(`
                    <i class="spinner loading icon"></i>
                    در حال ارسال
                `);

            $.ajax({
                url: `/automation/runs/${runId}/retry`,
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                data: JSON.stringify({}),
                success: function (data) {
                    showToast(
                        data.message || 'جاب برای اجرای مجدد ثبت شد.',
                        'success'
                    );

                    button.hide();

                    window.setTimeout(function () {
                        fetchStatus();
                    }, 500);
                },
                error: function (xhr) {
                    let message = 'خطا در اجرای مجدد جاب.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    showToast(message, 'error');

                    button
                        .prop('disabled', false)
                        .removeClass('loading')
                        .html(originalHtml);
                }
            });
        }

        $(document).on('click', '.btn-view-cares', function () {
            const userId = $(this).data('user-id');
            fetchUserCares(userId);
        });

        $(document).on('click', '#retry-run-button', function () {
            retryRun($(this));
        });

        $(document).ready(function () {
            fetchStatus();
            autoPollInterval = window.setInterval(fetchStatus, 5000);
        });
    </script>
@endpush
