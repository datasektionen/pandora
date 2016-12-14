@extends('master')

@section('title', 'Ändra bokning: ' . $event->title)

@section('action-button', '<a href="/events/' . $event->id . '/delete" class="primary-action">Ta bort</a>')

@section('content')
{!! Form::open(['url' => URL::to(Request::path(), [], true)]) !!}
    <div class="form">
        <div class="form-entry">
            <span class="description">
                Startdatum och -tid för bokning:
            </span>
            {!! Form::input('date', 'startdate', date('Y-m-d', strtotime($event->start)), ['id' => 'startdate']) !!}
            {!! Form::input('time', 'starttime', date('H:i', strtotime($event->start))) !!}
            <div class="clear"></div>
        </div>
        <div class="form-entry">
            <span class="description">
                Slutdatum och -tid för bokning:
            </span>
            {!! Form::input('date', 'enddate', date('Y-m-d', strtotime($event->end))) !!}
            {!! Form::input('time', 'endtime', date('H:i', strtotime($event->end))) !!}
            <div class="clear"></div>
        </div>
        <div class="form-entry">
            <span class="description">
                Vem bokar?
            </span>
            {!! Form::text('booker', $event->title, ['placeholder' => 'Nämnd/Sektion/Person']) !!}
            <span class="hint">(bokat genom {{ $event->author->name }})</span>
        </div>
        <div class="form-entry">
            <span class="description">
                Anledning för bokning:
            </span>
            {!! Form::text('reason', $event->description, ['placeholder' => 'T.ex. "Vi ska ha en fiskdammstävling."']) !!}
        </div>
        <div class="form-entry">
            <span class="description">
                Varför bokar du om bokningen? Detta kommer bifogas det e-postmeddelande som går ut till bokningsinnehavaren och administratören.
            </span>
            {!! Form::text('reason_edit', NULL, ['placeholder' => 'T.ex. "Ett annat event tog bokningstiden."']) !!}
        </div>
        {{--
        TODO: Enable when controller handles it
        @if ($event->entity->alcohol_question)
        <div class="form-entry">
            <span class="description">
                Kommer det serveras alkohol?
            </span>
            <div class="horizontal">
                <div class="radio">
                    {!! Form::radio('alcohol', 'yes', $event->alcohol, ['id' => 'alc']) !!} <label for="alc">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('alcohol', 'no', !$event->alcohol, ['id' => 'noalc']) !!} <label for="noalc">Nej</label>
                </div>
            </div>
        </div>
        @endif
        --}}
        <div class="form-entry">
        	<p>Din bokning kommer åter behöva godkännas om du ändrar den och trycker på knappen nedan. Genom att trycka på knappen godkänner du också att automatiska e-postmeddelanden skickas till dig för att uppdatera dig om din bokning.</p>
            {!! Form::submit('Skapa bokningsförfrågan', ['class' => 'theme-color', 'id' => 'save-booking']) !!}
        </div>
    </div>
{!! Form::close() !!}
@endsection