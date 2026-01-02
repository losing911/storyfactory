<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Check if already subscribed
        if (Subscriber::where('email', $validated['email'])->exists()) {
            return redirect()->back()->with('info', 'Zaten abonesiniz, teşekkürler!');
        }

        try {
            Subscriber::create([
                'email' => $validated['email'],
                'unsubscribe_token' => Str::random(32),
                'ip_address' => $request->ip(),
                'is_active' => true
            ]);

            return redirect()->back()->with('success', 'Direnişe hoş geldiniz. İlk sinyali bekleyin.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Kayıt Hatası: ' . $e->getMessage());
        }
    }

    public function unsubscribe($token)
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->firstOrFail();
        $subscriber->delete();

        return redirect()->route('home')->with('success', 'Abonelikten çıkıldı. Matrix sizi özleyecek.');
    }
}
