@extends('master')

@section('title', 'Datasektionens bokningssystem')

@section('content')
<div class="home_sections">
    <?php $i = 0; ?>
    @foreach ($entities ?? [] as $entity)
    <div class="col-md-4 home_section">
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
        <details class="home_section_description">
            <summary aria-label="Visa beskrivning föx§r {{ $entity->name }}">
                <i class="fa text-theme-color fa-info-circle" aria-hidden="true"></i>
            </summary>
            <p>{{ $entity->description }}</p>
        </details>
    </div>
    @if ($i++ % 4 == 3)
    <div class="clear"></div>
    @endif
    @endforeach

    <div class="clear"></div>
</div>
@endsection