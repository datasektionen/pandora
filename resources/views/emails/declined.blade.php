@extends('emails.master')

@section('content')
# Hej, {{ $user->name }}!

Din bokning blev inte godkänd!

Detta mejl är en bekräftelse på att din bokning blivit avslagen. Nedan finns information kring bokningen.

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
@if ($entity->lol_question)
| Behov för LoL-utrustning: | {{ $event->lol ? 'Ja' : 'Nej' }} |
@endif
| Bokat av:              | {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se) |
| Bokning skapad:        | {{ $event->created_at }}  |
| Status:                | {{ $event->approved === null && $event->deleted_at === null ? 'Inte handlagd' : ($event->approved != null ? 'Godkänd' : 'Inte godkänd') }} |

@endsection
