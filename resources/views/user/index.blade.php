@extends('layouts.app')

@section('title', 'کاربران')

@section('content')
    <div class="ui container">
        <div class="ui clearing segment">
            <h2 class="ui right floated header">کاربران</h2>

            <a href="{{ route('users.create') }}" class="ui left floated primary button">
                <i class="plus icon"></i>
                ایجاد کاربر
            </a>
        </div>

        <div class="ui segment">
            <table class="ui celled striped table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th>کد ملی</th>
                    @can('owner')
                    <th>ادمین</th>
                    @endcan
                    <th>وضعیت</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name ?? '-' }}</td>
                        <td>{{ $user->national_code }}</td>
                        @can('owner')
                        <td class="center aligned">
                            @if($user->isAdmin())
                                <i class="green check circle icon" title="ادمین"></i>
                            @else
                                <i class="red times circle icon" title="ادمین نیست"></i>
                            @endif
                        </td>
                        @endcan
                        <td class="center aligned">
                            @if($user->isActive())
                                <i class="green toggle on icon" title="فعال"></i>
                            @else
                                <i class="red toggle off icon" title="غیرفعال"></i>
                            @endif
                        </td>
                        <td>{{ $user->created_at?\Morilog\Jalali\Jalalian::fromDateTime($user->created_at)->format('Y-m-d H:i'):'-' }}</td>
                        <td>
                            <div class="ui buttons">
                                <a href="{{ route('users.edit', $user) }}" class="ui tiny blue button icon" data-tooltip="       ویرایش">
                                    <i class="edit icon"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('این کاربر حذف شود؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui tiny red button icon" data-tooltip="حذف">
                                        <i class="trash icon"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="center aligned">کاربری یافت نشد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

