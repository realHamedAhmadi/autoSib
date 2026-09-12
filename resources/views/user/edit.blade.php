@extends('layouts.app')

@section('title', 'ویرایش کاربر')

@section('content')
    <div class="ui container">
        <div class="ui segment">
            <h2 class="ui header">ویرایش کاربر</h2>

            <form class="ui form" method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label>نام</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="نام و نام خانوادگی">
                    @error('name')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>کد ملی</label>
                    <input type="text" name="national_code" value="{{ old('national_code', $user->national_code) }}" placeholder="کد ملی ۱۰ رقمی">
                    @error('national_code')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>کاربر در هر روز حداکثر چند نفر را می تواند مراقبت بزند؟(خالی: به دلخواه)</label>
                    <input type="number" name="max_user_care" value="{{ old('max_user_care',$user->max_user_care) }}" placeholder="حداکثر کاربر سیب برای مراقبت">
                    @error('max_user_care')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field">
                    <label>فاصله زمانی بین مراقبت ها (ثانیه)</label>
                    <input type="number" name="care_sleep_time" value="{{ old('care_sleep_time',$user->care_sleep_time) }}" min="15" placeholder="فاصله زمانی بین مراقبت ها">
                    @error('care_sleep_time')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                @can('owner')
                    @if(!$user->isOwner())
                <div class="inline field">
                    <div class="ui checkbox">
                        <input type="hidden" name="is_admin" value="0">
                        <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->is_admin) == 1)>
                        <label>ادمین</label>
                    </div>
                    @error('is_admin')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                    @endif
                @endcan
                @if(!$user->isOwner())
                <div class="inline field">
                    <div class="ui checkbox">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active) == 1)>
                        <label>فعال</label>
                    </div>
                    @error('is_active')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                @endif
                <div class="field">
                    <label>کاربر اجازه انجام کدام مراقبت ها رو داشته باشد؟</label>
                    <select class="ui dropdown" name="allowed_cares[]" multiple>
                        @foreach(\App\Support\CareType::cases() as $type)
                            <option
                                value="{{$type->name}}"
                                @selected(in_array($type->name, old('allowed_cares',$user->allowedCares->pluck('type')->toArray()), true))
                            >{{$type->value}}</option>
                        @endforeach
                    </select>
                    @error('allowed_cares')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                    @error('allowed_cares.*')
                    <div class="ui pointing red basic label">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="ui primary button">
                    <i class="save icon"></i>
                    بروزرسانی
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
        $('.ui.dropdown').dropdown();
    </script>
@endpush
