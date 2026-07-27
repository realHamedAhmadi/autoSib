@extends('layouts.app')

@section('title', 'لیست بیماران فشار خونی')

@section('content')
    <div class="ui container" style="margin-top: 2rem;">
        <h2 class="ui header">لیست بیماران فشارخونی</h2>
        <x-filter :groups="$serviceGroups=[]" />
        <form class="ui form" action="{{route('hyper-tension.store')}}" method="post" style="padding-bottom: 35px">
            @csrf
            <x-users :users="$users" />
            <x-fixed-submit  icon="check" buttonClass="primary" />
        </form>
        <x-pagination :paginator="$users=[]" />
    </div>
@endsection
