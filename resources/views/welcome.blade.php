@extends('master')

@section('title', 'Datasektionens bokningssystem')

@section('content')
<div class="home_sections">
    @foreach ($entities ?? [] as $entity)
    <div class="home_section">
        @if (isset($entity->fa_icon))
        <div class="home_section_icon">
            <a href="{{ url('bookings', $entity->id) }}">
                <i class="fa text-theme-color {{ $entity->fa_icon }}"></i>
            </a>
        </div>
        @endif
        <a href="{{ url('bookings', $entity->id) }}">
            <h2>{{ $entity->name }}</h2>
        </a>
    </div>
    @endforeach
</div>
@endsection