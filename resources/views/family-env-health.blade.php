@extends('layouts.main')
@section('style')
    <style>
        label {
            display: inline-block;
            padding-left: 40px;
        }

        table thead {
            position: sticky;
        }

        table td, table th {
            padding: 10px;
            width: 100px;
        }
    </style>
@stop
@section('content')
    <div style="color: red">
        @if(isset($errors))
            @foreach($errors->all() as $error)
                <div>{{$error}}</div>
            @endforeach
        @endif
    </div>
    <div>
        <form action="{{route('family-env-health.store')}}" method="post">
            @csrf
            <div>
                <label>
                    کد ملی
                    <input type="tel" name="national_code" maxlength="10">
                </label>
                <label>
                    آب لوله کشی دارد؟
                    <input type="checkbox" name="plumbing" checked value="1">
                </label>
                <label>
                    منابع بهسازی شده است؟
                    <input type="checkbox" name="water_sources" value="1">
                </label>
                <label>
                    دستشویی بهداشتی است؟
                    <input type="checkbox" name="toilet" value="1">
                </label>
            </div>
            <div>
                <label>
                    فاضلاب بهدشتی است؟
                    <input type="checkbox" name="sewage" value="1">
                </label>
                <label>
                    دفغ زباله بهدشتی است؟
                    <input type="checkbox" name="waste" value="1">
                </label>
                <label>
                    دام دارد؟
                    <input type="checkbox" name="has_livestock" checked value="1">
                </label>
                <label>
                    دفغ فضولات بهدشتی است؟
                    <input type="checkbox" name="animal_waste" value="1">
                </label>
                <label>
                    کاز شهری دارد؟
                    <input type="checkbox" name="has_city_gas" value="1">
                </label>
            </div>
            <div>
                <button type="submit">ثبت</button>
            </div>
        </form>
    </div>
    <div style="margin-top: 100px;margin-bottom: 50px">
        <form>
            <label>
                نام
                <input type="text" name="name">
            </label>
            <label>
                کد ملی
                <input type="text" name="national_code">
            </label>
            <button type="submit">جستجو</button>
        </form>
    </div>
    <div>
        <table>
            <thead>
            <tr>
                <th>روستا</th>
                <th>لوله کشی</th>
                <th>بهسازی منابع</th>
                <th>دست شویی</th>
                <th>فاضلاب</th>
                <th>دفع بهداشتی زباله</th>
                <th>دام دارد</th>
                <th>دفع بهداشتی فضولات</th>
                <th>گاز شهری دارد</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $fModel = \App\Models\FamilyEnvHealth::class;
            $uModel = \App\Models\User::class;
            $uTbl = $uModel::table();
            $fTbl = $fModel::table();
            $db = \Illuminate\Support\Facades\DB::class;
            $f = $fModel::addSelect($db::raw('sum(plumbing) as plumbing'))
                ->addSelect($db::raw('sum(water_sources) as water_sources'))
                ->addSelect($db::raw('sum(toilet) as toilet'))
                ->addSelect($db::raw('sum(sewage) as sewage'))
                ->addSelect($db::raw('sum(waste) as waste'))
                ->addSelect($db::raw('sum(has_livestock) as has_livestock'))
                ->addSelect($db::raw('sum(animal_waste) as animal_waste'))
                ->addSelect($db::raw('sum(has_city_gas) as has_city_gas'));

            $unitCodes = clone $f;
            $unitCodes = $unitCodes->leftJoin($uTbl, "$uTbl.id", "$fTbl.user_id")
                ->addSelect('unit_code', 'unit_name')
                ->groupBy('unit_code')->get();

            $f = $f
                ->whereHas('user', function ($q) {
                    //$q->whereNotNull('unit_code');
                })
                ->first();
            ?>
            @foreach($unitCodes as $code)
                <tr>
                    <td>
                        @if($code->unit_name)
                            {{$code->unit_name}}
                        @else
                            مشخص نیست
                        @endif
                    </td>
                    <td>
                        {{$code->plumbing}}
                    </td>
                    <td>
                        {{$code->water_sources}}
                    </td>
                    <td>
                        {{$code->toilet}}
                    </td>
                    <td>
                        {{$code->sewage}}
                    </td>
                    <td>
                        {{$code->waste}}
                    </td>
                    <td>
                        {{$code->has_livestock}}
                    </td>
                    <td>
                        {{$code->animal_waste}}
                    </td>
                    <td>
                        {{$code->has_city_gas}}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td>کل</td>
                <td>
                    {{$f->plumbing}}
                </td>
                <td>
                    {{$f->water_sources}}
                </td>
                <td>
                    {{$f->toilet}}
                </td>
                <td>
                    {{$f->sewage}}
                </td>
                <td>
                    {{$f->waste}}
                </td>
                <td>
                    {{$f->has_livestock}}
                </td>
                <td>
                    {{$f->animal_waste}}
                </td>
                <td>
                    {{$f->has_city_gas}}
                </td>
            </tr>
            </tbody>
        </table>
        تعداد
        {{count($records)}}
    </div>
    <div>
        <form action="{{route('family-env-health.update',1)}}" method="post">
            @csrf
            @method('put')
            <table>
                <thead>
                <tr>
                    <th>نام</th>
                    <th>کد ملی</th>
                    <th>روستا</th>
                    <th>لوله کشی</th>
                    <th>بهسازی منابع</th>
                    <th>دست شویی</th>
                    <th>فاضلاب</th>
                    <th>دفع بهداشتی زباله</th>
                    <th>دام دارد</th>
                    <th>دفع بهداشتی فضولات</th>
                    <th>گاز شهری دارد</th>
                </tr>
                </thead>
                <tbody>
                @foreach($records as $record)
                    <tr>
                        <td>{{$record->user->name}}</td>
                        <td>{{$record->user->national_code}}</td>
                        <td>{{$record->user->unit_name}}</td>
                        <td>
                            <input type="hidden" name="envs[{{$record->id}}][def]" value="1">
                            <input type="checkbox" name="envs[{{$record->id}}][plumbing]"
                                   {{$record->plumbing?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][water_sources]"
                                   {{$record->water_sources?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][toilet]"
                                   {{$record->toilet?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][sewage]"
                                   {{$record->sewage?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][waste]"
                                   {{$record->waste?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][has_livestock]"
                                   {{$record->has_livestock?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][animal_waste]"
                                   {{$record->animal_waste?'checked':''}} value="1">
                        </td>
                        <td>
                            <input type="checkbox" name="envs[{{$record->id}}][has_city_gas]"
                                   {{$record->has_city_gas?'checked':''}} value="1">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                <button type="submit" style="background-color: blue;color: #fff">
                    انجام تغییرات
                </button>
            </div>
        </form>
        <div style="margin-bottom: 30px">
            @if($records->hasPages())
                <ul>
                    @for($i=1;$i<=$records->lastPage();$i++)
                        <li>
                            <a href="{{$records->url($i)}}">{{$i}}</a>
                        </li>
                    @endfor
                </ul>
            @endif
        </div>
    </div>
@stop