@extends('master')


@section('title', 'Ändra entitet: ' . $entity->name)


@section('content')
    {!!  Form::open(['url' => URL::to(Request::path(), [], env('APP_ENV') != 'local')]) !!}
    <div class="form">
        <div class="form-entry">
                <span class="description">
                    Namn:
                </span>
            <div class="input">
                {!! Form::text('name', $entity->name, array('placeholder' => 'T.ex. "Bilen, Mötesrummet"')) !!}
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Beskrivning:
                </span>
            <div class="input">
                {!! Form::textarea('description', $entity->description, array('placeholder' => 'T.ex. "Mötesrummet är det bästa rummet."', 'class' => 'textarea')) !!}
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    <!-- Skriven av d-LoL (emeritus?) Karl-Isac. Fy F**n va jobbigt det var >:( -->
                    Låda med info (HTML-format):
                </span>
            <div class="input">
                {!! Form::textarea('ruta_med_stuff', $entity->ruta_med_stuff, array('placeholder' => 'T.ex. "Vape\'a inte i Mötesrummet!"', 'class' => 'textarea')) !!}
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Fråga om alkoholförtäring under bokning:
                </span>
            <div class="input horizontal">
                <div class="radio">
                    {!! Form::radio('alcohol_question', 'yes', $entity->alcohol_question, array('id' => 'alc_yes')) !!}
                    <label for="alc_yes">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('alcohol_question', 'no', !$entity->alcohol_question, array('id' => 'alc_no')) !!}
                    <label for="alc_no">Nej</label>
                </div>
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Fråga om behovet av speciell LoL-utrustning under bokning:
                </span>
            <div class="input horizontal">
                <div class="radio">
                    {!! Form::radio('lol_question', 'yes', $entity->lol_question, array('id' => 'lol_yes')) !!}
                    <label for="lol_yes">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('lol_question', 'no', !$entity->lol_question, array('id' => 'lol_no')) !!}
                    <label for="lol_no">Nej</label>
                </div>
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Visa ännu ej handlagda bokningar för allmänheten:
                </span>
            <div class="input horizontal">
                <div class="radio">
                    {!! Form::radio('show_pending_bookings', 'yes', $entity->show_pending_bookings, array('id' => 'bookings_yes')) !!}
                    <label for="bookings_yes">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('show_pending_bookings', 'no', !$entity->show_pending_bookings, array('id' => 'bookings_no')) !!}
                    <label for="bookings_no">Nej</label>
                </div>
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    E-post för händelsenotifiering:
                </span>
            <div class="input">
                {!! Form::text('notify_email', $entity->notify_email, array('placeholder' => 'T.ex. "carl@datasektionen.se"')) !!}
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Gruppnamn för administration i Pls:
                </span>
            <div class="input">
                {!! Form::text('pls_group', $entity->pls_group, array('placeholder' => 'T.ex. "meta"', 'disabled')) !!}
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    <a href="http://fontawesome.io/icons/" target="_blank">FA-ikon</a>:
                </span>
            <div class="input">
                {!! Form::text('fa_icon', $entity->fa_icon, array('placeholder' => 'T.ex. "fa-car"')) !!}
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Del av:
                </span>
            <div class="input">
                <div class="select">
                    {!! Form::select('part_of', App\Models\Entity::all()->pluck('name', 'id')->prepend('Välj annan entitet', 0), $entity->part_of) !!}
                </div>
            </div>
        </div>

        <div class="form-entry">
                <span class="description">
                    Kontrakt som måste godkännas vid bokning:
                </span>
            <div class="input">
                <div class="radio">
                    {!! Form::radio('contract', 'yes', $entity->contract_url != null, array('id' => 'contract_yes')) !!}
                    <label for="contract_yes">Ja, URL: <br>{!! Form::text('contract_url', $entity->contract_url) !!}
                    </label>
                </div>
                <div class="clear" style="height:30px"></div>
                <div class="radio">
                    {!! Form::radio('contract', 'no', $entity->contract_url == null, array('id' => 'contract_no')) !!}
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
                {!! Form::number('rank', $entity->rank, array('placeholder' => '1 = viktigast <-> 100 = mindre prioriterad')) !!}
            </div>
        </div>

        <div class="form-entry">
            <div class="input">
                {!! Form::submit('Ändra entitet', NULL) !!}
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection
