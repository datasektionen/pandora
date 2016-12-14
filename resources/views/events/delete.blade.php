@extends('master')

@section('title', 'Ta bort bokning: ' . $event->title)

@section('content')
<table style="margin: 0 auto">
    <tr>
        <td>Bokning av: </td>
        <td> {{ $event->entity->name }}</td>
    </tr>
    <tr>
        <td>Bokningens start: </td>
        <td> {{ $event->start }}</td>
    </tr>
    <tr>
        <td>Bokningens slut: </td>
        <td> {{ $event->end }}</td>
    </tr>
    <tr>
        <td>Av vem: </td>
        <td> {{ $event->title }}</td>
    </tr>
</table>
{!! Form::open() !!}
<div class="form">
    <div class="form-entry">
        <span class="description">
            Du kan ange en anledning till varför bokningen tas bort nedan. Denna kommer då bifogas i det e-postmeddelande som skickas till bokningsinnehavaren.
        </span>
        {!! Form::textarea('reason', null, ['placeholder' => 'Anledning till att ta bort bokningen.']) !!}
        <div class="clear"></div>
    </div>

    @if ($event->isRecurring())
    <div class="form-entry">
        <span class="description">
            Denna bokning är återkommande. Välj vilka bokningar du vill ta bort.
        </span>
        <div class="input">
            <div class="radio">
                {!! Form::radio('recurring', 'all', false, ['id' => 'all']) !!}
                <label for="all">Ta bort alla bokningar i serien (berör {{ $event->recurringEvents()->get()->count() }} bokningar)</label>
            </div>
            <div class="radio">
                {!! Form::radio('recurring', 'following', false, ['id' => 'following']) !!}
                <label for="following">Ta bort denna och alla efterföljande i serien (berör {{ $event->followingRecurringEvents()->get()->count() }} bokningar)</label>
            </div>
            <div class="radio">
                {!! Form::radio('recurring', 'this', false, ['id' => 'this']) !!}
                <label for="this">Ta bort endast denna bokning (berör 1 bokning)</label>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    @endif

    <div class="form-entry">
        <span class="description">
            Är du säker på att du vill ta bort denna bokning?
        </span>
        {!! Form::submit('Ja', ['name' => 'delete']) !!}
        <div class="clear"></div>
    </div>
</div>
{!! Form::close() !!}
@endsection