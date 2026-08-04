<div class="column">
    <div class="ui fluid segment center aligned dashboard-stat-card">
        <div class="dashboard-stat-icon" style="color: {{ $iconColor }};">
            <i class="{{ $icon }} icon"></i>
        </div>

        <div class="ui mini {{ $statClass }} statistic">
            <div class="value" id="{{ $id }}">{{ $value }}</div>
            <div class="label">{{ $label }}</div>
        </div>
    </div>
</div>
