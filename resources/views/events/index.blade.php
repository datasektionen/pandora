@extends('master')

@section('title', 'Bokning: ' . $event->title)

@if (Auth::check() && (Auth::user()->isAdminFor($event->entity) || $event->booked_by == Auth::user()->id))
	@section('action-button', '<a href="/events/' . $event->id . '/edit" class="primary-action">Ändra</a>')
@endif

@section('content')
<div class="center-table">
	@if ($event->approved === null && Auth::check() && Auth::user()->isAdminFor($event->entity))
		<ul class="actions">
	        <li><a href="/admin/bookings/{{ $event->id }}/accept" class="accept">Godkänn</a></li>
	        <li><a href="/admin/bookings/{{ $event->id }}/decline" class="decline">Neka</a></li>
	    </ul>
    @endif
	<h2>Allmän information</h2>
	<table>
		<tr>
			<td>Bokning av: </td>
			<td> {{ $event->entity->name }}</td>
		</tr>
		<tr>
			<td>Bokningens start: </td>
			<td> {{ $event->start }}</td>
		</tr>
		<tr>
			<td>Bokningens slut: </td>
			<td> {{ $event->end }}</td>
		</tr>
		<tr>
			<td>Av vem: </td>
			<td> {{ $event->title }}</td>
		</tr>
	</table>
	@if (Auth::check() && (Auth::user()->isAdminFor($event->entity) || Auth::user()->isAdmin() || Auth::user()->id == $event->booked_by))
	<h2>Information som inte visas för alla</h2>
	<table>
		<tr>
			<td>Anledning för bokning: </td>
			<td> {{ $event->description }}</td>
		</tr>
		@if ($event->entity->alcohol_question)
		<tr>
			<td>Servering av alkohol: </td>
			<td> {{ $event->alcohol ? 'Ja' : 'Nej' }}</td>
		</tr>
		@endif
		<tr>
			<td>Bokat av: </td>
			<td> {{ $event->author->name }} ({{ $event->author->kth_username }}@kth.se)</td>
		</tr>
		<tr>
			<td>Bokning skapad: </td>
			<td> {{ $event->created_at }}</td>
		</tr>
		<tr>
			<td>Status: </td>
			<td> {{ $event->approved === null && $event->deleted_at === null ? 'Inte handlagd' : ($event->approved != null ? 'Godkänd' : 'Inte godkänd') }}</td>
		</tr>
		@endif
	</table>
</div>
@endsection