<div class="ui raised segment dashboard-section" style="margin-top: 1.5rem;">
    @include('dashboard.partials.section-header', [
        'color' => 'grey',
        'icon' => 'hourglass half',
        'title' => 'صف انتظار',
        'subtitle' => 'فرآیندهایی که هنوز شروع نشده‌اند',
        'count' => $pendingRuns->count(),
        'countId' => 'badge-pending-count',
        'badgeColor' => 'grey',
    ])

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

                            @include('dashboard.partials.cells.run-actions', [
                                'showUrl' => route('automation.runs.show', $pendingRun),
                                'buttonClass' => 'basic',
                                'viewLabel' => 'مشاهده',
                                'canRetry' => false,
                                'runId' => $pendingRun->id,
                            ])
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
