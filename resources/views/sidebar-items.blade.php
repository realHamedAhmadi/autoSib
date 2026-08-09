@can('sibAdminUser')
<a
    href="{{route('dashboard')}}"
    class="item menu-item"
>
    <i class="users icon"></i>
    <span>داشبورد</span>
</a>
@can('allowedCare',\App\Support\CareType::DIABETIC)
<a
    href="{{route('diabetic.index')}}"
    class="item menu-item"
>
    <i class="users icon"></i>
    <span>مراقبت ماهانه دیابت</span>
</a>
@endcan
@can('allowedCare',\App\Support\CareType::HYPER_TENSION)
<a
    href="{{route('hyper-tension.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت ماهانه فشارخون</span>
</a>
@endcan
@can('allowedCare',\App\Support\CareType::YOUNG_PEOPLE)
<a
    href="{{route('young.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت جوانان</span>
</a>
@endcan
@can('allowedCare',\App\Support\CareType::MIDDLE_AGED)
<a
    href="{{route('middle-aged.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت میانسالان</span>
</a>
@endcan
@can('allowedCare',\App\Support\CareType::THE_ELDERLY)
<a
    href="{{route('elderly.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت سالمندان</span>
</a>
@endcan
@endcan
@can('admin')
<a
    href="{{route('users.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>کاربران</span>
</a>
@endcan


