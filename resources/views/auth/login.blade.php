@extends('layouts.app')

@section('title', 'ورود به حساب کاربری')

@section('content')
    <div class="auth-wrapper">
        <div class="ui raised very padded text container segment auth-box">
            <h2 class="ui teal centered header">
                <i class="user circle icon"></i>
                <div class="content">ورود به حساب کاربری</div>
            </h2>

            @if ($errors->any())
                <div class="ui negative message">
                    <div class="header">خطا در ورود</div>
                    <ul class="list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="ui large form" method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="field">
                    <label>ایمیل</label>
                    <div class="ui left icon input">
                        <input
                            type="tel"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="نام کاربری خود را وارد کنید"
                        >
                        <i class="user icon"></i>
                    </div>
                </div>

                <div class="field">
                    <label>رمز عبور</label>
                    <div class="ui left icon input">
                        <input
                            type="password"
                            name="password"
                            placeholder="رمز عبور خود را وارد کنید"
                        >
                        <i class="lock icon"></i>
                    </div>
                </div>

                <button type="submit" class="ui fluid large teal button">
                    ورود
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('.ui.checkbox').checkbox();
    </script>
@endpush
