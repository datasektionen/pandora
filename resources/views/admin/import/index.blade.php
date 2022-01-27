@extends('admin.master')


@section('title', 'Nya bokningar')


@section('admin-content')
    {!!  Form::open(['url' => URL::to(Request::path(), [], env('APP_ENV') != 'local')]) !!}
    <div class="form">
        <div class="form-entry">
            <span class="description">Url till kalendern:</span>
            <div class="input">
                {!! Form::text('url', null, ['placeholder' => 'https://domain.com/path/to/calendar.ics']) !!}
            </div>
        </div>
        <div class="form-entry">
            <span class="description">Importera till:</span>
            <div class="input">
                <div class="select">
                    {!! Form::select('entity', $entities->pluck('name', 'id')) !!}
                </div>
            </div>
        </div>
        <div class="form-entry">
            <div class="input">
                {!! Form::submit('Importera') !!}
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection
