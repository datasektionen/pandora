@extends('emails.master')

@section('content')
# Hej!

En ny bokningsförfrågan har inkommit för {{ $entity->name }}. Se nedan. [Du kan granska den här]({{ url('/events/' . $event->id) }}).

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