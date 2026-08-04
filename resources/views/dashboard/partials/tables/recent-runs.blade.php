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
                                @include('dashboard.partials.cells.status-badge', [
                                    'color' => $meta['color'],
                                    'label' => $meta['label'],
                                ])
                            </td>

                            @include('dashboard.partials.cells.progress-cell', [
                                'dataLabel' => 'پیشرفت کاربران',
                                'percent' => $userPercent,
                                'processed' => $run->processed_users ?? 0,
                                'total' => $run->total_users ?? 0,
                                'unit' => 'کاربر',
                            ])

                            @include('dashboard.partials.cells.progress-cell', [
                                'dataLabel' => 'پیشرفت مراقبت‌ها',
                                'percent' => $carePercent,
                                'processed' => $run->processed_cares ?? 0,
                                'total' => $run->total_cares ?? 0,
                                'unit' => 'مراقبت',
                            ])

                            <td data-label="زمان ثبت">{{ $jalaliDate($run->created_at) }}</td>
                            <td data-label="پایان عملیات">{{ $jalaliDate($run->finished_at) }}</td>

                            @include('dashboard.partials.cells.run-actions', [
                                'showUrl' => route('automation.runs.show', $run),
                                'buttonClass' => 'basic primary',
                                'viewLabel' => 'مشاهده',
                                'canRetry' => $canRetry($run->status),
                                'runId' => $run->id,
                            ])
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
