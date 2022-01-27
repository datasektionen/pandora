@extends('emails.master')

@section('content')
    # Hej, {{ $user->name }}!

    ## Din bokning är nu godkänd!

    Detta mejl är en bekräftelse på att din bokning blivit godkänd. Nedan finns information kring bokningen.

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
    | Status:                | {{ $event->approved === null && $event->deleted_at === null ? 'Inte handlagd' : ($event->approved != null ? 'Godkänd' : 'Inte godkänd') }} |

@endsection
