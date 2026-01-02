<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Subscriber;
use App\Mail\NewsletterMail;
use Illuminate\Support\Facades\Mail;

class AdminNewsletterController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->paginate(10);
        return view('admin.newsletter.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.newsletter.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Campaign::create([
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'status' => 'draft',
        ]);

        return redirect()->route('admin.newsletter.index')->with('success', 'Kampanya taslağı oluşturuldu.');
    }

    public function send($id)
    {
        $campaign = Campaign::findOrFail($id);
        
        // This is where we would dispatch a Queue Job
        // For now, let's keep it simple or implement the job next
        // dispatch(new \App\Jobs\SendCampaignJob($campaign));
        
        // Updating status manually since we haven't built the queue implementation fully yet
        // In reality, the Job should handle this.
        // For the verification step, we will verify the Queue implementation separately.
        
        return redirect()->back()->with('info', 'Gönderim kuyruğa eklendi (Coming Soon: Queue Implementation).');
    }

    public function subscribers()
    {
        $subscribers = Subscriber::latest()->paginate(50);
        return view('admin.newsletter.subscribers', compact('subscribers'));
    }

    public function destroySubscriber($id)
    {
        Subscriber::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Abone silindi.');
    }
    
    public function destroy($id)
    {
        Campaign::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kampanya silindi.');
    }
}
