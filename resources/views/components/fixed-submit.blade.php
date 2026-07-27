@props([
    'label' => 'ثبت',
    'icon' => 'save',
    'buttonClass' => 'primary',
    'type' => 'submit',
    'form' => null,
])

<div class="fixed-submit-wrapper">
    <button
        type="{{ $type }}"
        @if($form) form="{{ $form }}" @endif
        class="ui {{ $buttonClass }} button"
    >
        @if($icon)
            <i class="{{ $icon }} icon"></i>
        @endif
        {{ $label }}
    </button>
</div>

@once
    @push('styles')
        <style>
            .fixed-submit-wrapper {
                position: fixed;
                left: 50%;
                bottom: 20px;
                transform: translateX(-50%);
                z-index: 1000;
                background: rgba(255, 255, 255, 0.96);
                padding: 8px 12px;
                border-radius: 999px;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
                border: 1px solid rgba(34, 36, 38, 0.08);
            }

            .has-fixed-submit-space {
                padding-bottom: 90px !important;
            }
        </style>
    @endpush
@endonce
