@extends('layouts.frontend')

@section('content')
<!-- News Ticker -->
<div class="bg-neon-green text-black font-mono text-xs py-1 overflow-hidden border-b border-gray-800 relative z-50">
    <div class="whitespace-nowrap animate-marquee inline-block">
        /// {{ __('ui.system_alert') }} /// {{ __('ui.weather_update') }} /// {{ __('ui.latest_bounty') }} /// {{ __('ui.new_lore') }} /// {{ __('ui.daily_gen') }} ///
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
        <span class="inline-block border border-neon-pink text-neon-pink px-2 py-1 text-xs font-mono mb-4 tracking-widest bg-black/50 backdrop-blur">{{ __('ui.latest_transmission') }}</span>
        <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-6 glitch-effect uppercase leading-tight" data-text="{{ $latestStory->getTitle(app()->getLocale()) }}">{{ $latestStory->getTitle(app()->getLocale()) }}</h1>
        <p class="text-gray-300 text-lg md:text-xl font-light mb-8 line-clamp-2 max-w-2xl mx-auto">{{ Str::limit(strip_tags($latestStory->getText(app()->getLocale())), 300) }}</p>
        <a href="{{ route('story.show', $latestStory) }}" class="inline-block bg-neon-blue text-black font-display font-bold text-lg px-8 py-4 hover:bg-white hover:scale-105 transition duration-300 shadow-[0_0_20px_rgba(0,255,255,0.4)] clip-path-polygon">
            {{ __('ui.read_stream') }}
        </a>
    </div>
</div>
@endif

