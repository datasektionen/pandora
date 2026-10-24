@extends('admin.master')


@section('title', 'Nya bokningar')


@section('head-js')
<script type="text/javascript">
    $(document).ready(function() {
        $('.reason').css('width', '150px');
        $('.reason').hide();

        $('.radio').on('mousedown', function() {
            var radio = $(this).find('input[type="radio"]');
            radio.data('wasChecked', radio.prop('checked'));
        });

        $('input[type="radio"]').change(function() {
            if ($(this).prop('name').startsWith('booking')) {
                var row = $(this).parent().parent().parent();
                var selected = row.find('input[type="radio"]:checked');
                if (selected.val() == 'decline') {
                    row.find('td:nth-child(3) input').show();
                } else {
                    row.find('td:nth-child(3) input').hide();
                }
            }
        });

        $('input[type="radio"]').click(function() {
            if ($(this).data('wasChecked')) {
                this.checked = false;
                $(this).trigger('change');
            }
            $(this).removeData('wasChecked');
        });
    });
</script>
@endsection


@section('admin-content')
{!! Form::open(['url' => URL::to(Request::path(), [], env('APP_ENV') != 'local')]) !!}
@if($bookings->count() == 0)
<p>Du har inga bokningar att hantera! Bra jobbat!</p>
@else
<table>
    <tr class="admin-booking-header">
        <th>Godkänn</th>
        <th>Neka</th>
        <th></th>
        <th>Bokning</th>
        <th>Bokat objekt</th>
        <th>Start</th>
        <th>Slut</th>
        <th>Anmärkning</th>
    </tr>
    @foreach ($bookings as $booking)
    <tr>
        <td>
            <div class="radio">
                {!! Form::radio('booking[' . $booking->id . ']', 'approve', false, ['id' => 'accept' . $booking->id]) !!}
                <label for="accept{{ $booking->id }}"></label>
            </div>
        </td>
        <td>
            <div class="radio">
                {!! Form::radio('booking[' . $booking->id . ']', 'decline', false, ['id' => 'decline' . $booking->id]) !!}
                <label for="decline{{ $booking->id }}"></label>
            </div>
        </td>
        <td>
            {!! Form::text('reason[' . $booking->id . ']', null, ['class' => 'reason', 'placeholder' => 'Anledning till avslag']) !!}
        </td>
        <td>
            <a href="/bookings/{{ $booking->entity->id }}/{{ date("Y", strtotime($booking->start)) }}/{{ date("W", strtotime($booking->start)) }}/{{ $booking->id }}"
                title="Ändra">{{ $booking->title }}</a>
        </td>
        <td class="booking-entity"><i class="fa {{ $booking->entity->fa_icon }}"></i> {{ $booking->entity->name }}</td>
        <td>{{ date("Y-m-d H:i", strtotime($booking->start)) }}</td>
        <td>{{ date("Y-m-d H:i", strtotime($booking->end)) }}</td>
        <td>
            @if (($c = $booking->weakCollisions()) == 0)
            <b style="color: #3a0">Krockar inte med något!</b>
            @elseif (($c = $booking->weakCollisions()) != 0 && ($d = $booking->collisions()) != 0 && $c-$d != 0)
            <b style="color: #d50">Krockar med {{ $c }} bokning{{ $c > 1 ? 'ar' : '' }}! {{ $c-$d }} är
                med aktiviteter tillhörande entiteter som är del av denna.</b>
            @else
            <b style="color: #d50">Krockar med {{ $c }} bokning{{ $c > 1 ? 'ar' : '' }}!</b>
            @endif
        </td>
    </tr>
    @endforeach
</table>
{!! Form::submit('Genomför markerade beslut') !!}
@endif
{!! Form::close() !!}
{{ $bookings->links() }}
@endsection