@extends('layouts.app')

@section('title', 'لیست بیماران دیابتی')

@section('content')
    <div class="ui container" style="margin-top: 2rem;">
        <h2 class="ui header">لیست بیماران دیابتی</h2>
        <x-filter :groups="$serviceGroups=[]" />
        <form class="ui form" action="{{route('diabetic.store')}}" method="post" style="padding-bottom: 35px">
            @csrf
            <x-users :users="$users" />
            <x-fixed-submit  icon="check" buttonClass="primary" />
        </form>
        <x-pagination :paginator="$users=[]" />
    </div>
@endsection
