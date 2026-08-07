@can('sibAdminUser')
<a
    href="{{route('dashboard')}}"
    class="item menu-item"
>
    <i class="users icon"></i>
    <span>داشبورد</span>
</a>
<a
    href="{{route('diabetic.index')}}"
    class="item menu-item"
>
    <i class="users icon"></i>
    <span>مراقبت ماهانه دیابت</span>
</a>
<a
    href="{{route('hyper-tension.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت ماهانه فشارخون</span>
</a>
<a
    href="{{route('young.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت جوانان</span>
</a>
<a
    href="{{route('middle-aged.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت میانسالان</span>
</a>
<a
    href="{{route('elderly.index')}}"
    class="item menu-item"
>
    <i class="user icon"></i>
    <span>مراقبت سالمندان</span>
</a>
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


