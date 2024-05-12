<?php

namespace App\Console\Commands;

use App\Notifications\EventReminderNotificaton;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use PHPUnit\Event\EventAlreadyAssignedException;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to all event attendees that event starts soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = \App\Models\Event::with('attendees.user')
            ->whereBetween('start_time', [now(), now()->addDay()])
            ->get();

        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);
        $this->info("Found {$eventCount} {$eventLabel}.");
        $events->each(
            fn($event) => $event->attendees->each(
                fn($attendee) => $attendee->user->notify(
                    new EventReminderNotificaton(
                        $event
                    )
                )
            )
        );

        $this->info('Reminder notifications sent successfully!');
    }
}
