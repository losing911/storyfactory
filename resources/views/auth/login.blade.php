@extends('layouts.frontend')

@section('content')
<div class="min-h-screen flex items-center justify-center relative">
    <!-- Background Glitch Effect -->
    <div class="absolute inset-0 z-0 opacity-20 bg-[url('https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExbm90eW14Y3l5b3B6Z2g5aHZnNmJ5ZmJ5ZmJ5ZmJ5ZmJ5ZmJ5ZmJ5eiZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/3o7TKs2XQ6sW3l61wQ/giphy.gif')] bg-cover bg-center"></div>

    <div class="relative z-10 w-full max-w-md">
        <div class="bg-black/80 border-2 border-neon-blue p-8 shadow-[0_0_20px_rgba(0,255,255,0.3)] backdrop-blur-md">
            <h2 class="text-3xl font-display text-white mb-6 text-center glitch-effect" data-text="SYSTEM ACCESS">SYSTEM ACCESS</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-neon-blue text-sm font-mono tracking-widest mb-2">IDENTIFIER (EMAIL)</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-black border border-gray-700 text-white p-3 focus:border-neon-pink focus:ring-1 focus:ring-neon-pink outline-none font-mono placeholder-gray-600"
                        placeholder="agent@anxipunk.icu">
                    @error('email')
                        <p class="text-neon-pink text-xs mt-1 font-mono">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-neon-blue text-sm font-mono tracking-widest mb-2">ACCESS CODE (PASSWORD)</label>
                    <input id="password" type="password" name="password" required
                        class="w-full bg-black border border-gray-700 text-white p-3 focus:border-neon-pink focus:ring-1 focus:ring-neon-pink outline-none font-mono"
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-neon-pink text-xs mt-1 font-mono">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" class="bg-black border-gray-700 text-neon-blue focus:ring-neon-blue">
                        <label for="remember_me" class="ml-2 block text-gray-400 text-xs font-mono uppercase">Memory Lock</label>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-neon-blue/10 border border-neon-blue text-neon-blue hover:bg-neon-blue hover:text-black py-3 font-display uppercase tracking-widest transition duration-300 shadow-[0_0_10px_rgba(0,255,255,0.2)] hover:shadow-[0_0_20px_rgba(0,255,255,0.5)]">
                    Authenticte
                </button>
            </form>
        </div>
        
        <div class="text-center mt-4">
             <p class="text-gray-500 text-xs font-mono">UNAUTHORIZED ACCESS WILL BE TERMINATED.</p>
        </div>
    </div>
</div>
@endsection
