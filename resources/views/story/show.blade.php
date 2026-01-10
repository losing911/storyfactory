@extends('layouts.frontend')

@section('title', $story->seo_title)
@section('meta_description', $story->seo_description)

@section('meta_tags')
    <meta property="og:title" content="{{ $story->seo_title }}" />
    <meta property="og:description" content="{{ $story->seo_description }}" />
    <meta property="og:image" content="{{ asset($story->gorsel_url) }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@anxipunkart" />
    <meta name="twitter:title" content="{{ $story->seo_title }}" />
    <meta name="twitter:description" content="{{ $story->seo_description }}" />
    <meta name="twitter:image" content="{{ asset($story->gorsel_url) }}" />
    
    <!-- Custom Styles for Story Features -->
    <style>
        /* 1. Lore Tooltips */
        .lore-link {
            position: relative;
            cursor: help;
            border-bottom: 1px dashed #00ff41; /* Neon Green */
            color: #fff;
            transition: all 0.3s;
        }
        .lore-link:hover {
            color: #00ff41;
            text-shadow: 0 0 5px #00ff41;
        }
        .lore-tooltip {
            position: absolute;
            bottom: 140%; /* Above the text */
            left: 50%;
            transform: translateX(-50%);
            width: 280px;
            background: rgba(10, 10, 10, 0.95);
            border: 1px solid #00ff41;
            padding: 12px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: #ccc;
            z-index: 100;
            border-radius: 4px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s, bottom 0.2s;
            pointer-events: none;
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.2);
            text-align: left;
            line-height: 1.4;
        }
        .lore-link:hover .lore-tooltip {
            opacity: 1;
            visibility: visible;
            bottom: 150%;
        }
        
        /* 2. Reaction Animations */
        @keyframes glitch-shake {
            0% { transform: translate(0) }
            25% { transform: translate(-2px, 2px) }
            50% { transform: translate(2px, -2px) }
            75% { transform: translate(-2px, -2px) }
            100% { transform: translate(0) }
        }
        .reaction-btn.active {
            color: #fff;
            box-shadow: 0 0 15px currentColor;
            border-color: currentColor;
        }
        .reaction-btn:hover {
            animation: glitch-shake 0.3s infinite;
        }

        /* 3. Zen Mode */
        body.zen-mode header, 
        body.zen-mode #readingBar,
        body.zen-mode .share-section,
        body.zen-mode .comments-section,
        body.zen-mode .recommendations-section, 
        body.zen-mode nav { /* Assuming nav exists in layout */
            display: none !important;
        }
        body.zen-mode .zen-controls {
            opacity: 0.3;
        }
        body.zen-mode .zen-controls:hover {
            opacity: 1;
        }
    </style>
@endsection

