@extends('master')

@section('title', 'Datasektionens bokningssystem')

@section('content')
<div class="home_sections">

	@foreach ($entities as $entity)
	<div class="col-md-4 home_section">
		<h2>{{ $entity->name }}</h2>
		<p>{{ $entity->description }}</p>
		<p><a href="{{ url('bookings', $entity->id) }}" class="action">Boka {{ $entity->name }} &raquo;</a></p>
	</div>
	@endforeach

	<div class="clear"></div>
</div>
@endsection