@extends('master')

@section('title', 'Datasektionens bokningssystem')

@section('content')
<h1>Beta - detta system är inte i bruk</h1>
<p>Detta system är inte i bruk än, så om du försöker boka något så kommer det kanske se ut att funka, men bokningen gäller inte. Du bokar fortfarande genom att mejla lokalchef@d.kth.se eller d-mulle@d.kth.se.</p>
<div class="home_sections">

	<?php $i = 0; ?>
	@foreach ($entities as $entity)
	<div class="col-md-4 home_section">
		@if (isset($entity->fa_icon))
		<div class="home_section_icon">
			<i class="fa {{ $entity->fa_icon }}"></i>
        </div>
        @endif
		<h2>{{ $entity->name }}</h2>
		<p>{{ $entity->description }}</p>
		<p><a href="{{ url('bookings', $entity->id) }}" class="action">Boka {{ $entity->name }} &raquo;</a></p>
	</div>
	@if ($i++ % 3 == 2)
		<div class="clear"></div>
	@endif
	@endforeach

	<div class="clear"></div>
</div>
@endsection