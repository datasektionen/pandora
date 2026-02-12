@extends('emails.master')

@section('content')
# Hej, {{ $user->name }}!

Din bokningsförfrågan har registrerats. Den kommer nu handläggas av den som ansvarar för bokningarna av {{ $entity->name }}. Nedan visas den information du angav vid bokningen. Om något inte skulle stämma, kontakta ansvarig för bokningen. Detta mejl går inte att svara på.

Detta mejl innebär inte att din bokning är bekräftad. Du får ett nytt mejl när din bokning är handlagd.

| Egenskap               | Värde                     |
| ---------------------- | ------------------------- |
| Bokningens start:      | {{ $event->start }}       | 
| Bokningens slut:       | {{ $event->end }}         |
| Av vem:                | {{ $event->title }}       |
| Anledning för bokning: | {{ $event->description }} |
@if ($entity->alcohol_question)
| Servering av alkohol:  | {{ $event->alcohol ? 'Ja' : 'Nej' }} |
@endif
| Bokat av:              | {{ $event->author->name }} ({{ $event->author->email }}) |
| Bokning skapad:        | {{ $event->created_at }}  |
| Status:                | {{ $event->approved === null && $event->deleted_at === null ? 'Inte handlagd' : ($event->approved != null ? 'Godkänd' : 'Inte godkänd') }} |

@endsection