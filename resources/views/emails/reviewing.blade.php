@extends('emails.master')

@section('title', 'Din bokning handläggs')

@section('content')
<p style="margin:0;padding:0;border:0">Hej, {{ $user->name }}!</p>
<br/>
<p style="margin:0;padding:0;border:0">
    Din bokningsförfrågan har registrerats. Den kommer nu handläggas av den som ansvarar för bokningarna av {{ $entity->name }}. Nedan visas den information du angav vid bokningen. Om något inte skulle stämma, kontakta ansvarig för bokningen. Detta mejl går inte att svara på.
</p>
<br/>
<p style="margin:0;padding:0;border:0">
    Detta mejl innebär inte att din bokning är bekräftad. Du får ett nytt mejl när din bokning är handlagd.
</p>
<br/>
<table border="0" cellspacing="0" cellpadding="0" style="width:100%">
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
    <tr>
        <td>Anledning för bokning: </td>
        <td> {{ $event->description }}</td>
    </tr>
    @if ($entity->alcohol_question)
    <tr>
        <td>Servering av alkohol: </td>
        <td> {{ $event->alcohol ? 'Ja' : 'Nej' }}</td>
    </tr>
    @endif
    <tr>
        <td>Bokat av: </td>
        <td> {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se)</td>
    </tr>
    <tr>
        <td>Bokning skapad: </td>
        <td> {{ $event->created_at }}</td>
    </tr>
    <tr>
        <td>Status: </td>
        <td> {{ $event->approved === null && $event->deleted_at === null ? 'Inte handlagd' : ($event->approved != null ? 'Godkänd' : 'Inte godkänd') }}</td>
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