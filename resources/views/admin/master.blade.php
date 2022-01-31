<?php $bookings = Auth::user()->decisionEvents()->get()->count(); ?>

@extends('master')

@section('content')
    <div class="row">
        <div class="col-sm-4 col-md-3">
            <div id="secondary-nav">
                <h3>Meny</h3>
                <ul>
                    <li><a href="/admin/bookings">Nya bokningar <?php if ($bookings > 0) : ?><span
                                class="notif">{{ $bookings }}</span><?php endif; ?></a></li>
                    <li><a href="/admin/entities">Entiteter</a></li>
                    @if (Auth::user()->isAdmin())
                        <li><a href="/admin/import">Importera</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-sm-8 col-md-9">
            @yield('admin-content')
        </div>
        <div class="clear"></div>
    </div>
@endsection
