@if(isset($matchEvents) && count($matchEvents) > 0)
                <details class="recorded-events" open>
                    <summary>
                        <span>📋 {{ __('Recorded Events') }}</span>
                        <span class="fp-summary-right">
                            {{ count($matchEvents) }}
                            <span class="fp-chevron">⌄</span>
                        </span>
                    </summary>
                    <div class="fp-recorded-body">

                    @php
                    $eventsByType = collect($matchEvents)->groupBy(function ($event) {
                        if (property_exists($event, 'additional_data') && $event->additional_data !== null) {
                            $raw = $event->additional_data;
                            if (is_string($raw)) {
                                $decoded = json_decode($raw, true);
                                if (is_array($decoded) && isset($decoded['type'])) {
                                    return $decoded['type'];
                                }
                            } elseif (is_array($raw) && isset($raw['type'])) {
                                return $raw['type'];
                            }
                        }

                        return $event->event_type;
                    });
                    @endphp

                    @if($eventsByType->has('goal'))
                    <div class="event-type-section">
                        <div class="event-type-header goals">
                            <span>⚽</span>
                            {{ __('Goals') }} ({{ $eventsByType['goal']->count() }})
                        </div>
                        @foreach($eventsByType['goal'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name  }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this goal?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('assist'))
                    <div class="event-type-section">
                        <div class="event-type-header assists">
                            <span>🎯</span>
                            {{ __('Assists') }} ({{ $eventsByType['assist']->count() }})
                        </div>
                        @foreach($eventsByType['assist'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name  }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this assist?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('sub_out') || $eventsByType->has('sub_in'))
                    <div class="event-type-section">
                        <div class="event-type-header substitutions">
                            <span>🔄</span>
                            {{ __('Substitutions') }} ({{ ($eventsByType->get('sub_out', collect())->count() + $eventsByType->get('sub_in', collect())->count()) / 2 }})
                        </div>
                        @php
                        $subOutEvents = $eventsByType->get('sub_out', collect());
                        $subInEvents = $eventsByType->get('sub_in', collect());
                        $substitutionsByMinute = [];

                        foreach($subOutEvents as $subOut) {
                            $minute = $subOut->minute_in_match;
                            if (!isset($substitutionsByMinute[$minute])) {
                                $substitutionsByMinute[$minute] = ['out' => [], 'in' => []];
                            }
                            $substitutionsByMinute[$minute]['out'][] = $subOut;
                        }

                        foreach($subInEvents as $subIn) {
                            $minute = $subIn->minute_in_match;
                            if (!isset($substitutionsByMinute[$minute])) {
                                $substitutionsByMinute[$minute] = ['out' => [], 'in' => []];
                            }
                            $substitutionsByMinute[$minute]['in'][] = $subIn;
                        }

                        ksort($substitutionsByMinute);
                        @endphp

                        @foreach($substitutionsByMinute as $minute => $subs)
                            @if(count($subs['out']) > 0 && count($subs['in']) > 0)
                                @foreach($subs['out'] as $index => $subOut)
                                    @php $subIn = $subs['in'][$index] ?? null; @endphp
                                    @if($subIn)
                                    <div class="event-row-display">
                                        <div class="event-info">
                                            <div class="event-minute">{{ $minute }}'</div>
                                            <div class="player-details">
                                                <div class="player-name" style="color: #ef4444;">↓ {{ $subOut->player_name }}</div>
                                                <div class="club-name">{{ $subOut->club_name ?? $match->home_club_name }}</div>
                                            </div>
                                            <span style="color: #6b7280; margin: 0 8px;">→</span>
                                            <div class="player-details">
                                                <div class="player-name" style="color: #10b981;">↑ {{ $subIn->player_name }}</div>
                                                <div class="club-name">{{ $subIn->club_name ?? $match->home_club_name }}</div>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 4px;">
                                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete SUB OUT?');">
                                                @csrf
                                                <input type="hidden" name="event_id" value="{{ $subOut->event_id }}">
                                                <button type="submit" class="btn-delete" style="font-size: 0.688rem; padding: 4px 8px;">Out</button>
                                            </form>
                                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete SUB IN?');">
                                                @csrf
                                                <input type="hidden" name="event_id" value="{{ $subIn->event_id }}">
                                                <button type="submit" class="btn-delete" style="font-size: 0.688rem; padding: 4px 8px;">In</button>
                                            </form>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('yellow_card'))
                    <div class="event-type-section">
                        <div class="event-type-header yellow">
                            <span>🟨</span>
                            {{ __('Yellow Cards') }} ({{ $eventsByType['yellow_card']->count() }})
                        </div>
                        @foreach($eventsByType['yellow_card'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this card?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('red_card'))
                    <div class="event-type-section">
                        <div class="event-type-header red">
                            <span>🟥</span>
                            {{ __('Red Cards') }} ({{ $eventsByType['red_card']->count() }})
                        </div>
                        @foreach($eventsByType['red_card'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this card?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('penalty_missed'))
                    <div class="event-type-section">
                        <div class="event-type-header penalty-missed">
                            <span>❌</span>
                            {{ __('Penalties Missed') }} ({{ $eventsByType['penalty_missed']->count() }})
                        </div>
                        @foreach($eventsByType['penalty_missed'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('penalty_saved'))
                    <div class="event-type-section">
                        <div class="event-type-header penalty-saved">
                            <span>🧤</span>
                            {{ __('Penalties Saved') }} ({{ $eventsByType['penalty_saved']->count() }})
                        </div>
                        @foreach($eventsByType['penalty_saved'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('own_goal'))
                    <div class="event-type-section">
                        <div class="event-type-header own-goal">
                            <span>⚠️</span>
                            {{ __('Own Goals') }} ({{ $eventsByType['own_goal']->count() }})
                        </div>
                        @foreach($eventsByType['own_goal'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    </div>
                </details>
@endif
