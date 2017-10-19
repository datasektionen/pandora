@extends('emails.master')

@section('content')
<?php $red = 'style="color: #d20;"'; ?>
<?php $green = 'style="color: #3a0;"'; ?>

#Hej, {{ $user->name }}!
    
Din bokning har ändrats. Se nedan vad. Din bokning är inte bekräftad än. Du får ett nytt mejl när din bokning är handlagd.

@if (isset($entity->reason) && strlen($entity->reason) > 0)
**Anledning:** {{ $entity->reason }}
@endif

| Egenskap               | Gammalt värde          | Nytt värde |
| ---------------------- | -----------------------| ---------- |
@if (array_key_exists('start', $dirty))
| Bokningens start:      | <span {!! $red !!}>~~{{ $oldEvent->start }}~~</span> | <span {!! $green !!}>{{ $event->start }}</span> |
@else
| Bokningens start:      | {{ $oldEvent->start }} | {{ $event->start }} |
@endif
@if (array_key_exists('end', $dirty))
| Bokningens slut        | <span {!! $red !!}>~~{{ $oldEvent->end }}~~</span> | <span {!! $green !!}>{{ $event->end }}</span> |
@else
| Bokningens slut        | {{ $oldEvent->end }}   | {{ $event->end }} |
@endif
@if (array_key_exists('title', $dirty))
| Av vem:                | <span {!! $red !!}>~~{{ $oldEvent->title }}~~</span> | <span {!! $green !!}>{{ $event->title }}</span> |
@else
| Av vem:                | {{ $oldEvent->title }} | {{ $event->title }} |
@endif
@if (array_key_exists('description', $dirty))
| Anledning för bokning: | <span {!! $red !!}>~~{{ $oldEvent->description }}~~</span> | <span {!! $green !!}>{{ $event->description }}</span> |
@else
| Anledning för bokning: | {{ $oldEvent->description }} | {{ $event->description }} |
@endif
@if ($entity->alcohol_question)
@if (array_key_exists('alcohol', $dirty))
| Anledning för bokning: | <span {!! $red !!}>~~{{ $oldEvent->alcohol ? 'Ja' : 'Nej' }}~~</span> | <span {!! $green !!}>{{ $event->alcohol ? 'Ja' : 'Nej' }}</span> |
@else
| Anledning för bokning: | {{ $oldEvent->alcohol ? 'Ja' : 'Nej' }} | {{ $event->alcohol ? 'Ja' : 'Nej' }} |
@endif
@endif
| Bokat av:              | {{ $oldEvent->author->name }} ({{ $oldEvent->author->kth_username }}@kth.se) | {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se)  |
| Skapad:                | {{ $oldEvent->created_at }} | {{ $event->created_at }} |
| Status:                | <span {!! $red !!}>~~{{ $oldEvent->approved === null && $oldEvent->deleted_at === null ? 'Inte handlagd' : ($oldEvent->approved != null ? 'Godkänd' : 'Inte godkänd') }}~~</span> | Inte handlagt |

@endsection