<!-- Stats Bar -->
<div class="border-y border-gray-900 bg-black py-4">
    <div class="max-w-7xl mx-auto px-4 flex justify-between md:justify-around text-center font-mono text-xs md:text-sm text-gray-500">
        <div>
            <span class="block text-2xl text-neon-green font-display">{{ $stats['total_stories'] }}</span>
            <span class="tracking-widest">{{ __('ui.stories_generated') }}</span>
        </div>
        <div>
            <span class="block text-2xl text-neon-purple font-display">{{ $stats['active_nodes'] }}</span>
            <span class="tracking-widest">{{ __('ui.active_nodes') }}</span>
        </div>
        <div class="hidden md:block">
            <span class="block text-2xl text-red-500 font-display">{{ $stats['glitches_prevented'] }}</span>
            <span class="tracking-widest">{{ __('ui.glitches_purged') }}</span>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative">
    
    <!-- Section Title -->
    <div class="flex items-end justify-between mb-12 border-b border-gray-800 pb-4">
        <h2 class="text-4xl font-display text-white">{{ __('ui.archive_feed') }}</h2>
        <a href="{{ route('lore.index') }}" class="text-xs font-mono text-gray-500 hover:text-neon-pink transition">{{ __('ui.view_full_db') }}</a>
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
                        <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-700 font-display">{{ __('ui.no_signal') }}</div>
                    @endif
                    <div class="absolute top-2 right-2 bg-black/80 text-neon-green text-[10px] font-mono px-2 py-1 border border-neon-green/30">
                        {{ $story->mood ?? __('ui.data') }}
                    </div>
                </div>
                <div class="p-6 flex-grow flex flex-col">
                     <h3 class="text-xl font-display font-bold text-white mb-2 group-hover:text-neon-pink transition duration-300 leading-tight">{{ $story->getTitle(app()->getLocale()) }}</h3>
                     <p class="text-gray-500 text-sm line-clamp-3 mb-4 flex-grow">{{ Str::limit(strip_tags($story->getText(app()->getLocale())), 200) }}</p>
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
                <div class="absolute -top-3 -left-3 bg-neon-purple text-black font-bold font-mono text-xs px-2 py-1 transform -rotate-12 z-10 shadow-neon-purple">{{ __('ui.db_spotlight') }}</div>
                @include('lore.card', ['entry' => $spotlightLore])
                <div class="mt-2 text-center">
                    <a href="{{ route('lore.show', $spotlightLore->slug) }}" class="block w-full bg-gray-800 hover:bg-neon-purple hover:text-black text-gray-400 text-xs font-mono py-2 transition uppercase">
                        {{ __('ui.access_file') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Ad / Banner Placeholder -->
            <div class="border border-dashed border-gray-800 p-8 text-center text-gray-600 font-mono text-xs">
                {{ __('ui.ads_space') }}
                <br>{{ __('ui.contact_marketing') }}
            </div>
        </div>
    </div>
    
    <!-- Mobile Pagination -->
    <div class="mt-12">
        {{ $stories->links() }}
    </div>

    <!-- NEW: KNOWLEDGE BASE (What is Cyberpunk?) -->
    <div class="mt-24 grid lg:grid-cols-2 gap-16 items-center border-t border-gray-800 pt-16">
        <div class="space-y-6">
            <h2 class="text-3xl md:text-5xl font-display text-white leading-tight">
                <span class="text-neon-pink">HIGH TECH.</span><br>
                <span class="text-neon-blue">LOW LIFE.</span>
            </h2>
            <div class="prose prose-invert text-gray-400">
                <p>
                    <strong>Cyberpunk nedir?</strong> Sadece neon ışıklar ve robot kollar değildir. Çöküşün eşiğindeki bir toplumda, teknolojinin insanlığı nasıl hem yükselttiğini hem de hiç ettiği anlatır.
                </p>
                <p>
                    Devasa şirketlerin devletlerin yerini aldığı, verinin petrolden değerli olduğu ve insan bedeninin sadece bir "donanım" (meatbag) olarak görüldüğü distopik bir gelecektir. Anxipunk, bu evrenin İstanbul (Neo-Pera) simülasyonudur.
                </p>
            </div>
            <a href="{{ route('about') }}" class="inline-block border border-gray-600 text-gray-400 px-6 py-2 hover:border-white hover:text-white transition uppercase font-mono text-xs">
                > MANIFESTO_OKU
            </a>
        </div>
        <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-neon-pink to-neon-blue opacity-30 group-hover:opacity-75 blur transition duration-1000"></div>
            <div class="relative bg-black border border-gray-800 p-8 grid grid-cols-2 gap-4">
                <!-- Info Cards -->
                <div class="col-span-2 text-center border-b border-gray-800 pb-4 mb-4">
                    <h3 class="font-display text-white text-xl">/// THE PIONEERS</h3>
                </div>
                
                <div class="bg-gray-900/50 p-4 border border-gray-800 hover:border-neon-pink transition">
                    <h4 class="text-neon-pink font-bold font-mono text-xs mb-1">WILLIAM GIBSON</h4>
                    <p class="text-gray-500 text-[10px]">Neuromancer (1984). "Siberuzay" kelimesinin mucidi. Matrix'in babası.</p>
                </div>

                <div class="bg-gray-900/50 p-4 border border-gray-800 hover:border-neon-blue transition">
                    <h4 class="text-neon-blue font-bold font-mono text-xs mb-1">BLADE RUNNER</h4>
                    <p class="text-gray-500 text-[10px]">Ridley Scott (1982). Philip K. Dick'in eserinden uyarlama. Görsel estetiği belirledi.</p>
                </div>

                <div class="bg-gray-900/50 p-4 border border-gray-800 hover:border-neon-green transition">
                    <h4 class="text-neon-green font-bold font-mono text-xs mb-1">GHOST IN THE SHELL</h4>
                    <p class="text-gray-500 text-[10px]">Masamune Shirow (1989). İnsan ve makine arasındaki o ince çizgi.</p>
                </div>

                <div class="bg-gray-900/50 p-4 border border-gray-800 hover:border-neon-purple transition">
                    <h4 class="text-neon-purple font-bold font-mono text-xs mb-1">CYBERPUNK 2077</h4>
                    <p class="text-gray-500 text-[10px]">CD Projekt Red. Türü modern kitlelere tanıtan açık dünya RPG şaheseri.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW: Neural Art Gallery Strip -->
    <div class="mt-20 border-t border-gray-800 pt-16">
        <h2 class="text-3xl font-display text-white mb-8 flex items-center justify-between">
            <span>/// NÖRAL_SANAT_GALERİSİ</span>
            <a href="{{ route('gallery.index') }}" class="text-xs text-neon-blue hover:underline whitespace-nowrap">TÜMÜNÜ_GÖR >></a>
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                // Fetch random images for visuals
                $galleryImages = \App\Models\Story::whereNotNull('gorsel_url')->inRandomOrder()->take(4)->get();
            @endphp
            @foreach($galleryImages as $img)
                <a href="{{ route('story.show', $img) }}" class="group relative aspect-square overflow-hidden border border-gray-800 hover:border-neon-green transition">
                    <img src="{{ $img->gorsel_url }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500 filter grayscale group-hover:grayscale-0">
                    <div class="absolute inset-0 bg-black/50 group-hover:opacity-0 transition"></div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- NEW: Subscribe / Join Resistance -->
    <div class="mt-20 bg-neon-purple/5 border-y border-neon-purple/30 py-16 text-center">
        <h2 class="text-4xl font-display text-white mb-4 glitch-text" data-text="DİRENİŞE KATIL">DİRENİŞE KATIL</h2>
        <p class="text-gray-400 max-w-xl mx-auto mb-8 font-mono text-sm">
            Neo-Pera güncellemeleri, gizli lore belgeleri ve sistem uyarıları doğrudan nöral arayüzünüze iletilir.
        </p>
        <form action="{{ route('contact.store') }}" method="POST" class="max-w-md mx-auto flex gap-2">
            @csrf
            <input type="hidden" name="name" value="Abone">
            <input type="hidden" name="subject" value="Bülten Aboneliği">
            <input type="hidden" name="message" value="Direniş bültenine katılmak istiyorum.">
            
            <input type="email" name="email" placeholder="E-POSTA_ADRESİ_GİRİN" required class="flex-grow bg-black border border-neon-purple text-neon-purple p-3 focus:outline-none placeholder-purple-900">
            <button type="submit" class="bg-neon-purple text-black font-bold px-6 py-3 hover:bg-white transition">
                BAŞLAT
            </button>
        </form>
    </div>

    <!-- Community Voting Section -->
    <div class="mt-24 border-t border-gray-800 py-16 bg-gradient-to-b from-transparent to-gray-900/20">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-display text-neon-purple mb-8 text-glow">/// {{ __('ui.tomorrows_chronicle') }}</h2>
            <p class="text-gray-400 mb-8 font-mono text-sm">{{ __('ui.vote_desc') }}</p>
            
            <div id="loadingPoll" class="text-neon-blue animate-pulse">{{ __('ui.connecting') }}</div>
            
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
