@extends('layouts.frontend')

@section('title', $story->meta_baslik ?? $story->baslik)
@section('meta_description', $story->meta_aciklama ?? Str::limit(strip_tags($story->metin), 160))

@section('meta_tags')
    <meta property="og:title" content="{{ $story->meta_baslik ?? $story->baslik }}" />
    <meta property="og:description" content="{{ $story->meta_aciklama ?? Str::limit(strip_tags($story->metin), 160) }}" />
    <meta property="og:image" content="{{ asset($story->gorsel_url) }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@anxipunkart" />
    <meta name="twitter:title" content="{{ $story->meta_baslik ?? $story->baslik }}" />
    <meta name="twitter:description" content="{{ $story->meta_aciklama ?? Str::limit(strip_tags($story->metin), 160) }}" />
    <meta name="twitter:image" content="{{ asset($story->gorsel_url) }}" />
@endsection

@section('content')
<article class="min-h-screen">
    <!-- Reading Progress Bar -->
    <div class="fixed top-0 left-0 h-1 bg-neon-pink z-50 transition-all duration-300 shadow-[0_0_10px_rgba(255,0,255,0.7)]" id="readingBar" style="width: 0%"></div>

    <!-- Header -->
    <header class="relative h-[70vh] flex items-end pb-20 bg-black overflow-hidden">
        <div class="absolute inset-0" id="heroParallax">
             @if($story->gorsel_url)
                <img src="{{ $story->gorsel_url }}" alt="{{ $story->baslik }}" class="w-full h-full object-cover opacity-50 scale-110">
            @endif
             <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/60 to-transparent"></div>
        </div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center w-full">
            <div class="flex justify-center items-center gap-4 mb-6">
                 <div class="inline-block border border-neon-green/30 px-3 py-1 text-neon-green font-mono text-sm tracking-widest bg-black/50 backdrop-blur-sm">
                    {{ $story->yayin_tarihi->format('d F Y') }} // {{ $story->konu }}
                </div>
                <!-- TTS Button -->
                <button id="ttsButton" class="border border-neon-blue/50 text-neon-blue px-3 py-1 font-mono text-sm hover:bg-neon-blue/20 transition-colors flex items-center gap-2">
                    <span>▶ AUDIO_PROTOCOL</span>
                </button>
            </div>

            <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-8 leading-tight text-glow filter drop-shadow-lg glitch-effect" data-text="{{ $story->baslik }}">
                {{ $story->baslik }}<span class="sr-only"> - Anxipunk Cyberpunk Story Archive</span>
            </h1>
            
            <div class="flex justify-center gap-4 text-sm font-mono text-gray-400">
                @if($story->etiketler)
                    @foreach($story->etiketler as $etiket)
                        <span class="text-neon-blue">#{{ $etiket }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    </header>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-4 py-12 relative">
        <!-- Sidebar Line -->
        <div class="absolute left-4 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-neon-pink to-transparent hidden lg:block opacity-50"></div>

        <div class="prose prose-invert prose-lg max-w-none text-gray-300 font-sans leading-relaxed" id="storyContent">
            {!! $story->metin !!}
        </div>

        <!-- Share Protocol -->
        <div class="mt-16 pt-8 border-t border-gray-800">
            <h4 class="font-display text-neon-blue mb-6 text-sm tracking-widest uppercase">Initiate Share Protocol</h4>
            <div class="flex flex-wrap gap-4">
                 <a href="https://twitter.com/intent/tweet?text={{ urlencode($story->baslik) }}&url={{ urlencode(route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-blue text-gray-300 hover:text-neon-blue px-6 py-3 transition duration-300 group">
                    <span class="font-mono text-xs">X_COM</span>
                 </a>
                 <a href="https://wa.me/?text={{ urlencode($story->baslik . ' ' . route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-green text-gray-300 hover:text-neon-green px-6 py-3 transition duration-300 group">
                    <span class="font-mono text-xs">WHATSAPP_NET</span>
                 </a>
                 <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-pink text-gray-300 hover:text-neon-pink px-6 py-3 transition duration-300 group">
                    <span class="font-mono text-xs">FACEBOOK_LNK</span>
                 </a>
            </div>
        </div>
    </div>
    <!-- Hacker Chat (Terminal Style) -->
    <div class="mt-20 border-t-2 border-dashed border-gray-800 pt-12 pb-24">
        <div class="max-w-2xl mx-auto bg-black border border-gray-800 p-6 font-mono text-sm shadow-[0_0_20px_rgba(0,0,0,0.8)]">
            <h3 class="text-neon-green mb-4 border-b border-gray-800 pb-2">/// NETRUNNER_COMM_CHANNEL</h3>
            
            <div id="commentList" class="space-y-4 mb-8 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                @foreach($story->comments as $comment)
                    <div class="group">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span class="text-neon-pink">user_root: {{ $comment->nickname ?? 'anonymous' }}</span>
                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-gray-300 pl-4 border-l border-gray-800 group-hover:border-neon-green group-hover:text-neon-green transition-colors">
                            {{ $comment->message }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input Form -->
            <div class="border-t border-gray-800 pt-4">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-neon-blue">root@anxipunk:~$</span>
                        <input type="text" id="nickInput" placeholder="Enter Nickname (Optional)" class="bg-transparent border-b border-gray-800 focus:border-neon-green text-gray-300 focus:outline-none w-full py-1 text-xs">
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-neon-blue">root@anxipunk:~$</span>
                        <textarea id="msgInput" rows="2" placeholder="Inject Message..." class="bg-transparent border-0 focus:ring-0 text-gray-300 focus:outline-none w-full py-1 resize-none"></textarea>
                    </div>
                    <button id="submitComment" class="self-end border border-gray-800 hover:border-neon-green text-gray-500 hover:text-neon-green px-4 py-1 text-xs uppercase transition tracking-widest mt-2">
                        [EXECUTE_SEND]
                    </button>
                </div>
            </div>
        </div>
    </div>
</article>

<script>
    // 1. Reading Progress Bar Logic
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        document.getElementById('readingBar').style.width = scrollPercent + '%';
    });

    // 2. Text-to-Speech (TTS) Logic
    const ttsBtn = document.getElementById('ttsButton');
    const content = document.getElementById('storyContent').innerText;
    let isSpeaking = false;
    let utterance = null;

    if ('speechSynthesis' in window) {
        ttsBtn.addEventListener('click', () => {
            const synth = window.speechSynthesis;

            if (isSpeaking) {
                // Stop
                synth.cancel();
                isSpeaking = false;
                ttsBtn.innerHTML = '<span>▶ AUDIO_PROTOCOL</span>';
                ttsBtn.classList.remove('bg-neon-blue', 'text-black');
            } else {
                // Start
                utterance = new SpeechSynthesisUtterance(content);
                utterance.lang = 'tr-TR'; // Turkish
                utterance.rate = 0.9; // Slightly slower
                utterance.pitch = 0.8; // Deep/Robotic
                
                // Try to find a good voice
                const voices = synth.getVoices();
                // Prefer a male/deep voice if available (optional filter)
                
                utterance.onend = () => {
                    isSpeaking = false;
                    ttsBtn.innerHTML = '<span>▶ AUDIO_PROTOCOL</span>';
                    ttsBtn.classList.remove('bg-neon-blue', 'text-black');
                };

                synth.speak(utterance);
                isSpeaking = true;
                ttsBtn.innerHTML = '<span>⏹ TERMINATE_AUDIO</span>';
                ttsBtn.classList.add('bg-neon-blue', 'text-black');
            }
        });
    } else {
        ttsBtn.style.display = 'none'; // Not supported
    }

    // 3. Parallax Hero Effect
    const heroParallax = document.getElementById('heroParallax');
    if (heroParallax) {
        window.addEventListener('scroll', () => {
            const scrolly = window.scrollY;
            heroParallax.style.transform = `translateY(${scrolly * 0.5}px)`;
        });
    }

    // 4. Dynamic Soundtrack (Mood Based) -- NEURAL_LINK
    const storyMood = "{{ $story->mood ?? 'mystery' }}";
    const customTrack = "{{ $story->music_url ?? '' }}"; // New AI Generated Track

    const audioTracks = {
        'action': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 
        'mystery': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3',
        'melancholy': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
        'high-tech': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3',
        'corruption': 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3'
    };
    
    // Use custom track if available, else mood fallback
    const finalTrack = customTrack && customTrack.length > 5 ? customTrack : (audioTracks[storyMood] || audioTracks['mystery']);
    const isCustom = customTrack && customTrack.length > 5;

    // Create Audio Elements
    let bgAudio = new Audio(finalTrack);
    bgAudio.loop = true;
    bgAudio.volume = 0.4;
    
    // UI Control
    const metaContainer = document.querySelector('.max-w-4xl.mx-auto.text-center.mb-12'); // This selector might be fragile if HTML changes
    // Fallback if not found: document.body
    const targetContainer = document.querySelector('.flex.justify-center.items-center.gap-4.mb-6') || document.body;

    const musicControl = document.createElement('button');
    musicControl.className = 'border border-gray-700 px-3 py-1 font-mono text-sm hover:bg-gray-800 transition-colors flex items-center gap-2 text-gray-400';
    
    const label = isCustom ? "AI_OST_GENERATED" : `NEURAL_OST [${storyMood.toUpperCase()}]`;
    musicControl.innerHTML = `<span>🎵 ${label}</span>`;
    
    // Insert after the TTS button
    const ttsButton = document.getElementById('ttsButton');
    if(ttsButton && ttsButton.parentNode) {
        ttsButton.parentNode.insertBefore(musicControl, ttsButton.nextSibling);
    } else {
        // Fallback append
        if(targetContainer) targetContainer.appendChild(musicControl);
    }

    /* Reseting lines to match replacement logic */

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

    // 5. Hacker Chat Logic
    const submitBtn = document.getElementById('submitComment');
    const msgInput = document.getElementById('msgInput');
    const nickInput = document.getElementById('nickInput');
    const commentList = document.getElementById('commentList');

    submitBtn.addEventListener('click', () => {
        const msg = msgInput.value.trim();
        const nick = nickInput.value.trim();
        if(!msg) return;

        submitBtn.innerText = '[TRANSMITTING...]';
        
        fetch('{{ route('comment.store', $story) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg, nickname: nick })
        })
        .then(res => res.json())
        .then(data => {
            if(data.id) {
                // Add to list
                const html = `
                    <div class="group animate-pulse">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span class="text-neon-pink">user_root: ${data.nickname}</span>
                            <span>Just now</span>
                        </div>
                        <div class="text-gray-300 pl-4 border-l border-gray-800 group-hover:border-neon-green group-hover:text-neon-green transition-colors">
                            ${data.message}
                        </div>
                    </div>
                `;
                commentList.insertAdjacentHTML('afterbegin', html);
                msgInput.value = '';
                submitBtn.innerText = '[EXECUTE_SEND]';
            }
        })
        .catch(err => {
            alert('TRANSMISSION FAILED');
            submitBtn.innerText = '[ERROR]';
        });
    });
</script>
@endsection
