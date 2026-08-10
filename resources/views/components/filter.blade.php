@props(['groups' => []])

@php
    $hasFilterRequest = request()->hasAny([
        'enable_national_id',
        'enable_name',
        //'enable_mobile',
        'enable_age',
        'enable_gender',
        'enable_service_group',
        'national_id',
        'name',
        'family',
        //'mobile',
        'age_from',
        'age_to',
        'gender',
        'service_groups',
        'marriage_types',
        'family_relation'
    ]);

    // Only national_id is enabled by default.
    $enabled = [
        'national_id' => request()->has('enable_national_id') ? request()->boolean('enable_national_id') : true,
        'name' => request()->boolean('enable_name'),
        'mobile' => request()->boolean('enable_mobile'),
        'age' => request()->boolean('enable_age'),
        'gender' => request()->boolean('enable_gender'),
        'service_group' => request()->boolean('enable_service_group'),
    ];

    // Filter panel is hidden by default, but stays open after search/filter request.
    $showFilterPanel = $hasFilterRequest;

    $cacheKey="service-group-".request()->getSibAdminUser()->networkId;
    $serviceGroups=\Illuminate\Support\Facades\Cache::get($cacheKey);
@endphp

<button type="button" class="ui basic blue button" id="toggle-filter-panel">
    <i class="filter icon"></i>
    نمایش فیلترها
</button>

<div class="ui segment" id="filter-panel" style="{{ $showFilterPanel ? '' : 'display:none;' }}">
    <div class="ui blue ribbon label">
        <i class="filter icon"></i>
        فیلترهای جستجو
    </div>

    <form action="{{ url()->current() }}" method="GET" class="ui form" id="filter-form" style="margin-top: 1rem;">

        <div class="grouped fields">
            <label>فیلترهای فعال</label>

            <div class="inline fields">
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" id="toggle-national-id" name="enable_national_id" value="1" {{ $enabled['national_id'] ? 'checked' : '' }}>
                        <label for="toggle-national-id">کد ملی</label>
                    </div>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" id="toggle-name" name="enable_name" value="1" {{ $enabled['name'] ? 'checked' : '' }}>
                        <label for="toggle-name">نام و نام خانوادگی</label>
                    </div>
                </div>

                <div class="field" style="display: none">
                    <div class="ui checkbox">
                        <input type="checkbox" id="toggle-mobile" name="enable_mobile" value="1" {{ $enabled['mobile'] ? 'checked' : '' }}>
                        <label for="toggle-mobile">موبایل</label>
                    </div>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" id="toggle-age" name="enable_age" value="1" {{ $enabled['age'] ? 'checked' : '' }}>
                        <label for="toggle-age">بازه سنی</label>
                    </div>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" id="toggle-gender" name="enable_gender" value="1" {{ $enabled['gender'] ? 'checked' : '' }}>
                        <label for="toggle-gender">جنسیت</label>
                    </div>
                </div>

                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" id="toggle-service-group" name="enable_service_group" value="1" {{ $enabled['service_group'] ? 'checked' : '' }}>
                        <label for="toggle-service-group">گروه خدمت</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui divider"></div>

        <div class="fields">
            <div class="four wide field filter-block" data-target="toggle-national-id">
                <label>کد ملی</label>
                <input type="text" name="national_id" value="{{ request('national_id') }}" placeholder="10 رقم">
            </div>

            <div class="three wide field filter-block" data-target="toggle-name">
                <label>نام</label>
                <input type="text" name="name" value="{{ request('name') }}">
            </div>

            <div class="three wide field filter-block" data-target="toggle-name">
                <label>نام خانوادگی</label>
                <input type="text" name="family" value="{{ request('family') }}">
            </div>

            <div class="three wide field filter-block" data-target="toggle-mobile">
                <label>شماره موبایل</label>
                <input disabled type="text" name="mobile" value="{{ request('mobile') }}" placeholder="09...">
            </div>
        </div>

        <div class="fields">
            <div class="two wide field filter-block" data-target="toggle-age">
                <label>سن از</label>
                <input type="number" min="0" name="age_from" value="{{ request('age_from') }}">
            </div>

            <div class="two wide field filter-block" data-target="toggle-age">
                <label>سن تا</label>
                <input type="number" min="0" name="age_to" value="{{ request('age_to') }}">
            </div>

            <div class="three wide field filter-block" data-target="toggle-gender">
                <label>جنسیت</label>
                <select name="gender" class="ui dropdown">
                    <option value="">همه</option>
                    <option value="1" {{ request('gender') === '1' ? 'selected' : '' }}>مرد</option>
                    <option value="2" {{ request('gender') === '2' ? 'selected' : '' }}>زن</option>
                </select>
            </div>

            <div class="six wide field filter-block" data-target="toggle-service-group">
                <label>گروه خدمت</label>
                <select name="service_groups[]" class="ui fluid search dropdown" multiple>
                    @foreach($serviceGroups as $group)
                        <option value="{{ $group->id }}"
                            {{ in_array((string) $group->id, (array) request('service_groups', []), true) ? 'selected' : '' }}>
                            {{ $group->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="fields">
                <div class="field">
                    <div class="ui checkbox slider">
                        <input type="checkbox" id="toggle-family-relation" name="family_relation[]"
                               value="63"
                            {{ in_array('63', (array) request('family_relation', []), true) ? 'checked' : '' }}>
                        <label for="toggle-family-relation">سرپرست</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui checkbox slider">
                        <input type="checkbox" id="toggle-marriage-types" name="marriage_types[]"
                               value="{{\App\Support\MaritalStatus::MARRIED->value}}"
                            {{ in_array((string) \App\Support\MaritalStatus::MARRIED->value, (array) request('marriage_types', []), true) ? 'checked' : '' }}>
                        <label for="toggle-marriage-types">متاهل</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui divider"></div>

        <button type="submit" class="ui primary button">
            <i class="search icon"></i>
            جستجو
        </button>

        <a href="{{ url()->current() }}" class="ui button">
            پاکسازی
        </a>
    </form>
</div>

@push('scripts')
    <script>
        $(function () {
            $('.ui.dropdown').dropdown();
            $('.ui.checkbox').checkbox();

            function syncFilter(toggleId) {
                var enabled = $('#' + toggleId).is(':checked');
                var blocks = $('[data-target="' + toggleId + '"]');

                blocks.each(function () {
                    var block = $(this);
                    var controls = block.find('input, select, textarea');

                    if (enabled) {
                        block.show();
                        controls.prop('disabled', false);
                        block.find('.ui.dropdown').removeClass('disabled');
                    } else {
                        block.hide();
                        controls.prop('disabled', true);
                        block.find('.ui.dropdown').addClass('disabled');
                    }
                });
            }

            function syncFilterButtonText() {
                var isVisible = $('#filter-panel').is(':visible');

                $('#toggle-filter-panel').html(
                    isVisible
                        ? '<i class="filter icon"></i> مخفی کردن فیلترها'
                        : '<i class="filter icon"></i> نمایش فیلترها'
                );
            }

            $('input[type="checkbox"][id^="toggle-"]').each(function () {
                syncFilter(this.id);
            });

            $('input[type="checkbox"][id^="toggle-"]').on('change', function () {
                syncFilter(this.id);
            });

            $('#toggle-filter-panel').on('click', function () {
                $('#filter-panel').slideToggle(150, function () {
                    syncFilterButtonText();
                });
            });

            syncFilterButtonText();
        });
    </script>
@endpush
