@extends('layouts.frontend')

@section('content')
<!-- News Ticker -->
<div class="bg-neon-green text-black font-mono text-xs py-1 overflow-hidden border-b border-gray-800 relative z-50">
    <div class="whitespace-nowrap animate-marquee inline-block">
        /// SYSTEM ALERT: NEURAL LINK CONNECTION UNSTABLE /// WEATHER UPDATE: ACID RAIN PROBABILITY 98% AT SECTOR 7 /// LATEST BOUNTY: "THE GLITCH" - REWARD 5000 CREDITS /// NEW LORE ENTRIES DETECTED IN ARCHIVE /// DAILY STORY GENERATION COMPLETED SUCCESSFULLY ///
    </div>
</div>
<style>
    .animate-marquee { animation: marquee 20s linear infinite; }
    @keyframes marquee { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
</style>

<!-- Hero Section (Dynamic) -->
@if($latestStory)
<div class="relative overflow-hidden h-[70vh] flex items-center justify-center bg-black group">
    <div class="absolute inset-0 bg-cover bg-center transition duration-1000 transform group-hover:scale-105 opacity-60" style="background-image: url('{{ $latestStory->gorsel_url }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-black/50 to-transparent"></div>
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <span class="inline-block border border-neon-pink text-neon-pink px-2 py-1 text-xs font-mono mb-4 tracking-widest bg-black/50 backdrop-blur">LATEST_TRANSMISSION</span>
        <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-6 glitch-effect uppercase leading-tight" data-text="{{ $latestStory->baslik }}">{{ $latestStory->baslik }}</h1>
        <p class="text-gray-300 text-lg md:text-xl font-light mb-8 line-clamp-2 max-w-2xl mx-auto">{{ Str::limit(strip_tags($latestStory->metin), 150) }}</p>
        <a href="{{ route('story.show', $latestStory) }}" class="inline-block bg-neon-blue text-black font-display font-bold text-lg px-8 py-4 hover:bg-white hover:scale-105 transition duration-300 shadow-[0_0_20px_rgba(0,255,255,0.4)] clip-path-polygon">
            READ STREAM
        </a>
    </div>
</div>
@endif

<!-- Stats Bar -->
<div class="border-y border-gray-900 bg-black py-4">
    <div class="max-w-7xl mx-auto px-4 flex justify-between md:justify-around text-center font-mono text-xs md:text-sm text-gray-500">
        <div>
            <span class="block text-2xl text-neon-green font-display">{{ $stats['total_stories'] }}</span>
            <span class="tracking-widest">STORIES GENERATED</span>
        </div>
        <div>
            <span class="block text-2xl text-neon-purple font-display">{{ $stats['active_nodes'] }}</span>
            <span class="tracking-widest">ACTIVE NODES</span>
        </div>
        <div class="hidden md:block">
            <span class="block text-2xl text-red-500 font-display">{{ $stats['glitches_prevented'] }}</span>
            <span class="tracking-widest">GLITCHES PURGED</span>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative">
    
    <!-- Section Title -->
    <div class="flex items-end justify-between mb-12 border-b border-gray-800 pb-4">
        <h2 class="text-4xl font-display text-white">ARCHIVE_FEED</h2>
        <a href="{{ route('lore.index') }}" class="text-xs font-mono text-gray-500 hover:text-neon-pink transition">VIEW_FULL_DATABASE >></a>
    </div>

    <!-- Layout: Grid + Sidebar -->
    <div class="grid lg:grid-cols-4 gap-8">
        
        <!-- Story Grid (Left 3 cols) -->
        <div class="lg:col-span-3 grid md:grid-cols-2 gap-8">
            @foreach($stories as $story)
            <a href="{{ route('story.show', $story) }}" class="group block relative bg-gray-900 border border-gray-800 hover:border-neon-pink transition duration-300 overflow-hidden rounded-sm hover:shadow-neon-pink h-full flex flex-col">
                <div class="h-48 bg-gray-800 overflow-hidden relative">
                    @if($story->gorsel_url)
                        <img src="{{ $story->gorsel_url }}" alt="{{ $story->baslik }}" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700 opacity-80 group-hover:opacity-100">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-700 font-display">NO SIGNAL</div>
                    @endif
                    <div class="absolute top-2 right-2 bg-black/80 text-neon-green text-[10px] font-mono px-2 py-1 border border-neon-green/30">
                        {{ $story->mood ?? 'DATA' }}
                    </div>
                </div>
                <div class="p-6 flex-grow flex flex-col">
                     <h3 class="text-xl font-display font-bold text-white mb-2 group-hover:text-neon-pink transition duration-300 leading-tight">{{ $story->baslik }}</h3>
                     <p class="text-gray-500 text-sm line-clamp-3 mb-4 flex-grow">{{ Str::limit(strip_tags($story->metin), 100) }}</p>
                     <div class="text-xs font-mono text-gray-600 pt-4 border-t border-gray-800 flex justify-between">
                        <span>// {{ $story->yayin_tarihi->format('Y.m.d') }}</span>
                        <span>ID: {{ $story->id }}</span>
                     </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- Sidebar (Right 1 col) -->
        <div class="hidden lg:block space-y-8">
            <!-- Lore Spotlight -->
            @if($spotlightLore)
            <div class="bg-gray-900 border border-gray-800 p-1 relative group">
                <div class="absolute -top-3 -left-3 bg-neon-purple text-black font-bold font-mono text-xs px-2 py-1 transform -rotate-12 z-10 shadow-neon-purple">DATABASE_SPOTLIGHT</div>
                @include('lore.card', ['entry' => $spotlightLore])
                <div class="mt-2 text-center">
                    <a href="{{ route('lore.show', $spotlightLore->slug) }}" class="block w-full bg-gray-800 hover:bg-neon-purple hover:text-black text-gray-400 text-xs font-mono py-2 transition uppercase">
                        Access Full File
                    </a>
                </div>
            </div>
            @endif

            <!-- Ad / Banner Placeholder -->
            <div class="border border-dashed border-gray-800 p-8 text-center text-gray-600 font-mono text-xs">
                [ADS_SPACE_AVAILABLE]
                <br>Contact Night City Marketing
            </div>
        </div>
    </div>
    
    <!-- Mobile Pagination -->
    <div class="mt-12">
        {{ $stories->links() }}
    </div>

    <!-- Community Voting Section -->
    <div class="mt-24 border-t border-gray-800 py-16 bg-gradient-to-b from-transparent to-gray-900/20">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-display text-neon-purple mb-8 text-glow">/// TOMORROW'S CHRONICLE</h2>
            <p class="text-gray-400 mb-8 font-mono text-sm">Decide the fate of the City. Vote for tomorrow's headline.</p>
            
            <div id="loadingPoll" class="text-neon-blue animate-pulse">CONNECTING TO NETWORK...</div>
            
            <div id="pollOptions" class="grid md:grid-cols-1 gap-4 max-w-2xl mx-auto hidden">
                <!-- Options injected by JS -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pollContainer = document.getElementById('pollOptions');
        const loading = document.getElementById('loadingPoll');
        let pollId = null;

        // Fetch Active Poll
        fetch('/poll/active')
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                pollContainer.classList.remove('hidden');
                pollId = data.id;
                renderOptions(data.options);
            })
            .catch(err => {
                loading.innerText = 'CONNECTION ERROR: ' + err.message;
                loading.classList.remove('animate-pulse');
                loading.classList.add('text-red-500');
            });

        function renderOptions(options) {
            pollContainer.innerHTML = '';
            const totalVotes = options.reduce((sum, opt) => sum + parseInt(opt.votes), 0) || 1; // Avoid div by zero

            options.forEach(opt => {
                const percent = Math.round((opt.votes / totalVotes) * 100);
                
                const btn = document.createElement('div');
                btn.className = 'bg-gray-900 border border-gray-700 p-4 rounded hover:border-neon-purple transition cursor-pointer relative overflow-hidden group';
                btn.onclick = () => vote(opt.id);

                btn.innerHTML = `
                    <div class="absolute top-0 left-0 bottom-0 bg-neon-purple/10 transition-all duration-500" style="width: ${percent}%"></div>
                    <div class="relative flex justify-between items-center z-10">
                        <span class="font-mono text-gray-300 group-hover:text-white transition">${opt.text}</span>
                        <span class="font-display text-neon-purple text-xl">${opt.votes}</span>
                    </div>
                `;
                pollContainer.appendChild(btn);
            });
        }

        function vote(optionId) {
            fetch('/poll/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ poll_id: pollId, option_id: optionId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert('ACCESS DENIED: ' + data.error);
                } else {
                    renderOptions(data.options);
                }
            });
        }
    });
</script>
@endsection
