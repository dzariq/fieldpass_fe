<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewCompetitionInviteNotification extends Notification
{
    protected $inviteCount;

    public function __construct($inviteCount)
    {
        $this->inviteCount = $inviteCount;
    }

    // Specify which channels the notification should be sent through
    public function via($notifiable)
    {
        return ['database'];
    }

    // Define the data that will be stored in the database
    public function toDatabase($notifiable)
    {
        return [
            'message' => "You have {$this->inviteCount} new competition invites.",
            'url' => route('admin.competition.invites.index'), // URL to the invites page
        ];
    }
}
