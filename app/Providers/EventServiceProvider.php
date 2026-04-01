<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CompetitionClub;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Notifications\NewCompetitionInviteNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            NewCompetitionInviteNotification::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Log::info('BOARD | EVENT LISTENER');

        Event::listen(Login::class, function ($event) {
            Log::info('BOARD | EVENT LOGIN | ' . $event->user->name . ' | ' . $event->user->id);

           
        });
    }
}
