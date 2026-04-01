@php
     $usr = Auth::guard('player')->user();

     if($usr->can('competition.create'))
     {
        $competitions = \App\Models\Competition::all();
     }else{
        $clubIds = $usr->clubs()->pluck('club_id')->toArray();
        $competitions = \App\Models\Competition::whereHas('clubs', function ($query) use ($clubIds) {
            $query->whereIn('club.id', $clubIds);
        })->get();
     }
@endphp
@if ($usr->can('competition.details'))
    <div style="background:cadetblue" class="user-profile pull-left">
        {{-- <img class="avatar user-thumb" src="{{ asset('backend/assets/images/author/avatar.png') }}" alt="avatar"> --}}
        <h4 class="user-name dropdown-toggle" data-toggle="dropdown">
        Competitions
        <i class="fa fa-angle-down"></i></h4>
        @foreach($competitions as $competition)
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('player.competition.details',['id' => $competition->id]) }}">{{$competition->name}}</a>
            </div>
        @endforeach
        <form id="player-logout-form" action="{{ route('player.logout.submit') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
@endif

<div class="user-profile pull-right">
    {{-- <img class="avatar user-thumb" src="{{ asset('backend/assets/images/author/avatar.png') }}" alt="avatar"> --}}
    <h4 class="user-name dropdown-toggle" data-toggle="dropdown">
    {{ Auth::guard('player')->user()->name }}
    <i class="fa fa-angle-down"></i></h4>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('player.logout.submit') }}"
        onclick="event.preventDefault();
                      document.getElementById('player-logout-form').submit();">Log Out</a>
    </div>

    <form id="player-logout-form" action="{{ route('player.logout.submit') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>