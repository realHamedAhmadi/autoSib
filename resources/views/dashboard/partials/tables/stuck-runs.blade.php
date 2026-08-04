<div id="stuck-runs-section" style="display: {{ $stuckRuns->isEmpty() ? 'none' : 'block' }}; margin-top: 1.5rem;">
    <div class="ui negative raised segment dashboard-section">
        @include('dashboard.partials.section-header', [
            'color' => 'red',
            'icon' => 'exclamation triangle',
            'title' => 'فرآیندهای مشکوک به توقف',
            'subtitle' => 'بدون بروزرسانی بیش از ' . $stuckMinutes . ' دقیقه',
            'count' => $stuckRuns->count(),
            'countId' => 'badge-stuck-count',
            'badgeColor' => 'red',
        ])

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

                        @include('dashboard.partials.cells.run-actions', [
                            'showUrl' => route('automation.runs.show', $stuckRun),
                            'buttonClass' => 'red',
                            'viewLabel' => 'بررسی',
                            'canRetry' => $canRetry($stuckRun->status),
                            'runId' => $stuckRun->id,
                        ])
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
