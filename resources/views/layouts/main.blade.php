<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('style')
    <style>
        .sidebar{
            float: right;
            width: 200px;
        }
        .content{
            float: left;
            width: calc(100% - 200px);
        }
    </style>
</head>
<body dir="rtl">

<div class="sidebar">
    @include('sidebar')
</div>
<div class="content">
    @yield('content')
</div>

@yield('script')
</body>
</html>
