<div class="week-component">
    <div class="week">
        <div class="title-row">
            <div class="hour placeholder">
                <a href="#" id="show-all">Visa mer &darr;</a>
            </div>
            @for($i = 0; $i < 7; $i++)
            <div class="day{{ $i == $today ? ' today' : '' }}">
                <div class="title">
                    {{ date("j/n", strtotime('+' . $i . 'days', $startDate)) }}
                </div>
            </div>
            @endfor
        </div>

        <div class="hour-row">
            <div class="legend">
                @for($j = 0; $j < 24; $j++)
                <div class="hour{{ $j < 8 ? ' hidden' : '' }}">
                    <span>{{ $j }}:00</span>
                </div>
                @endfor
                <div class="clear"></div>
            </div>

            @for($i = 0; $i < 7; $i++)
            <div class="day{{ $i == $today ? ' today' : '' }}">
                {!! Form::hidden('date', date("Y-m-d", strtotime('+' . $i . 'days', $startDate)), ['class' => 'date-val']) !!}
                @for($j = 0; $j < 24; $j++)
                <div class="hour{{ $j < 8 ? ' hidden' : '' }}">

                </div>
                @endfor
                <div class="clear"></div>
            </div>
            @endfor
            @foreach($tracks as $date => $dayTracks)
                @foreach($dayTracks as $t => $track)
                    @foreach($track as $event)
                        <div 
                            class="event {{ $event->approved === null ? 'not-confirmed' : 'confirmed' }} {{ $event->id == $highlightId ? 'highlight' : '' }}" 
                            style="
                            top:   {{ (strtotime($event->start) - strtotime(date("Y-m-d", strtotime($event->start)))) / 3600 * 35 - 8*35 }}px;
                            height:{{ (strtotime($event->end) - strtotime($event->start)) / 3600 * 35 }}px;
                            min-height:{{ (strtotime($event->end) - strtotime($event->start)) / 3600 * 35 }}px;
                            max-height:{{ (strtotime($event->end) - strtotime($event->start)) / 3600 * 35 }}px;
                            left:  {{ 5 + floor((strtotime($event->start)-strtotime(date('Y-m-d', $startDate))) / 3600 / 24) * 13.57 + $t * 13.57/$numTracks[$date] }}%;
                            width: {{ 13.57/$numTracks[$date] * $event->colspan }}%;">
                            <a class="content{{ $event->entity_id != $entity->id ? ' child' : '' }}" href="/events/{{ $event->id }}">
                                <span class="from">{{ date("H:i", strtotime($event->start)) }}</span>
                                <span class="to">{{ date("H:i", strtotime($event->end)) }}</span>
                                <div class="text">
                                    @if($event->entity_id != $entity->id)
                                        <b>{{ $event->entity->name }} är bokat:</b><br>
                                    @endif
                                    @if($event->approved === null)
                                        Bokningsförfrågan<br>
                                    @else
                                        <b>{{ $event->title }}</b><br>
                                    @endif

                                    @if (Auth::check() && (Auth::user()->isAdminFor($entity) || $event->created_by == Auth::user()->id))
                                        Vem: {{ $event->title }}
                                        <br>Skapad av: {{ $event->author->name }}
                                        <br>Varför: {{ $event->description }}

                                        @if ($entity->alcohol_question)
                                            <br>Alkohol: {{ $event->alcohol ? 'Ja' : 'Nej' }}
                                        @endif
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                @endforeach
            @endforeach
        </div>
    </div>
</div>