@extends('master')

@section('title', 'Datasektionens bokningssystem')

@section('content')
    <div class="home_sections">
        <?php $i = 0; ?>
        @foreach ($entities ?? [] as $entity)
            <div class="col-md-4 home_section">
                @if (isset($entity->fa_icon))
                    <div class="home_section_icon">
                        <i class="fa text-theme-color {{ $entity->fa_icon }}"></i>
                    </div>
                @endif
                <h2>{{ $entity->name }}</h2>
                <p>{{ $entity->description }}</p>
                <p><a href="{{ url('bookings', $entity->id) }}" class="action">Boka {{ $entity->name }} &raquo;</a></p>
            </div>
            @if ($i++ % 4 == 3)
                <div class="clear"></div>
            @endif
        @endforeach

        <div class="clear"></div>
    </div>
@endsection
