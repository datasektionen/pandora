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
            Är du säker på att du vill ta bort denna bokning?
        </span>
        {!! Form::submit('Ja', ['name' => 'delete']) !!}
        <div class="clear"></div>
    </div>
</div>
{!! Form::close() !!}
@endsection