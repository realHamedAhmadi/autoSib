<td data-label="عملیات">
    <div class="run-actions">
        <a href="{{ $showUrl }}" class="ui mini {{ $buttonClass ?? 'basic' }} button">
            {{ $viewLabel ?? 'مشاهده' }}
        </a>

        @if (!empty($canRetry))
            <button
                type="button"
                class="ui mini yellow icon button retry-run-btn"
                data-id="{{ $runId }}"
                title="اجرای مجدد"
            >
                <i class="sync alternate icon"></i>
            </button>
        @endif
    </div>
</td>
