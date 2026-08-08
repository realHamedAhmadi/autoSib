@props([
    'paginator',
    'perPageOptions' => [10, 15, 25, 50, 100],
    'perPageName' => 'countPerPage',
])

@php
    if (!is_object($paginator) || !method_exists($paginator, 'total') || $paginator->total() <= 0) {
        return;
    }

    $currentPerPage = (int) request($perPageName, $paginator->perPage());

    // Filter out previous/next items from linkCollection to avoid UI duplications
    $pageLinks = collect($paginator->linkCollection())->filter(function ($link) {
        $label = strip_tags($link['label']);
        return !str_contains($label, 'Previous')
            && !str_contains($label, 'Next')
            && !str_contains($label, '«')
            && !str_contains($label, '»');
    });
@endphp

<div class="sp-pagination-wrapper">
    <style>
        .sp-pagination-wrapper {
            margin-top: 1rem;
            width: 100%;
        }
        .sp-pagination-container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: #fff;
            border: 1px solid rgba(34, 36, 38, 0.15);
            border-radius: 0.28571429rem;
        }
        .sp-pagination-left, .sp-pagination-right {
            display: flex;
            align-items: center;
        }
        .sp-pagination-right {
            gap: 0.5rem;
            margin-right: auto;
        }
        .sp-pagination-right label {
            font-size: 0.9em;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.87);
            white-space: nowrap;
            margin: 0;
        }
        .sp-select-custom {
            padding: 0.4em 0.8em;
            border: 1px solid rgba(34, 36, 38, 0.15);
            border-radius: 0.28571429rem;
            background: #fff;
            color: rgba(0, 0, 0, 0.87);
            font-size: 0.9em;
            outline: none;
            cursor: pointer;
        }
        @media (max-width: 767px) {
            .sp-pagination-container {
                flex-direction: column;
                align-items: stretch;
            }
            .sp-pagination-left {
                justify-content: center;
                overflow-x: auto;
            }
            .sp-pagination-right {
                justify-content: space-between;
                width: 100%;
                border-top: 1px solid rgba(34, 36, 38, 0.1);
                padding-top: 0.75rem;
            }
        }
    </style>

    <div class="sp-pagination-container">
        {{-- Pagination Links --}}
        <div class="sp-pagination-left">
            @if ($paginator->hasPages())
                <div class="ui pagination menu" role="navigation" style="margin:0;">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <div class="icon item disabled" aria-disabled="true">
                            <i class="right chevron icon"></i>
                        </div>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="icon item" rel="prev">
                            <i class="right chevron icon"></i>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach ($pageLinks as $link)
                        @if ($link['url'])
                            <a href="{{ $link['url'] }}" class="item {{ $link['active'] ? 'active' : '' }}">
                                {!! $link['label'] !!}
                            </a>
                        @else
                            <div class="disabled item">{!! $link['label'] !!}</div>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="icon item" rel="next">
                            <i class="left chevron icon"></i>
                        </a>
                    @else
                        <div class="icon item disabled" aria-disabled="true">
                            <i class="left chevron icon"></i>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Per Page Selector --}}
        <div class="sp-pagination-right">
            <label for="sp-per-page-select">تعداد ردیف در صفحه:</label>
            <select
                id="sp-per-page-select"
                class="sp-select-custom js-sp-per-page"
                data-param-name="{{ $perPageName }}"
            >
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected($currentPerPage === (int) $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<script>
    (function () {
        function handlePerPageChange(selectElem) {
            var val = selectElem.value;
            var paramName = selectElem.getAttribute('data-param-name') || 'countPerPage';
            var url = new URL(window.location.href);

            url.searchParams.set(paramName, val);
            url.searchParams.set('page', '1'); // Reset to page 1 on limit change

            window.location.href = url.toString();
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('js-sp-per-page')) {
                handlePerPageChange(e.target);
            }
        });

        // Handle Semantic UI Dropdown JS initialization if attached
        if (typeof jQuery !== 'undefined') {
            jQuery('.sp-pagination-right .ui.dropdown').dropdown({
                onChange: function (value) {
                    var select = this.querySelector('select') || document.querySelector('.js-sp-per-page');
                    if (select) {
                        select.value = value;
                        handlePerPageChange(select);
                    }
                }
            });
        }
    })();
</script>
