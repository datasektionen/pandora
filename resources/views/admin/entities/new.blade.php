@extends('master')


@section('title', 'Skapa ny entitet')


@section('content')
    {!! Form::open(['url' => URL::to(Request::path(), [], env('APP_ENV') != 'local')]) !!}
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
                Fråga om alkoholförtäring under bokning:
            </span>
            <div class="input horizontal">
                <div class="radio">
                    {!! Form::radio('alcohol_question', 'yes', null, array('id' => 'alc_yes')) !!}
                    <label for="alc_yes">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('alcohol_question', 'no', null, array('id' => 'alc_no')) !!}
                    <label for="alc_no">Nej</label>
                </div>
            </div>
        </div>

        <div class="form-entry">
            <span class="description">
                Visa ännu ej handlagda bokningar för allmänheten:
            </span>
            <div class="input horizontal">
                <div class="radio">
                    {!! Form::radio('show_pending_bookings', 'yes', null, array('id' => 'bookings_yes')) !!}
                    <label for="bookings_yes">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('show_pending_bookings', 'no', null, array('id' => 'bookings_no')) !!}
                    <label for="bookings_no">Nej</label>
                </div>
            </div>
        </div>

        <div class="form-entry">
            <span class="description">
                E-post för händelsenotifiering:
            </span>
            <div class="input">
                {!! Form::text('notify_email', NULL, array('placeholder' => 'T.ex. "lokalchef@d.kth.se"')) !!}
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
            <span class="description">
                <a href="http://fontawesome.io/icons/" target="_blank">FA-ikon</a>:
            </span>
            <div class="input">
                {!! Form::text('fa_icon', NULL, array('placeholder' => 'T.ex. "fa-car"')) !!}
            </div>
        </div>

        <div class="form-entry">
            <span class="description">
                Del av:
            </span>
            <div class="input">
                <div class="select">
                    {!! Form::select('part_of', App\Models\Entity::all()->pluck('name', 'id')->prepend('Välj annan entitet', 0)) !!}
                </div>
            </div>
        </div>

        <div class="form-entry">
            <span class="description">
                Kontrakt som måste godkännas vid bokning:
            </span>
            <div class="input">
                <div class="radio">
                    {!! Form::radio('contract', 'yes', true, array('id' => 'contract_yes')) !!}
                    <label for="contract_yes">Ja, URL: <br>{!! Form::text('contract_url') !!}</label>
                </div>
                <div class="clear" style="height:30px"></div>
                <div class="radio">
                    {!! Form::radio('contract', 'no', null, array('id' => 'contract_no')) !!}
                    <label for="contract_no">Nej</label>
                </div>
            </div>
        </div>
        
        <div class="form-entry">
                <span class="description">
                    Rank:
                </span>
                <p>
                Hur högt upp ska denna entitet vara på startsidan?
                    </p>
            <div class="input">
                {!! Form::number('rank', NULL, array('placeholder' => '1 = viktigast <-> 100 = mindre prioriterad')) !!}
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
