@extends('master')

@section('title', 'Mina bokningar')

@section('content')
	<h1>Kommande</h1>
	<table style="width: 100%">
		<thead>
			<tr>
				<th>Entitet</th>
				<th style="width:30px;"></th>
				<th>Beskrivning</th>
				<th>Status</th>
				<th>Från</th>
				<th>Till</th>
			</tr>
		</thead>
		@forelse ($future as $event)
		<tr>
			<td><i class="fa {{ $event->entity->fa_icon }}"></i> {{ $event->entity->name }}</td>
			<td><a href="/bookings/{{ $event->entity_id }}/{{ $event->start->format('Y') }}/{{ $event->start->format('W') }}/{{ $event->id }}"><i class="fa fa-calendar"></i></a></td>
			<td><a href="/events/{{ $event->id }}">{{ $event->title }}</a></td>
			<td>{{ $event->approved === null ? 'Ej godkänd' : 'Godkänd' }}</td>
			<td>{{ $event->start->format('Y-m-d H:i') }}</td>
			<td>{{ $event->end->format('Y-m-d H:i') }}</td>
		</tr>
		@empty
		<tr>
			<td colspan="3" style="text-align: center">Det finns inga bokningar här...</td>
		</tr>
		@endforelse
	</table>

	<h1>Passerade</h1>
	<table style="width: 100%">
		<thead>
			<tr>
				<th>Entitet</th>
				<th style="width:30px;"></th>
				<th>Beskrivning</th>
				<th>Status</th>
				<th>Från</th>
				<th>Till</th>
			</tr>
		</thead>
		@forelse ($past as $event)
		<tr>
			<td><i class="fa {{ $event->entity->fa_icon }}"></i> {{ $event->entity->name }}</td>
			<td><a href="/bookings/{{ $event->entity_id }}/{{ $event->start->format('Y') }}/{{ $event->start->format('W') }}/{{ $event->id }}"><i class="fa fa-calendar"></i></a></td>
			<td><a href="/events/{{ $event->id }}">{{ $event->title }}</a></td>
			<td>{{ $event->approved === null ? 'Ej godkänd' : 'Godkänd' }}</td>
			<td>{{ $event->start->format('Y-m-d H:i') }}</td>
			<td>{{ $event->end->format('Y-m-d H:i') }}</td>
		</tr>
		@empty
		<tr>
			<td colspan="3" style="text-align: center">Det finns inga bokningar här...</td>
		</tr>
		@endforelse
	</table>
@endsection