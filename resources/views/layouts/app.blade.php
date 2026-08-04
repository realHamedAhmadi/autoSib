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
        }

        body {
            overflow-x: hidden;
        }

        .page-wrapper {
            min-height: 100vh;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .auth-box {
            width: 100%;
            max-width: 420px;
        }

        @media only screen and (max-width: 767px) {
            .ui.container {
                width: auto !important;
                margin-left: .75rem !important;
                margin-right: .75rem !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="page-wrapper">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="{{ asset('/js/semantic.min.js') }}"></script>

@stack('scripts')
</body>
</html>
