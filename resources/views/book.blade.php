@extends('master')


@section('title', 'Boka ' . $entity->name)


@section('action-button', '<a href="/bookings/' . $entity->id . '" class="primary-action">Visa schema</a>')


@section('content')
	@include('includes.book-form', ['close' => false])
@endsection