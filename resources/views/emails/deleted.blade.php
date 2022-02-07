@extends('emails.master')

@section('content')
# Hej!

Nedanstående bokning har **tagits bort** för {{ $entity->name }}. Tiden är alltså avbokad.

@if (isset($event->reason) && strlen($event->reason) > 0)
**Anledning:** {{ $event->reason }}
@endif

| Egenskap               | Värde                     |
| ---------------------- | ------------------------- |
| Bokningens start:      | {{ $event->start }}       |
| Bokningens slut:       | {{ $event->end }}         |
| Av vem:                | {{ $event->title }}       |
| Anledning för bokning: | {{ $event->description }} |
@if ($entity->alcohol_question)
| Servering av alkohol:  | {{ $event->alcohol ? 'Ja' : 'Nej' }} |
@endif
| Bokat av:              | {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se) |
| Bokning skapad:        | {{ $event->created_at }}  |
| Status:                | Avbokad |

@endsection
