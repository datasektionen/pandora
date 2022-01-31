@extends('master')


@section('title', $entity->name)


@section('header-left')
    {{-- The controls for the wide page --}}
    <div class="center calendar">
        <div class="controls">
            <a href="{{ url('bookings', [$entity->id, $prevYear, $prevWeek]) }}" class="prev theme-color">&lt;</a>
            <span>Vecka {{ $week }} år {{ $year }}</span>
            <a href="{{ url('bookings', [$entity->id, $nextYear, $nextWeek]) }}" class="next theme-color">&gt;</a>
            <a href="{{ url('bookings', [$entity->id]) }}" class="today theme-color">Idag</a>
        </div>
    </div>
@endsection


@section('action-button')
    <a href="/bookings/{{$entity->id}}/book" class="primary-action">Boka</a>
@endsection


@section('head-js')
    {{-- Include the booking script --}}
    <script type="text/javascript" async src="/js/book.js"></script>
@endsection


@section('content')
    {{-- Present alert for users not logged in, or a book form otherwise --}}
    @if (Auth::guest())
        @include('includes.bottom-alert', ['message' => 'Du kan inte boka tider utan att <a href="/login">logga in</a> först.'])
    @else
        <div class="book-box-container">
            <div class="book-box">
                @include('includes.book-form')
            </div>
        </div>
    @endif

    {{-- The controls for the mobile page --}}
    <div class="header-lower center">
        <div class="controls">
            <a href="{{ url('bookings', [$entity->id, $prevYear, $prevWeek]) }}" class="prev theme-color">&lt;</a>
            <span>Vecka {{ $week }} år {{ $year }}</span>
            <a href="{{ url('bookings', [$entity->id, $nextYear, $nextWeek]) }}" class="next theme-color">&gt;</a>
            <a href="{{ url('bookings', [$entity->id]) }}" class="today theme-color">Idag</a>
            <a href="/bookings/{{ $entity->id }}/book" class="today theme-color">Boka</a>
        </div>
    </div>

    @include('includes.week-component')

    <h3>Dela kalendern</h3>
    <p>Du kan enkelt dela denna kalender (till exempel importera den till Google Calendar) genom att använda länken
        nedan.</p>
    <input type="text" class="" id="copy" value="{{ url('/bookings/' . $entity->id . '/ical') }}">
@endsection
