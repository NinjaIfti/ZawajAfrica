<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TherapistBooking;
use App\Notifications\TherapistBookingReminder;
use Carbon\Carbon;

class SendTherapistReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'therapist:send-reminders {type=all : Type of reminder (24h, 1h, 15m, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send therapist booking reminders to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $sentCount = 0;

        $this->info("Starting therapist reminder process for: {$type}");

        if ($type === 'all' || $type === '24h') {
            $sentCount += $this->sendReminderType('24h');
        }

        if ($type === 'all' || $type === '1h') {
            $sentCount += $this->sendReminderType('1h');
        }

        if ($type === 'all' || $type === '15m') {
            $sentCount += $this->sendReminderType('15m');
        }

        $this->info("Total reminders sent: {$sentCount}");
        return 0;
    }

    /**
     * Send specific type of reminder
     */
    private function sendReminderType(string $type): int
    {
        $timeFilter = $this->getTimeFilter($type);
        
        $bookings = TherapistBooking::with(['user', 'therapist'])
            ->where('status', 'confirmed')
            ->where('payment_status', 'paid') // Only send reminders for paid bookings
            ->whereBetween('appointment_datetime', $timeFilter)
            ->get();

        $this->line("Sending {$type} reminders for {$bookings->count()} bookings...");

        foreach ($bookings as $booking) {
            try {
                $booking->user->notify(new TherapistBookingReminder($booking, $type));
                $this->info("✓ Sent {$type} reminder to {$booking->user->name} for session with {$booking->therapist->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send {$type} reminder for booking {$booking->id}: {$e->getMessage()}");
            }
        }

        return $bookings->count();
    }

    /**
     * Get time filter based on reminder type
     */
    private function getTimeFilter(string $type): array
    {
        return match($type) {
            '24h' => [
                Carbon::now()->addDay()->startOfHour(),
                Carbon::now()->addDay()->endOfHour()
            ],
            '1h' => [
                Carbon::now()->addHour()->startOfMinute(),
                Carbon::now()->addHour()->addMinutes(5)
            ],
            '15m' => [
                Carbon::now()->addMinutes(15)->startOfMinute(),
                Carbon::now()->addMinutes(20)
            ],
            default => [
                Carbon::now(),
                Carbon::now()->addMinute()
            ]
        };
    }
}
