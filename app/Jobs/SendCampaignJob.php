<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Campaign;
use App\Models\Subscriber;
use App\Mail\NewsletterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function handle()
    {
        // Update status to Sending
        $this->campaign->update(['status' => 'sending']);

        $subscribers = Subscriber::active()->get();
        $this->campaign->update(['total_recipients' => $subscribers->count()]);

        $count = 0;
        foreach($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($this->campaign, $subscriber));
                $count++;
                
                // Update progress every 10 emails
                if($count % 10 == 0) {
                    $this->campaign->update(['sent_count' => $count]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send campaign {$this->campaign->id} to {$subscriber->email}: " . $e->getMessage());
            }
        }

        // Finalize
        $this->campaign->update([
            'status' => 'sent',
            'sent_count' => $count,
            'sent_at' => now()
        ]);
    }
}
