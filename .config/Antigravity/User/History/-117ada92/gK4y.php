<?php

namespace App\Jobs;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Announcement $announcement
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recipients = $this->announcement->buildRecipientsQuery()->get();

        $sentCount = 0;

        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new AnnouncementMail($this->announcement));
                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Failed to send announcement email to {$user->email}: {$e->getMessage()}");
            }
        }

        // Update the actual recipients count
        $this->announcement->update(['recipients_count' => $sentCount]);
    }
}
