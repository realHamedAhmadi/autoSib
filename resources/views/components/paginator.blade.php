@props([
    'paginator',
])

@php
    // Stop rendering if paginator is invalid or has no data
    if (!is_object($paginator) || !method_exists($paginator, 'total') || $paginator->total() <= 0) {
        return;
    }

    // Filter out previous/next items from linkCollection to avoid duplicate arrows
    $pageLinks = collect($paginator->linkCollection())->filter(function ($link) {
        $label = strip_tags($link['label']);
        return !str_contains($label, 'Previous')
            && !str_contains($label, 'Next')
            && !str_contains($label, '«')
            && !str_contains($label, '»');
    });
@endphp

@if ($paginator->hasPages())
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
                justify-content: flex-start;
                padding: 0.75rem 1rem;
                background: #fff;
                border: 1px solid rgba(34, 36, 38, 0.15);
                border-radius: 0.28571429rem;
            }
            .sp-pagination-left {
                display: flex;
                align-items: center;
                width: 100%;
            }
            @media (max-width: 767px) {
                .sp-pagination-left {
                    justify-content: center;
                    overflow-x: auto;
                }
                .sp-pagination-left .ui.pagination.menu {
                    display: flex !important;
                    flex-wrap: nowrap;
                }
            }
        </style>

        <div class="sp-pagination-container">
            <div class="sp-pagination-left">
                <div class="ui pagination menu" role="navigation" style="margin: 0; width: 100%;">
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
            </div>
        </div>
    </div>
@endif
