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
