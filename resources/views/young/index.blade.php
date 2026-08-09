@extends('layouts.app')

@section('title', 'لیست جوانان')

@section('content')
    <div class="ui container" style="margin-top: 2rem;">
        <h2 class="ui header">لیست جوانان</h2>
        <x-filter :groups="$serviceGroups=[]" />
        <form id="list-form" class="ui form" action="{{route('young.store')}}" method="post" style="padding-bottom: 35px">
            @csrf
            <x-users :users="$users" />
        </form>
    </div>
@endsection
@push('fixed-elements')
    <x-fixed-submit  icon="check" buttonClass="primary" form="list-form"/>
@endpush
