@extends('admin.master')


@section('title', 'Entititer')


@section('action-button', '<a href="/admin/entities/new" class="primary-action">Skapa ny</a>')


@section('admin-content')
	<p>Här visas all bokningsbara objekt (entiteter) som du får visa. Namn på entiteten visas för alla användare. Pls-gruppen är namnet på gruppen i Pls som reglerar vilka som får administrera entiteten.</p>
	{!! Form::open() !!}
		<table>
			<tr>
				<th>Namn</th>
				<th>Pls-grupp</th>
				<th>Visa bokningsförslag?</th>
				<th>Fråga om alkohol?</th>
			</tr>
			@foreach ($entities as $entity)
			<tr>
				<td><a href="/admin/entities/edit/{{ $entity->id }}" title="Ändra">{{ $entity->name }}</a></td>
				<td>{{ $entity->pls_group }}</td>
				<td>{{ $entity->show_pending_bookings ? 'Ja' : 'Nej' }}</td>
				<td>{{ $entity->alcohol_question ? 'Ja' : 'Nej' }}</td>
			</tr>
			@endforeach
		</table>
	{!! Form::close() !!}
	{{ $entities->links() }}
@endsection