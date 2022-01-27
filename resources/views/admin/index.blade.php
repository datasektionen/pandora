@extends('admin.master')

@section('title', 'Administrera bokningar')

@section('admin-content')
    <p>Här kan du administrera alla bokningar.</p>
    <h2>Dina rättigheter</h2>
    <p>Du har rättigheter för att administrera följande entiteter:</p>
    <ul>
        @foreach (App\Models\Entity::forAuthUser()->get() as $entity)
            <li>{{ $entity->name }}</li>
        @endforeach
    </ul>
    <p>
        Dessa rättigheter innebär att du kan godkänna bokningar för dessa entiteter samt redigera deras inställningar.
        Du får också automatiskt ett e-postmeddelande när en bokning för en entitet som du administrera läggs till, för
        att du direkt ska kunna godkänna eller neka bokningen.
    </p>
    <h2>Terminologi</h2>
    <p>På administrationssidorna används vissa speciella ord för att beskriva olika saker.</p>
    <ul>
        <li><b>Entitet</b> En entitet är någonting som kan bokas. Det kan vara en lokal, en bil, en bok eller något helt
            annat.
        </li>
        <li><b>Bokning</b> En bokning är ett tidsintervall förknippat med någon entitet där en användare vill boka
            entiteten. Den kan vara antingen obekräftad, bekräftad eller nekad. Nekade bokningar visas inte i schemat
            för någon. Obekräftade bokningar visas i schemat för den som administrerar entiteten eller för allmänheten
            om inställningen "Visa bokningsförslag" är aktiverad. Bekräftade bokningar visas alltid för allmänheten.
        </li>
    </ul>
@endsection
