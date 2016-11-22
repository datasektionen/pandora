@extends('emails.master')

@section('title', 'Din bokning handläggs')

@section('content')
<?php $red = ' style="color: #d20;"'; ?>
<?php $green = ' style="color: #3a0;"'; ?>

<p style="margin:0;padding:0;border:0">Hej, {{ $user->name }}!</p>
<br/>
<p style="margin:0;padding:0;border:0">
    Din bokning har ändrats. Se nedan vad. Din bokning är inte bekräftad än. Du får ett nytt mejl när din bokning är handlagd.
</p>
<br/>
<table border="0" cellspacing="0" cellpadding="0" style="width:100%">
    <tr>
        <th></th>
        <th> Gammalt värde</th>
        <th> Nytt värde</th>
    </tr>
    <tr>
        <td>Bokningens start: </td>
        @if (array_key_exists('start', $dirty))
            <td> <strike{!! $red !!}>{{ $oldEvent->start }}</strike></td>
            <td> <span{!! $green !!}>{{ $event->start }}</span></td>
        @else
            <td> {{ $oldEvent->start }}</td>
            <td> {{ $event->start }}</td>
        @endif
    </tr>
    <tr>
        <td>Bokningens slut: </td>
        @if (array_key_exists('end', $dirty))
            <td> <strike{!! $red !!}>{{ $oldEvent->end }}</strike></td>
            <td> <span{!! $green !!}>{{ $event->end }}</span></td>
        @else
            <td> {{ $oldEvent->end }}</td>
            <td> {{ $event->end }}</td>
        @endif
    </tr>
    <tr>
        <td>Av vem: </td>
        @if (array_key_exists('title', $dirty))
            <td> <strike{!! $red !!}>{{ $oldEvent->title }}</strike></td>
            <td> <span{!! $green !!}>{{ $event->title }}</span></td>
        @else
            <td> {{ $oldEvent->title }}</td>
            <td> {{ $event->title }}</td>
        @endif
    </tr>
    <tr>
        <td>Anledning för bokning: </td>
        @if (array_key_exists('description', $dirty))
            <td> <strike{!! $red !!}>{{ $oldEvent->description }}</strike></td>
            <td> <span{!! $green !!}>{{ $event->description }}</span></td>
        @else
            <td> {{ $oldEvent->description }}</td>
            <td> {{ $event->description }}</td>
        @endif
    </tr>
    @if ($entity->alcohol_question)
    <tr>
        <td>Servering av alkohol: </td>
        @if ($oldEvent->alcohol != $event->alcohol)
            <td> <strike{!! $red !!}>{{ $oldEvent->alcohol ? 'Ja' : 'Nej' }}</strike></td>
            <td> <span{!! $green !!}>{{ $event->alcohol ? 'Ja' : 'Nej' }}</span></td>
        @else
            <td> {{ $oldEvent->alcohol ? 'Ja' : 'Nej' }}</td>
            <td> {{ $event->alcohol ? 'Ja' : 'Nej' }}</td>
        @endif
    </tr>
    @endif
    <tr>
        <td>Bokat av: </td>
        <td> {{ $oldEvent->author->name }} ({{ $oldEvent->author->kth_username }}@kth.se)</td>
        <td> {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se)</td>
    </tr>
    <tr>
        <td>Bokning skapad: </td>
        <td> {{ $oldEvent->created_at }}</td>
        <td> {{ $event->created_at }}</td>
    </tr>
    <tr>
        <td>Status: </td>
        <td> <strike{!! $red !!}>{{ $oldEvent->approved === null && $oldEvent->deleted_at === null ? 'Inte handlagd' : ($oldEvent->approved != null ? 'Godkänd' : 'Inte godkänd') }}</strike></td>
        <td> <span{!! $green !!}>Inte handlagd</span></td>
    </tr>
</table>
<br/>
<p style="margin:0;padding:0;border:0">
    Hälsningar (om datorer hade känslor),
</p>
<br/>
<p style="margin:0;padding:0;border:0">
    Datasektionens bokningssystem
</p>
@endsection