@extends('master')


@section('title', 'Skapa ny entitet')


@section('content')
{!! Form::open(['url' => URL::to(Request::path(), [], true)]) !!}
    <div class="form">
        <div class="form-entry">
            <span class="description">
                Namn:
            </span>
            <div class="input">
                {!! Form::text('name', NULL, array('placeholder' => 'T.ex. "Bilen, Mötesrummet"')) !!}
            </div>
        </div>

        <div class="form-entry">
            <span class="description">
                Beskrivning:
            </span>
            <div class="input">
                {!! Form::textarea('description', NULL, array('placeholder' => 'T.ex. "Mötesrummet är det bästa rummet."', 'class' => 'textarea')) !!}
            </div>
        </div>

        <div class="form-entry">
            <span class="description">
                Gruppnamn för administration i Pls:
            </span>
            <div class="input">
                {!! Form::text('pls_group', NULL, array('placeholder' => 'T.ex. "meta"')) !!}
            </div>
        </div>

        <div class="form-entry">
            <div class="input">
                {!! Form::submit('Skapa entitet', NULL) !!}
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection
