<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'سامانه')</title>

    <link
        rel="stylesheet"
        href="{{ asset('/css/semantic.rtl.min.css') }}"
    >

    <style>
        html,
        body {
            direction: rtl;
            background: #f5f7fa;
            font-family: Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            overflow-x: hidden;
        }

        .ui.pushable {
            min-height: 100vh;
            background: #f5f7fa;
        }

        /*
         * Sidebar base configuration
         */
        .app-sidebar.ui.sidebar {
            top: 0 !important;
            right: 0 !important;
            left: auto !important;
            bottom: 0 !important;
            width: 280px !important;
            min-height: 100vh;
            background: #ffffff !important;
            border-left: 1px solid #e5e7eb;
            box-shadow: none !important;
            overflow-y: auto;
        }

        .app-sidebar .menu-header {
            min-height: 64px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #2185d0;
            color: #ffffff;
        }

        .app-sidebar .menu-header .header {
            margin: 0;
            color: #ffffff;
            font-size: 17px;
        }

        .app-sidebar .menu-item {
            display: flex !important;
            align-items: center;
            gap: 10px;
            padding: 15px 20px !important;
            color: #374151 !important;
        }

        .app-sidebar .menu-item:hover {
            background: #f3f4f6 !important;
            color: #2185d0 !important;
        }

        .app-sidebar .menu-item.active {
            background: #e8f3fc !important;
            color: #2185d0 !important;
            font-weight: bold;
            border-right: 3px solid #2185d0;
        }

        .app-sidebar .menu-item i {
            width: 20px;
            margin: 0 !important;
            text-align: center;
        }

        /*
         * App bar base styling
         */
        .app-bar {
            height: 64px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 90;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .app-bar-right,
        .app-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .app-bar-title {
            margin: 0 !important;
            color: #1f2937;
        }

        .app-bar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            white-space: nowrap;
        }

        .app-content {
            padding: 24px;
        }

        /*
         * Desktop mode layout
         */
        @media only screen and (min-width: 768px) {
            .app-sidebar.ui.sidebar {
                display: block !important;
                visibility: visible !important;
                transform: translate3d(0, 0, 0) !important;
            }

            .app-pusher {
                min-height: 100vh;
                margin-right: 280px !important;
                transform: none !important;
            }

            .sidebar-close {
                display: none !important;
            }

            .sidebar-toggle {
                display: none !important;
            }
        }

        /*
         * Mobile mode layout
         */
        @media only screen and (max-width: 767px) {
            .app-sidebar.ui.sidebar {
                right: 0 !important;
                left: auto !important;
                width: 280px !important;
                transform: translate3d(100%, 0, 0) !important;
                visibility: hidden !important;
                transition: transform 0.25s ease !important;
            }

            .app-sidebar.ui.sidebar.visible {
                transform: translate3d(0, 0, 0) !important;
                visibility: visible !important;
            }

            .app-pusher {
                min-height: 100vh;
                margin-right: 0 !important;
                transform: none !important;
            }

            .sidebar-toggle {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
            }

            .sidebar-close {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                color: #ffffff !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .app-bar {
                height: 56px;
                padding: 0 16px;
            }

            .app-content {
                padding: 16px;
            }

            /*.app-bar-user span {
                display: none;
            }*/

            .ui.container {
                width: auto !important;
                margin-left: 0.75rem !important;
                margin-right: 0.75rem !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<div class="ui pushable">
    @auth()
    {{-- Sidebar --}}
    <aside class="ui right vertical sidebar menu app-sidebar">
        {{--<div class="menu-header">
            <button
                type="button"
                class="ui icon button sidebar-close"
                id="sidebarClose"
                aria-label="بستن منو"
            >
                <i class="close icon"></i>
            </button>
        </div>--}}
        @include('sidebar-items')
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="item menu-item"
                style="width: 100%; border: 0; background: transparent; text-align: right; cursor: pointer;"
            >
                <i class="sign-out icon"></i>
                <span>خروج</span>
            </button>
        </form>
    </aside>
    @endauth

    {{-- Pusher / Main container --}}
    <div class="pusher app-pusher">

        @auth()
        {{-- App Bar --}}
        <header class="app-bar">
            <div class="app-bar-right">
                <button
                    type="button"
                    class="ui icon basic button sidebar-toggle"
                    id="sidebarToggle"
                    aria-label="باز کردن منو"
                >
                    <i class="bars icon"></i>
                </button>

                <h3 class="ui header app-bar-title">
                    @yield('title', 'سامانه')
                </h3>
            </div>

            <div class="app-bar-left">
                {{--<button
                    type="button"
                    class="ui icon basic button"
                    title="اعلان‌ها"
                    aria-label="اعلان‌ها"
                >
                    <i class="bell outline icon"></i>
                </button>--}}

                <div class="app-bar-user">
                    <i class="user circle outline icon"></i>
                    <span>
                        {{ auth()->user()?->name ?? 'کاربر' }}
                    </span>
                </div>
            </div>
        </header>
        @endauth

        {{-- Main Page Content --}}
        <main class="app-content">
            @yield('content')
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="{{ asset('/js/semantic.min.js') }}"></script>

<script>
    $(function () {
        const $sidebar = $('.app-sidebar');

        function isMobile() {
            return window.innerWidth <= 767;
        }

        function openMobileSidebar() {
            if (!isMobile()) {
                return;
            }
            $sidebar.addClass('visible');
            $('body').addClass('sidebar-open');
        }

        function closeMobileSidebar() {
            $sidebar.removeClass('visible');
            $('body').removeClass('sidebar-open');
        }

        // Sidebar toggle handlers
        $('#sidebarToggle').on('click', function (e) {
            e.stopPropagation();
            if ($sidebar.hasClass('visible')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });

        $('#sidebarClose').on('click', function () {
            closeMobileSidebar();
        });

        // Close when clicking dynamic links
        $('.app-sidebar .menu-item').on('click', function () {
            if (isMobile()) {
                closeMobileSidebar();
            }
        });

        // Close on clicking backdrop/outside area
        $(document).on('click', function (event) {
            if (!isMobile()) {
                return;
            }

            const clickedInsideSidebar = $(event.target).closest('.app-sidebar').length > 0;
            const clickedToggle = $(event.target).closest('#sidebarToggle').length > 0;

            if ($sidebar.hasClass('visible') && !clickedInsideSidebar && !clickedToggle) {
                closeMobileSidebar();
            }
        });

        // Close on escape key
        $(document).on('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMobileSidebar();
            }
        });

        // Handle resize boundary
        $(window).on('resize', function () {
            if (!isMobile()) {
                closeMobileSidebar();
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>
