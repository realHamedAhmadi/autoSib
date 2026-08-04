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
                            $meta = $statusMeta[$todayRun->status] ?? [
                                'label' => $todayRun->status,
                                'color' => 'grey',
                            ];

                            $userPercent = $todayRun->total_users > 0
                                ? (int) round(
                                    ($todayRun->processed_users / $todayRun->total_users) * 100
                                )
                                : 0;

                            $carePercent = $todayRun->total_cares > 0
                                ? (int) round(
                                    ($todayRun->processed_cares / $todayRun->total_cares) * 100
                                )
                                : 0;
                        @endphp

                        <tr class="{{ $getRowClass($todayRun->status) }}">
                            <td data-label="شناسه اجرا">
                                <span class="run-id">#{{ $todayRun->id }}</span>
                            </td>

                            <td data-label="نوع مراقبت">
                                {{ $todayRun->input['care_type'] ?? '-' }}
                            </td>

                            <td data-label="وضعیت">
                                    <span class="ui mini {{ $meta['color'] }} label">
                                        {{ $meta['label'] }}
                                    </span>
                            </td>

                            <td data-label="پیشرفت کاربران" class="progress-cell">
                                <div class="ui tiny progress">
                                    <div
                                        class="bar"
                                        style="width: {{ $userPercent }}%;"
                                    ></div>
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
                                    <div
                                        class="bar"
                                        style="width: {{ $carePercent }}%;"
                                    ></div>
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
                                    <a
                                        href="{{ route('automation.runs.show', $todayRun) }}"
                                        class="ui mini blue button"
                                    >
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
