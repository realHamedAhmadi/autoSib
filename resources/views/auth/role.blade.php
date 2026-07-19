@extends('layouts.app')

@section('title', 'انتخاب نقش')

@section('content')
    <div class="auth-wrapper">
        <div class="ui raised very padded text container segment auth-box">
            <h2 class="ui centered header">انتخاب نقش</h2>

            <form id="role-form" class="ui form" method="POST" action="{{ route('set.role') }}">
                @csrf
                <div class="grouped fields">
                    @foreach ($roles as $role)
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input
                                    type="radio"
                                    name="role_id"
                                    value="{{ $role->roleUserId}}"
                                    onchange="document.getElementById('role-form').submit();"
                                >
                                <label>{{ $role->title }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('.ui.radio.checkbox').checkbox();
    </script>
@endpush
