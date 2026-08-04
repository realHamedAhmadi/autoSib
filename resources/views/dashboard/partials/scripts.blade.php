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
