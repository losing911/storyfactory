<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        // Honeypot Check
        if ($request->filled('website_url')) {
            // Silently fail (pretend success) to fool the bot
            return redirect()->back()->with('success', 'Mesajınız iletildi. Teşekkürler netrunner!');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        \App\Models\ContactMessage::create($validated);

        return redirect()->back()->with('success', 'Mesajınız iletildi. Teşekkürler netrunner!');
    }
}
