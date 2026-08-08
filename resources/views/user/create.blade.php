@extends('layouts.app')

@section('title', 'ایجاد کاربر')

@section('content')
    <div class="ui container">
        <div class="ui segment">
            <h2 class="ui header">ایجاد کاربر</h2>

            <form class="ui form" method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="field">
                    <label>نام</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="نام و نام خانوادگی">
                    @error('name')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>کد ملی</label>
                    <input type="text" name="national_code" value="{{ old('national_code') }}" placeholder="کد ملی ۱۰ رقمی">
                    @error('national_code')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                @can('owner')
                <div class="inline field">
                    <div class="ui checkbox">
                        <input type="hidden" name="is_admin" value="0">
                        <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin') == 1)>
                        <label>ادمین</label>
                    </div>
                    @error('is_admin')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                @endcan
                <button type="submit" class="ui primary button">
                    <i class="save icon"></i>
                    ذخیره
                </button>

                <a href="{{ route('users.index') }}" class="ui button">
                    بازگشت
                </a>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $('.ui.checkbox').checkbox();
    </script>
@endpush
