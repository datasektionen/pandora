BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN

@foreach($events as $event)
BEGIN:VEVENT
UID:{{ $event->id }}@bokning.datasektionen.se
DTSTAMP:{{ date('Ymd\THis\Z', strtotime($event->start)) }}
DTSTART:{{ date('Ymd\THis\Z', strtotime($event->start)) }}
DTEND:{{ date('Ymd\THis\Z', strtotime($event->end)) }}
LOCATION:{{ $entity->name }}
URL:{{ url('/bookings/' . $entity->id . '/' . date('Y', strtotime($event->start)) . '/' . date('W', strtotime($event->start))) }}
SUMMARY:{{ $event->title }}
END:VEVENT

@endforeach
END:VCALENDAR