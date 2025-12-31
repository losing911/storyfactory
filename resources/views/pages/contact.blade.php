@extends('layouts.frontend')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl text-gray-300">
    <h1 class="text-4xl font-bold text-neon-blue mb-8 glitch-text" data-text="İletişim">İletişim</h1>

    <div class="grid md:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="glass-panel p-8">
            <h2 class="text-2xl font-display text-white mb-6">/// TRANSMIT_DATA</h2>
            
            @if(session('success'))
                <div class="bg-green-900/50 border border-green-500 text-green-300 p-4 mb-6 font-mono text-sm">
                    > STATUS: SUCCESS<br>
                    > MESSAGE: {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">IDENTITY (NAME)</label>
                    <input type="text" name="name" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">COMM_CHANNEL (EMAIL)</label>
                    <input type="email" name="email" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">SUBJECT_HEADER</label>
                    <input type="text" name="subject" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">DATA_PACKET (MESSAGE)</label>
                    <textarea name="message" rows="5" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition"></textarea>
                </div>
                <button type="submit" class="w-full bg-neon-blue text-black font-bold font-display py-3 hover:bg-white transition duration-300">
                    SEND_TRANSMISSION
                </button>
            </form>
        </div>

        <!-- Info Panel -->
        <div class="space-y-8">
            <div class="glass-panel p-8 border-l-4 border-neon-purple">
                <h3 class="text-xl font-display text-white mb-4">/// ENCRYPTED_CHANNELS</h3>
                <p class="text-gray-400 mb-6 text-sm leading-relaxed">
                    We are always listening. Whether you have a new story idea, a bug report, or just want to join the resistance, our channels are open.
                </p>
                
                <div class="space-y-4">
                    <a href="mailto:contact@anxipunk.icu" class="flex items-center gap-3 text-neon-green hover:underline">
                        <span>✉</span> contact@anxipunk.icu
                    </a>
                    <a href="https://x.com/AnxlPunk" target="_blank" class="flex items-center gap-3 text-white hover:text-neon-blue transition">
                        <span>✖</span> @AnxlPunk (Official Comms)
                    </a>
                </div>
            </div>

            <div class="bg-black border border-dashed border-gray-800 p-6 text-xs font-mono text-gray-500">
                > NOTE: All communications are encrypted end-to-end.<br>
                > DO NOT share sensitive corporate data.<br>
                > Response time: 24-48 cycles.
            </div>
        </div>
    </div>
</div>
@endsection