@section('content')
<article class="min-h-screen pb-24 relative group">
    <!-- Reading Progress Bar -->
    <div class="fixed top-0 left-0 h-1 bg-neon-pink z-50 transition-all duration-300 shadow-[0_0_10px_rgba(255,0,255,0.7)]" id="readingBar" style="width: 0%"></div>

    <!-- Zen Mode Toggle (Fixed Top Right) -->
    <div class="fixed top-4 right-4 z-[60] zen-controls mix-blend-difference">
        <button id="zenToggle" class="bg-black/80 border border-gray-700 text-gray-400 hover:text-white px-3 py-1 rounded-full text-xs font-mono backdrop-blur-md transition">
            👁️ ZEN_MODE
        </button>
    </div>

    <!-- Header -->
    <header class="relative h-[65vh] md:h-[75vh] flex items-end pb-12 md:pb-20 bg-black overflow-hidden">
        <div class="absolute inset-0" id="heroParallax">
             @if($story->gorsel_url)
                <img src="{{ $story->gorsel_url }}" alt="{{ $story->baslik }}" class="w-full h-full object-cover opacity-50 scale-110">
            @endif
             <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/60 to-transparent"></div>
        </div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center w-full">
            <div class="flex flex-wrap justify-center items-center gap-3 mb-6">
                <!-- Author Badge -->
                <div class="flex items-center gap-2 bg-black/50 backdrop-blur-sm border border-neon-green/30 px-3 py-1.5 rounded-full hover:bg-black/80 transition group">
                    @if($story->author)
                        <a href="{{ route('author.show', $story->author->slug) }}" class="flex items-center gap-2">
                            <img src="{{ $story->author->avatar }}" class="w-6 h-6 md:w-8 md:h-8 rounded-full border border-neon-green group-hover:scale-110 transition">
                            <div class="flex flex-col text-left">
                                <span class="text-neon-green font-mono text-[10px] md:text-xs tracking-widest leading-none group-hover:text-white transition">{{ $story->author->name }}</span>
                                <span class="text-gray-500 text-[8px] md:text-[10px] uppercase leading-none">{{ $story->author->role }}</span>
                            </div>
                        </a>
                    @else
                        <div class="w-8 h-8 rounded-full border border-neon-green bg-gray-800 flex items-center justify-center text-[10px]">🤖</div>
                         <div class="flex flex-col text-left">
                            <span class="text-neon-green font-mono text-xs tracking-widest leading-none">ANXIPUNK_CORE</span>
                        </div>
                    @endif
                </div>
                
                <!-- Date -->
                <div class="inline-block border border-gray-700 px-3 py-1 text-gray-400 font-mono text-[10px] md:text-sm tracking-widest bg-black/50 backdrop-blur-sm">
                    {{ $story->yayin_tarihi->format('d.m.Y') }}
                </div>

                <!-- TTS Button -->
                <button id="ttsButton" class="border border-neon-blue/50 text-neon-blue px-3 py-1 font-mono text-[10px] md:text-sm hover:bg-neon-blue/20 transition-colors flex items-center gap-1 backdrop-blur-sm">
                    <span>▶ AUDIO</span>
                </button>
            </div>

            <h1 class="text-4xl md:text-7xl font-display font-black text-white mb-4 md:mb-8 leading-tight text-glow filter drop-shadow-lg glitch-effect" data-text="{{ $story->baslik }}">
                {{ $story->baslik }}
            </h1>
            
            <div class="flex flex-wrap justify-center gap-2 text-xs font-mono text-gray-400">
                @if($story->etiketler)
                    @foreach($story->etiketler as $etiket)
                        <span class="text-neon-blue bg-neon-blue/10 px-2 py-0.5 rounded">#{{ $etiket }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="max-w-3xl mx-auto px-4 py-8 md:py-12 relative">
        <!-- Sidebar Line (Desktop) -->
        <div class="absolute left-4 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-neon-pink to-transparent hidden lg:block opacity-50"></div>

        <!-- SEO Intro -->
        @if($story->sosyal_ozet)
        <div class="mb-8 p-4 md:p-6 bg-gray-900/30 border-l-4 border-neon-green font-sans text-base md:text-lg text-gray-300 italic leading-relaxed backdrop-blur-sm">
            {{ $story->sosyal_ozet }}
        </div>
        @endif

        <!-- Story Text -->
        <div class="prose prose-invert prose-sm md:prose-lg max-w-none text-gray-300 font-sans leading-relaxed overflow-hidden" id="storyContent">
             <!-- The processed_content includes user's text + Lore Links with tooltips -->
            {!! $story->processed_content !!}
        </div>
        
        <!-- Reaction Bar -->
        <div class="mt-12 py-8 border-t border-b border-gray-800 share-section">
            <h4 class="font-mono text-xs text-center text-gray-500 mb-6 tracking-[0.2em] uppercase">/// NEURAL_FEEDBACK_LOOP</h4>
            <div class="flex justify-center gap-6 md:gap-12">
                <!-- Overload -->
                <button class="reaction-btn group flex flex-col items-center gap-2 text-gray-500 transition-all duration-300" data-type="overload">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-full border border-gray-700 bg-gray-900 group-hover:border-neon-pink flex items-center justify-center text-2xl transition-all">
                        ⚡
                    </div>
                    <span class="font-mono text-[10px] md:text-xs">OVERLOAD</span>
                    <span class="count font-bold text-neon-pink text-sm" id="count-overload">{{ $reactions['overload'] ?? 0 }}</span>
                </button>

                <!-- Link -->
                <button class="reaction-btn group flex flex-col items-center gap-2 text-gray-500 transition-all duration-300" data-type="link">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-full border border-gray-700 bg-gray-900 group-hover:border-neon-blue flex items-center justify-center text-2xl transition-all">
                        🧬
                    </div>
                    <span class="font-mono text-[10px] md:text-xs">LINK</span>
                    <span class="count font-bold text-neon-blue text-sm" id="count-link">{{ $reactions['link'] ?? 0 }}</span>
                </button>

                <!-- Flatline -->
                <button class="reaction-btn group flex flex-col items-center gap-2 text-gray-500 transition-all duration-300" data-type="flatline">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-full border border-gray-700 bg-gray-900 group-hover:border-red-500 flex items-center justify-center text-2xl transition-all">
                        💀
                    </div>
                    <span class="font-mono text-[10px] md:text-xs">FLATLINE</span>
                    <span class="count font-bold text-red-500 text-sm" id="count-flatline">{{ $reactions['flatline'] ?? 0 }}</span>
                </button>
            </div>
        </div>
        
        <!-- Share Links -->
        <div class="mt-8 pt-4 share-section">
             <h4 class="font-display text-neon-blue mb-4 text-xs tracking-widest uppercase">Initiate Share Protocol</h4>
             <div class="flex flex-wrap gap-2 md:gap-4">
                 <a href="https://twitter.com/intent/tweet?text={{ urlencode($story->baslik) }}&url={{ urlencode(route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-blue text-gray-300 hover:text-neon-blue px-4 py-2 text-xs font-mono transition">X_COM</a>
                 <a href="https://wa.me/?text={{ urlencode($story->baslik . ' ' . route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-green text-gray-300 hover:text-neon-green px-4 py-2 text-xs font-mono transition">WHATSAPP</a>
             </div>
        </div>

    </div>

    <!-- Similar Stories -->
    @if(isset($similarStories) && $similarStories->count() > 0)
    <div class="max-w-6xl mx-auto px-4 mt-8 pb-12 recommendations-section">
        <h4 class="font-display text-white mb-6 text-sm tracking-widest uppercase border-l-4 border-neon-purple pl-4">/// NEURAL_RECOMMENDATIONS</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($similarStories as $simStory)
            <a href="{{ route('story.show', $simStory) }}" class="group block bg-gray-900 border border-gray-800 hover:border-neon-purple transition duration-300">
                <div class="relative h-32 overflow-hidden">
                    @if($simStory->gorsel_url)
                        <img src="{{ $simStory->gorsel_url }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500 opacity-60 group-hover:opacity-100">
                    @else
                         <div class="w-full h-full bg-gray-800 flex items-center justify-center"><span class="text-xs font-mono text-gray-600">NO_DATA</span></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent"></div>
                </div>
                <div class="p-4">
                    <h5 class="text-neon-blue font-display text-sm truncate group-hover:text-neon-pink transition">{{ $simStory->baslik }}</h5>
                    <div class="text-xs text-gray-500 mt-2 font-mono flex justify-between">
                        <span>{{ $simStory->yayin_tarihi->format('d.m') }}</span>
                        <span>READ_LOG</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Hacker Chat -->
    <div class="border-t border-gray-900 bg-black pt-16 pb-24 comments-section relative overflow-hidden">
        <!-- Background Grid for Terminal Feel -->
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10 pointer-events-none"></div>
        
        <div class="max-w-2xl mx-auto px-4 relative z-10">
            <div class="bg-gray-900/50 backdrop-blur border border-gray-800 p-6 font-mono text-sm shadow-[0_0_30px_rgba(0,255,65,0.05)] rounded-sm">
                <div class="flex items-center justify-between mb-6 border-b border-gray-800 pb-2">
                    <h3 class="text-neon-green tracking-widest text-xs uppercase">/// NETRUNNER_COMM_CHANNEL_v2.4</h3>
                    <div class="flex gap-2">
                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    </div>
                </div>
                
                <div id="commentList" class="space-y-4 mb-6 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($story->comments as $comment)
                        <div class="group">
                            <div class="flex justify-between text-[10px] uppercase text-gray-500 mb-1 tracking-wider">
                                <span class="text-neon-pink font-bold">root@user: {{ $comment->nickname ?? 'anonymous' }}</span>
                                <span>[{{ $comment->created_at->format('H:i:s / d.m.Y') }}]</span>
                            </div>
                            <div class="text-gray-300 text-xs md:text-sm pl-3 border-l-2 border-gray-700 group-hover:border-neon-green group-hover:text-white transition-colors py-1">
                                > {{ $comment->message }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-600 italic py-8 text-xs">
                            > NO_DATA_STREAM_FOUND. BE THE FIRST TO INJECT CODE.
                        </div>
                    @endforelse
                </div>
    
                <!-- Input Form -->
                <div class="border-t border-gray-700/50 pt-4 mt-2">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-neon-blue text-xs font-bold whitespace-nowrap">root@anxipunk:~$</span>
                            <input type="text" id="nickInput" placeholder="identify_yourself" class="bg-transparent border-b border-gray-700 focus:border-neon-green text-gray-300 focus:outline-none w-full py-1 text-xs font-mono tracking-wide placeholder-gray-600">
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-neon-blue text-xs font-bold whitespace-nowrap">>></span>
                            <textarea id="msgInput" rows="2" placeholder="inject_comment..." class="bg-transparent border border-gray-800 focus:border-neon-green p-2 text-gray-300 focus:outline-none w-full text-xs font-mono resize-none placeholder-gray-600"></textarea>
                        </div>
                        <button id="submitComment" class="self-end bg-gray-900 border border-gray-700 hover:border-neon-green text-gray-400 hover:text-neon-green px-6 py-2 text-[10px] font-bold uppercase transition-all tracking-widest hover:shadow-[0_0_10px_rgba(0,255,65,0.2)]">
                            [EXECUTE_SEND_PROTOCOL]
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emote Comments Section -->
    <div class="max-w-3xl mx-auto px-4 py-12 comments-section">
        <h3 class="font-display text-neon-purple text-lg mb-6 tracking-wider uppercase flex items-center gap-3">
            <span class="w-3 h-3 bg-neon-purple rounded-full animate-pulse"></span>
            /// COMMUNITY_FEEDBACK
        </h3>
        <script defer src="https://i.emote.com/js/emote.js"></script>
        <div id="emote_com"></div>
    </div>
</article>

<script>
    // 1. Reading Progress Bar
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        document.getElementById('readingBar').style.width = scrollPercent + '%';
    });

    // 2. Zen Mode Toggle
    const zenBtn = document.getElementById('zenToggle');
    let isZen = false;
    zenBtn.addEventListener('click', () => {
        isZen = !isZen;
        if(isZen) {
            document.body.classList.add('zen-mode');
            zenBtn.innerText = '❌ DISABLE_ZEN';
            zenBtn.classList.replace('text-gray-400', 'text-neon-pink');
        } else {
            document.body.classList.remove('zen-mode');
            zenBtn.innerText = '👁️ ZEN_MODE';
            zenBtn.classList.replace('text-neon-pink', 'text-gray-400');
        }
    });
    
    // 3. Reactions
    const reactionBtns = document.querySelectorAll('.reaction-btn');
    const storyId = {{ $story->id }};
    
    reactionBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            const countSpan = btn.querySelector('.count');
            
            // Send Request
            fetch('{{ route('story.react', $story) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ type: type })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // Update counts
                    for (const [rType, rCount] of Object.entries(data.counts)) {
                        const span = document.getElementById(`count-${rType}`);
                        if(span) span.innerText = rCount;
                    }
                    // Highlight Active
                    if(data.action === 'added') btn.classList.add('active');
                    else btn.classList.remove('active');
                }
            })
            .catch(err => console.error('Reaction Error:', err));
        });
    });

    // 4. TTS Audio (Robust)
    const ttsBtn = document.getElementById('ttsButton');
    let audioObj = null;
    let isPlaying = false;

    ttsBtn.addEventListener('click', () => {
        if(isPlaying) {
            if(audioObj) { audioObj.pause(); audioObj.currentTime = 0; }
            isPlaying = false;
            ttsBtn.innerHTML = '<span>▶ AUDIO</span>';
            ttsBtn.classList.remove('bg-neon-blue', 'text-black');
            return;
        }

        if(!audioObj) {
            ttsBtn.innerHTML = '<span>⏳ LOADING...</span>';
            const audioUrl = "{{ route('story.audio', $story) }}";
            audioObj = new Audio(audioUrl);
            
            audioObj.addEventListener('error', (e) => {
                console.error("Audio Load Error:", e);
                // Fallback to Native Browser TTS if Server TTS fails (Network Error / No Key)
                ttsBtn.innerHTML = '<span>⚠️ FALLBACK TTS</span>';
                
                // Native Browser TTS Fallback
                const contentText = document.getElementById('storyContent').innerText;
                const utterance = new SpeechSynthesisUtterance(contentText.substring(0, 500) + '... (Audio Preview)');
                utterance.lang = 'tr-TR';
                utterance.pitch = 0.8;
                utterance.rate = 0.9;
                
                utterance.onend = () => {
                     isPlaying = false;
                     ttsBtn.innerHTML = '<span>▶ AUDIO (BROWSER)</span>';
                     ttsBtn.classList.remove('bg-neon-blue', 'text-black');
                };
                
                window.speechSynthesis.speak(utterance);
                
                isPlaying = true;
                ttsBtn.innerHTML = '<span>⏹ STOP (NATIVE)</span>';
                ttsBtn.classList.add('bg-neon-blue', 'text-black');
                
                // Don't use Audio Object anymore
                audioObj = null; 
            });

            audioObj.addEventListener('canplaythrough', () => {
                if(isPlaying) return;
                audioObj.play().then(() => {
                    isPlaying = true;
                    ttsBtn.innerHTML = '<span>⏹ STOP AUDIO</span>';
                    ttsBtn.classList.add('bg-neon-blue', 'text-black');
                }).catch(err => {
                    ttsBtn.innerHTML = '<span>▶ TAP TO PLAY</span>';
                });
            });
            
            audioObj.addEventListener('ended', () => {
                isPlaying = false;
                ttsBtn.innerHTML = '<span>▶ AUDIO</span>';
                ttsBtn.classList.remove('bg-neon-blue', 'text-black');
            });

            audioObj.load();
        } else {
            audioObj.play();
            isPlaying = true;
            ttsBtn.innerHTML = '<span>⏹ STOP AUDIO</span>';
            ttsBtn.classList.add('bg-neon-blue', 'text-black');
        }
    });

    // 5. Ambient Music (Button injection)
    const storyMood = "{{ $story->mood ?? 'mystery' }}";
    const customTrack = "{{ $story->music_url ?? '' }}"; 
    const isCustom = customTrack && customTrack.length > 5;
    const audioTracks = {
        'action': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 
        'mystery': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3',
        'melancholy': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
        'high-tech': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3',
        'corruption': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3'
    };
    const finalTrack = isCustom ? customTrack : (audioTracks[storyMood] || audioTracks['mystery']);
    
    let bgAudio = new Audio(finalTrack);
    bgAudio.loop = true;
    bgAudio.volume = 0.3;

    const musicControl = document.createElement('button');
    musicControl.className = 'border border-gray-700 px-3 py-1 font-mono text-[10px] md:text-sm hover:bg-gray-800 transition-colors flex items-center gap-1 backdrop-blur-sm';
    const label = isCustom ? "SYNTH_OST" : `OS_${storyMood.toUpperCase()}`;
    musicControl.innerHTML = `<span>🎵 ${label}</span>`;
    
    const ttsButton2 = document.getElementById('ttsButton');
    if(ttsButton2 && ttsButton2.parentNode) {
        ttsButton2.parentNode.insertBefore(musicControl, ttsButton2.nextSibling);
    }

    let isMusicPlaying = false;
    musicControl.addEventListener('click', () => {
        if(isMusicPlaying) {
            bgAudio.pause();
            musicControl.classList.remove('text-neon-purple', 'border-neon-purple');
            musicControl.classList.add('text-gray-400');
            isMusicPlaying = false;
        } else {
            bgAudio.play().catch(e => console.log("Audio Play Blocked", e));
            musicControl.classList.add('text-neon-purple', 'border-neon-purple');
            musicControl.classList.remove('text-gray-400');
            isMusicPlaying = true;
        }
    });

    // 6. Hacker Chat Submit
    const submitBtn = document.getElementById('submitComment');
    const msgInput = document.getElementById('msgInput');
    const nickInput = document.getElementById('nickInput');
    const commentList = document.getElementById('commentList');

    if(submitBtn) {
        submitBtn.addEventListener('click', () => {
            const msg = msgInput.value.trim();
            const nick = nickInput.value.trim();
            if(!msg) return;

            submitBtn.innerText = '[...]';
            
            fetch('{{ route('comment.store', $story) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ message: msg, nickname: nick })
            })
            .then(res => res.json())
            .then(data => {
                if(data.id) {
                    const html = `
                        <div class="group animate-pulse">
                            <div class="flex justify-between text-[10px] text-gray-500 mb-1">
                                <span class="text-neon-pink">user_root: ${data.nickname}</span>
                                <span>Just now</span>
                            </div>
                            <div class="text-gray-300 text-xs pl-2 border-l border-gray-800 group-hover:border-neon-green group-hover:text-neon-green transition-colors">
                                ${data.message}
                            </div>
                        </div>`;
                    commentList.insertAdjacentHTML('afterbegin', html);
                    msgInput.value = '';
                    submitBtn.innerText = '[EXECUTE_SEND]';
                }
            })
            .catch(err => {
                submitBtn.innerText = '[ERR]';
            });
        });
    }
</script>
@endsection
