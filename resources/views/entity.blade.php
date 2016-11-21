@extends('master')

@section('title', $entity->name)

@section('header-left')
    <div class="center">
        <div class="controls">
            <a href="{{ url('bookings', [$entity->id, $prevYear, $prevWeek]) }}" class="prev theme-color">&lt;</a>
            <span>Vecka {{ $week }} år {{ $year }}</span>
            <a href="{{ url('bookings', [$entity->id, $nextYear, $nextWeek]) }}" class="next theme-color">&gt;</a>
            <a href="{{ url('bookings', [$entity->id]) }}" class="today theme-color">Idag</a>
        </div>
    </div>
@endsection

@section('action-button', '<a href="/bookings/' . $entity->id . '/book" class="primary-action">Boka</a>')

@section('head-js')
<script type="text/javascript" async src="/js/book.js"></script>
@endsection

@section('content')
@if (Auth::guest())
    @include('includes.bottom-alert', ['message' => 'Du kan inte boka tider utan att <a href="/login">logga in</a> först.'])
@else
    <div class="book-box-container">
        <div class="book-box">
            @include('includes.book-form')
        </div>
    </div>
@endif

<div class="header-lower center">
    <div class="controls">
        <a href="{{ url('bookings', [$entity->id, $prevYear, $prevWeek]) }}" class="prev theme-color">&lt;</a>
        <span>Vecka {{ $week }} år {{ $year }}</span>
        <a href="{{ url('bookings', [$entity->id, $nextYear, $nextWeek]) }}" class="next theme-color">&gt;</a>
        <a href="{{ url('bookings', [$entity->id]) }}" class="today theme-color">Idag</a>
        <a href="/bookings/{{ $entity->id }}/book" class="today theme-color">Boka</a>
    </div>
</div>

<div class="week-component">
<div class="week">
    <div class="title-row">
        <div class="hour placeholder">
            <a href="#" id="show-all">Visa mer &darr;</a>
        </div>
        @for($i = 0; $i < 7; $i++)
        <div class="day{{ $i == $today ? ' today' : '' }}">
            <div class="title">
                {{ date("j/n", strtotime('+' . $i . 'days', $startDate)) }}
            </div>
        </div>
        @endfor
    </div>

    <div class="hour-row">
        <div class="legend">
            @for($j = 0; $j < 24; $j++)
            <div class="hour{{ $j < 8 ? ' hidden' : '' }}">
                <span>{{ $j }}:00</span>
            </div>
            @endfor
            <div class="clear"></div>
        </div>

        @for($i = 0; $i < 7; $i++)
        <div class="day{{ $i == $today ? ' today' : '' }}">
            {!! Form::hidden('date', date("Y-m-d", strtotime('+' . $i . 'days', $startDate)), ['class' => 'date-val']) !!}
            @for($j = 0; $j < 24; $j++)
            <div class="hour{{ $j < 8 ? ' hidden' : '' }}">

            </div>
            @endfor
            <div class="clear"></div>
        </div>
        @endfor

        @foreach($tracks as $date => $dayTracks)
        @foreach($dayTracks as $t => $track)
        @foreach($track as $event)
        <div 
        class="event {{ $event->approved === null ? 'not-confirmed' : 'confirmed' }}" 
        style="
        top:   {{ (strtotime($event->start) - strtotime(date("Y-m-d", strtotime($event->start)))) / 3600 * 35 - 8*35 }}px;
        height:{{ (strtotime($event->end) - strtotime($event->start)) / 3600 * 35 }}px;
        min-height:{{ (strtotime($event->end) - strtotime($event->start)) / 3600 * 35 }}px;
        max-height:{{ (strtotime($event->end) - strtotime($event->start)) / 3600 * 35 }}px;
        left:  {{ 5 + floor((strtotime($event->start)-strtotime(date('Y-m-d', $startDate))) / 3600 / 24) * 13.57 + $t * 13.57/$numTracks[$date] }}%;
        width: {{ 13.57/$numTracks[$date] * $event->colspan }}%;">
        <div class="content">
            <span class="from">{{ date("H:i", strtotime($event->start)) }}</span>
            <span class="to">{{ date("H:i", strtotime($event->end)) }}</span>
            <div class="text">
                @if($event->approved === null)
                    Bokningsförfrågan<br>
                @else
                    {{ $event->title }}<br>
                @endif

                @if (Auth::check() && (Auth::user()->isAdminFor($entity) || $event->created_by == Auth::user()->id))
                    Vem: {{ $event->title }}
                    <br>Skapad av: {{ $event->author->name }}
                    <br>Varför: {{ $event->description }}
                    @if ($entity->alcohol_question)
                    <br>Alkohol: {{ $event->alcohol ? 'Ja' : 'Nej' }}
                    @endif
                    <br>
                    @if($event->approved === null && Auth::user()->isAdminFor($entity))
                        <br><a href="/admin/bookings/{{ $event->id }}/accept" class="accept">Godkänn</a>
                        <br><a href="/admin/bookings/{{ $event->id }}/decline">Neka</a>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endforeach
    @endforeach
    @endforeach
</div>
</div>
</div>
@endsection