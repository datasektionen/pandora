{!! Form::open(['url' => '/bookings/' . $entity->id . '/book']) !!}

@if (!isset($close) || $close)
    <a class="close" href="" id="closedialog">Stäng</a>
@endif

<h1>Boka {{ $entity->name }}</h1>
<div class="form">
    <div class="form-entry">
            <span class="description">
                Startdatum och -tid för bokning:
            </span>
        {!! Form::input('date', 'startdate', null, ['id' => 'startdate', 'class' => 'datepicker', 'placeholder' => 'YYYY-MM-DD']) !!}
        {!! Form::input('time', 'starttime', null, ['class' => 'timepicker', 'placeholder' => 'TT:MM']) !!}
        <div class="clear"></div>
    </div>
    <div class="form-entry">
            <span class="description">
                Slutdatum och -tid för bokning:
            </span>
        {!! Form::input('date', 'enddate', null, ['class' => 'datepicker', 'placeholder' => 'YYYY-MM-DD']) !!}
        {!! Form::input('time', 'endtime', null, ['class' => 'timepicker', 'placeholder' => 'TT:MM']) !!}
        <div class="clear"></div>
    </div>
    <div class="form-entry">
            <span class="description">
                Vem bokar?
            </span>
        {!! Form::text('booker', null, ['placeholder' => 'Nämnd/Sektion/Person']) !!}
        <span class="hint">(du bokar genom {{ Auth::user()->name }})</span>
    </div>
    <div class="form-entry">
            <span class="description">
                Anledning för bokning:
            </span>
        {!! Form::text('reason', null, ['placeholder' => 'T.ex. "Vi ska ha en fiskdammstävling."']) !!}
        <span class="hint">Det är viktigt att ange en utförlig anledning då detta tas i beaktning när flera bokningar finns på samma tid</span>
    </div>
    @if ($entity->alcohol_question)
        <div class="form-entry">
            <span class="description">
                Kommer det serveras alkohol?
            </span>
            <div class="horizontal">
                <div class="radio">
                    {!! Form::radio('alcohol', 'yes', false, ['id' => 'alc']) !!} <label for="alc">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('alcohol', 'no', true, ['id' => 'noalc']) !!} <label for="noalc">Nej</label>
                </div>
            </div>
        </div>
    @endif
    @if ($entity->lol_question)
        <div class="form-entry">
            <span class="description">
                Kommer det att behövas någon speciell Ljud-och-Ljus utrustning? (t.ex. mikrofoner, högtalare, kablage)
            </span>
            <div class="horizontal">
                <div class="radio">
                    {!! Form::radio('lol', 'yes', false, ['id' => 'lol']) !!} <label for="lol">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('lol', 'no', true, ['id' => 'nolol']) !!} <label for="nolol">Nej</label>
                </div>
            </div>
        </div>
    @endif
    @if ($entity->contract_url !== null)
        <div class="form-entry">
            <span class="description">
                Har du läst och godkänner <a href="{!! $entity->contract_url !!}" target="_blank">bokningsavtalet</a>?
            </span>
            <div class="horizontal">
                <div class="radio">
                    {!! Form::radio('contract', 'yes', false, ['id' => 'contract']) !!} <label for="contract">Ja</label>
                </div>
            </div>
        </div>
    @endif

    @if (Auth::user()->isAdminFor($entity))
        <div class="form-entry">
            <span class="description">
                Återkommer eventet veckovis?
            </span>
            <div class="horizontal">
                <div class="radio">
                    {!! Form::radio('recurring', 'yes', false, ['id' => 'recurring']) !!} <label
                        for="recurring">Ja</label>
                </div>
                <div class="radio">
                    {!! Form::radio('recurring', 'no', true, ['id' => 'norecurring']) !!} <label
                        for="norecurring">Nej</label>
                </div>
            </div>
        </div>
        <div class="form-entry" id="untildate">
            <span class="description">
                Fram till vilket datum?
            </span>
            <div class="horizontal">
                {!! Form::input('date', 'recurringuntil', null) !!}
            </div>
        </div>
    @endif
    <div class="form-entry">
        <p>Genom att trycka på knappen nedan godkänner du att automatiska e-postmeddelanden skickas till dig för att
            uppdatera dig om din bokning.</p>
        {!! Form::submit('Skapa bokningsförfrågan', ['class' => 'theme-color', 'id' => 'save-booking']) !!}
    </div>
</div>
<script type="text/javascript">
    $('input[name="recurring"]').change(function (e) {
        if ($(this).val() === 'yes') {
            $('#untildate').show();
        } else {
            $('#untildate').hide();
        }
    });
    $('input[name="recurring"][value="no"]').change();
</script>
{!! Form::close() !!}
