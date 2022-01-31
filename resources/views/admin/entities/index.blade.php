@extends('admin.master')


@section('title', 'Entititer')


@section('action-button')
    <a href="/admin/entities/new" class="primary-action">Skapa ny</a>
@endsection


@section('admin-content')
    <p>Här visas all bokningsbara objekt (entiteter) som du får visa. Namn på entiteten visas för alla användare.
        Pls-gruppen är namnet på gruppen i Pls som reglerar vilka som får administrera entiteten.</p>
    <p>Om en entitet är del av en annan entitet kommer dessa att generera krockar i administrationsgränssnittet samt
        visas i varandras scheman.</p>
    <p><b>I dagsläget stödjer inte systemet rekursiva arv. Det betyder att "Del av"-kriteriet endast kommer ta hänsyn
            till den närmaste föräldern, och inte flera.</b></p>
    {!!  Form::open(['url' => URL::to(Request::path(), [], env('APP_ENV') != 'local')]) !!}
    <table>
        <tr>
            <th>Namn</th>
            <th>Del av</th>
            <th>Pls-grupp</th>
            <th>Visa bokningsförslag?</th>
            <th>Fråga om alkohol?</th>
            <th>E-post vid händelser</th>
        </tr>
        @foreach ($entities as $entity)
            <tr>
                <td><a href="/admin/entities/edit/{{ $entity->id }}" title="Ändra"><i
                            class="fa {{ $entity->fa_icon }}"></i> {{ $entity->name }}</a></td>
                <td>{{ $entity->parent == null ? '' : $entity->parent->name }}</td>
                <td>{{ $entity->pls_group }}</td>
                <td>{{ $entity->show_pending_bookings ? 'Ja' : 'Nej' }}</td>
                <td>{{ $entity->alcohol_question ? 'Ja' : 'Nej' }}</td>
                <td>{{ $entity->notify_email }}</td>
            </tr>
        @endforeach
    </table>
    {!! Form::close() !!}
    {{ $entities->links() }}
@endsection
