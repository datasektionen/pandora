@extends('master')

@section('header-left')
    <a href="/bookings/{{$event->entity_id}}/{{date('Y', strtotime($event->start))}}/{{date('W', strtotime($event->start))}}/{{$event->id}}">&#171;
        Visa i kalender</a>
@endsection

@section('title', 'Bokning: ' . $event->title)

@if (Auth::check() && (Auth::user()->isAdminFor($event->entity) || $event->booked_by == Auth::user()->id))
@section('action-button')
    <a href="/events/{{$event->id}}'/edit" class="primary-action">Ändra</a>
@endsection
@endif

@section('content')
    <div class="center-table">
        @if ($event->approved === null && Auth::check() && Auth::user()->isAdminFor($event->entity))
            <p>
                @if (($c = $event->weakCollisions()) == 0)
                    <b style="color: #3a0">Krockar inte med något!</b>
                @elseif (($c = $event->weakCollisions()) != 0 && ($d = $event->collisions()) != 0 && $c-$d != 0)
                    <b style="color: #d50">Krockar med {{ $c }} bokning{{ $c > 1 ? 'ar' : '' }}! {{ $c-$d }} är med
                        aktiviteter tillhörande entiteter som är del av denna.</b>
                @else
                    <b style="color: #d50">Krockar med {{ $c }} bokning{{ $c > 1 ? 'ar' : '' }}!</b>
                @endif
            </p>
            <ul class="actions">
                <li><a href="/admin/bookings/{{ $event->id }}/accept" class="accept">Godkänn</a></li>
                <li><a href="/admin/bookings/{{ $event->id }}/decline" class="decline">Neka</a></li>
            </ul>
        @endif
        <h2>Allmän information</h2>
        <table>
            <tr>
                <td>Bokning av:</td>
                <td> {{ $event->entity->name }}</td>
            </tr>
            <tr>
                <td>Bokningens start:</td>
                <td> {{ $event->start }}</td>
            </tr>
            <tr>
                <td>Bokningens slut:</td>
                <td> {{ $event->end }}</td>
            </tr>
            <tr>
                <td>Av vem:</td>
                <td> {{ $event->title }}</td>
            </tr>
            @if ($event->recurringOrigin !== null)
                <tr>
                    <td>Upprepning av:</td>
                    <td><a href="/events/{{ $event->recurringOrigin->id }}">{{ $event->recurringOrigin->title }}</a>
                    </td>
                </tr>
            @endif
        </table>
        @if (Auth::check() && (Auth::user()->isAdminFor($event->entity) || Auth::user()->isAdmin() || Auth::user()->id == $event->booked_by))
            <h2>Information som inte visas för alla</h2>
            <table>
                <tr>
                    <td>Anledning för bokning:</td>
                    <td> {{ $event->description }}</td>
                </tr>
                @if ($event->entity->alcohol_question)
                    <tr>
                        <td>Servering av alkohol:</td>
                        <td> {{ $event->alcohol ? 'Ja' : 'Nej' }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Bokat av:</td>
                    <td> {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se)</td>
                </tr>
                <tr>
                    <td>Bokning skapad:</td>
                    <td> {{ $event->created_at }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td> {{ $event->approved === null && $event->deleted_at === null ? 'Inte handlagd' : ($event->approved != null ? 'Godkänd' : 'Inte godkänd') }}</td>
                </tr>
                @endif
            </table>
    </div>
@endsection
