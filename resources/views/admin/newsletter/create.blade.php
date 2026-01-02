@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6 text-white font-mono">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
            <h1 class="text-2xl font-display text-white">
                NEW_TRANSMISSION <span class="text-neon-pink">/// COMPOSE</span>
            </h1>
            <a href="{{ route('newsletter.index') }}" class="text-gray-500 hover:text-white transition text-xs flex items-center gap-2">
                &larr; CANCEL
            </a>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded p-8">
            <form action="{{ route('newsletter.store') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-500 text-xs uppercase tracking-widest mb-2">Subject Line</label>
                    <input type="text" name="subject" required class="w-full bg-black border border-gray-700 text-white p-3 focus:outline-none focus:border-neon-pink transition placeholder-gray-800" placeholder="Anxipunk Weekly Update...">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-500 text-xs uppercase tracking-widest mb-2">Content (HTML Supported)</label>
                    <textarea name="content" rows="15" required class="w-full bg-black border border-gray-700 text-gray-300 p-4 font-mono text-sm focus:outline-none focus:border-neon-pink transition placeholder-gray-800" placeholder="<h1>Hello Netrunners,</h1><p>Write your message here...</p>"></textarea>
                    <p class="text-xs text-gray-600 mt-2">You can use standard HTML tags. Unsubscribe link will be appended automatically.</p>
                </div>

                <div class="flex justify-end gap-4 border-t border-gray-800 pt-6">
                    <button type="submit" class="bg-neon-pink text-black px-8 py-3 font-bold hover:bg-white transition text-sm">
                        SAVE DRAFT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
