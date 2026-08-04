<div class="dashboard-section-header">
    <h3 class="ui {{ $color ?? '' }} header">
        <i class="{{ $icon }} icon"></i>
        <div class="content">
            {{ $title }}
            @isset($subtitle)
                <div class="sub header">{{ $subtitle }}</div>
            @endisset
        </div>
    </h3>

    @isset($countId)
        <span class="ui circular {{ $badgeColor ?? $color ?? 'grey' }} label" id="{{ $countId }}">
            {{ $count ?? 0 }}
        </span>
    @endisset

    @isset($action)
        {!! $action !!}
    @endisset
</div>
