@extends('layouts.app')

@section('title', 'لیست بیماران دیابتی')

@section('content')
    <div class="ui container" style="margin-top: 2rem;">
        <h2 class="ui header">لیست بیماران دیابتی</h2>
        <x-filter :groups="$serviceGroups=[]" />
        <form id="list-form" class="ui form" action="{{route('diabetic.store')}}" method="post" style="padding-bottom: 35px">
            @csrf
            <x-users :users="$users" :careType="$careType"/>
        </form>
    </div>
@endsection
@push('fixed-elements')
    <x-fixed-submit  icon="check" buttonClass="primary" form="list-form"/>
@endpush
