@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-display text-white mb-8 border-l-4 border-neon-blue pl-4">COMMAND CENTER</h1>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-900 border border-gray-800 p-6 rounded">
                <h3 class="text-gray-500 font-mono text-sm uppercase">Total Stories</h3>
                <p class="text-4xl text-neon-green font-display">{{ $stats['total_stories'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 p-6 rounded">
                <h3 class="text-gray-500 font-mono text-sm uppercase">Total Views</h3>
                <p class="text-4xl text-neon-blue font-display">{{ $stats['total_views'] }}</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 p-6 rounded">
                <h3 class="text-gray-500 font-mono text-sm uppercase">Unique Visitors</h3>
                <p class="text-4xl text-neon-pink font-display">{{ $stats['unique_visitors'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Column: AI Strategy & Charts -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- AI Insight Card -->
                <div class="bg-gray-900 border border-neon-purple/50 rounded overflow-hidden shadow-[0_0_20px_rgba(189,0,255,0.1)]">
                    <div class="bg-gray-800/50 p-4 border-b border-gray-700 flex justify-between items-center">
                        <h2 class="text-neon-purple font-display flex items-center gap-2">
                             🤖 AI STRATEGY ADVISOR
                        </h2>
                        @if($insight)
                            <span class="text-xs font-mono text-gray-500">{{ $insight->report_date }}</span>
                        @else
                            <span class="text-xs font-mono text-gray-500">NO DATA YET</span>
                        @endif
                    </div>
                    <div class="p-6 prose prose-invert max-w-none prose-sm">
                        @if($insight)
                            {!! Str::markdown($insight->summary_text) !!}
                        @else
                            <p class="text-gray-400 italic">Waiting for enough data to formulate strategy. Run 'php artisan app:analyze-traffic' later.</p>
                        @endif
                    </div>
                </div>

                <!-- Admin Shortcuts -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.stories.create') }}" class="bg-gray-800 hover:bg-neon-green hover:text-black text-gray-300 p-4 text-center border border-gray-700 transition">
                        New Story (Manual)
                    </a>
                    <a href="{{ route('admin.ai.create') }}" class="bg-gray-800 hover:bg-neon-blue hover:text-black text-gray-300 p-4 text-center border border-gray-700 transition">
                        New Story (AI)
                    </a>
                    <form action="{{ route('admin.ai.generate') }}" method="POST" class="contents">
                        @csrf
                        <input type="hidden" name="topic" value="Random">
                        <button type="submit" class="bg-gray-800 hover:bg-neon-pink hover:text-black text-gray-300 p-4 text-center border border-gray-700 transition">
                            Trigger Daily Story
                        </button>
                    </form>
                    <a href="{{ url('/telescope') }}" target="_blank" class="bg-gray-800 hover:bg-white hover:text-black text-gray-300 p-4 text-center border border-gray-700 transition">
                        System Logs
                    </a>
                    
                    <!-- NEW: Inbox Shortcut -->
                    <a href="{{ route('admin.inbox.index') }}" class="bg-gray-800 hover:bg-neon-purple hover:text-black text-gray-300 p-4 text-center border border-gray-700 transition relative">
                        Inbox
                        @php
                            $unreadCount = \App\Models\ContactMessage::where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] px-1.5 rounded-full font-bold">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <!-- NEW: Newsletter Shortcut -->
                    <a href="{{ route('admin.newsletter.index') }}" class="bg-gray-800 hover:bg-neon-pink hover:text-black text-gray-300 p-4 text-center border border-gray-700 transition">
                        Newsletter
                    </a>
            </div>

            <!-- Sidebar: Live Logs -->
            <div class="bg-gray-900 border border-gray-800 rounded p-4 h-fit">
                <h3 class="text-white font-display text-sm mb-4 border-b border-gray-800 pb-2">LIVE FEED</h3>
                <div class="space-y-3 font-mono text-xs">
                    @forelse($recentLogs as $log)
                        <div class="flex flex-col border-b border-gray-800 pb-2">
                             <div class="flex justify-between items-center mb-1">
                                <span class="text-neon-blue">{{ Str::limit($log->url, 20) }}</span>
                                <span class="text-gray-600">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                             </div>
                             <div class="flex justify-between text-gray-500">
                                <span>{{ $log->ip_address ?? 'Anonymous' }}</span>
                                <span class="uppercase">{{ $log->device_type }}</span>
                             </div>
                        </div>
                    @empty
                        <span class="text-gray-600">No signals detected.</span>
                    @endforelse
                </div>

                    <div class="mt-8">
                        <h3 class="text-white font-display text-sm mb-4 border-b border-gray-800 pb-2">GENERATED E-BOOKS</h3>
                        <a href="{{ route('admin.ebooks.create') }}" class="block mb-4 bg-neon-yellow text-black text-center py-2 font-bold hover:bg-white transition text-xs font-mono">
                            [+] COMPILE NEW VOLUME
                        </a>
                        <div class="space-y-3 font-mono text-xs">
                            @forelse($ebooks as $ebook)
                            <div class="flex flex-col border border-gray-800 p-2 rounded bg-black/50">
                                 <strong class="text-white block">{{ $ebook->title }}</strong>
                                 <div class="flex justify-between mt-1 text-gray-500">
                                     <span>Vol {{ $ebook->volume_number }}</span>
                                     <a href="{{ route('ebooks.show', $ebook->slug) }}" target="_blank" class="text-neon-green hover:underline">READ &rarr;</a>
                                 </div>
                            </div>
                        @empty
                            <span class="text-gray-600">No books in archive.</span>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
