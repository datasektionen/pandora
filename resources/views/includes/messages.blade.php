@if (!isset($shown))
    @if (Session::has('error'))
        <div class="error">
            @if (is_array(Session::get('error')))
                <ul>
                    @foreach (Session::get('error') as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            @else
                <p>{!! Session::get('error') !!}</p>
            @endif
        </div>
    @endif

    @if (count($errors) > 0)
        <div class="error">
            <h3>Något fel uppstod:</h3>
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (Session::has('success'))
        <div class="success">
            <p>{!! Session::get('success') !!}</p>
        </div>
    @endif
@endif
<?php $shown = true; ?>