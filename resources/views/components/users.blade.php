@props(['users'])

<div class="ui segment">
    <table class="ui celled striped selectable table">
        <thead>
        <tr>
            <th class="center aligned" style="width: 50px;">
                <div class="ui checkbox">
                    <input type="checkbox" id="select-all-users">
                    <label></label>
                </div>
            </th>
            <th>نام</th>
            <th>نام خانوادگی</th>
            <th>موبایل</th>
            <th>کد ملی</th>
        </tr>
        </thead>

        <tbody>
        @forelse($users as $key=>$user)
            <tr>
                <td class="center aligned">
                    <div class="ui checkbox">
                        <input
                            type="checkbox"
                            name="users[{{$key}}][id]"
                            value="{{ $user->nationalId }}"
                            class="user-checkbox"
                        >
                        <input
                            type="checkbox"
                            name="users[{{$key}}][token]"
                            value="{{ $user->userToken }}"
                            class="user-checkbox"
                        >
                        <label></label>
                    </div>
                </td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->family }}</td>
                <td>
                    <a href="tel:{{ $user->mobile ?: '-' }}">{{ $user->mobile ?: '-' }}</a>
                </td>
                <td>{{ $user->nationalId ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="center aligned">موردی یافت نشد</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
    <script>
        $(function () {
            $('.ui.checkbox').checkbox();

            $('#select-all-users').on('change', function () {
                $('.user-checkbox').prop('checked', $(this).is(':checked'));
            });

            $('.user-checkbox').on('change', function () {
                var total = $('.user-checkbox').length;
                var checked = $('.user-checkbox:checked').length;

                $('#select-all-users').prop('checked', total > 0 && total === checked);
            });
        });
    </script>
@endpush
