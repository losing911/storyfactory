<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anxipunk Art | @yield('title', 'Cyberpunk Stories')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <meta name="description" content="@yield('meta_description', 'Daily AI Generated Cyberpunk Stories')">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    @yield('meta_tags')
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Rajdhani', 'sans-serif'],
                        display: ['Orbitron', 'sans-serif'],
                    },
                    colors: {
                        neon: {
                            pink: '#ff00ff',
                            blue: '#00ffff',
                            green: '#00ff00',
                            purple: '#bd00ff'
                        },
                        dark: '#050505'
                    },
                    boxShadow: {
                        'neon-blue': '0 0 10px #00ffff, 0 0 20px #00ffff',
                        'neon-pink': '0 0 10px #ff00ff, 0 0 20px #ff00ff',
                    }
                }
            }
        }
    </script>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <style>
        body {
            background-color: #050505;
            background-image: 
                linear-gradient(rgba(0, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        /* High Contrast Mode (Gritty B&W) */
        body.contrast-active {
            filter: grayscale(100%) contrast(120%);
        }
        .text-glow {
            text-shadow: 0 0 10px currentColor;
        }
        .glitch-effect {
            position: relative;
        }
        .glitch-effect::before,
        .glitch-effect::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .glitch-effect::before {
            left: 2px;
            text-shadow: -1px 0 #ff00ff;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim 5s infinite linear alternate-reverse;
        }
        .glitch-effect::after {
            left: -2px;
            text-shadow: -1px 0 #00ffff;
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim2 5s infinite linear alternate-reverse;
        }
        @keyframes glitch-anim {
            0% { clip: rect(14px, 9999px, 86px, 0); }
            20% { clip: rect(65px, 9999px, 7px, 0); }
            40% { clip: rect(84px, 9999px, 86px, 0); }
            60% { clip: rect(10px, 9999px, 20px, 0); }
            80% { clip: rect(54px, 9999px, 47px, 0); }
            100% { clip: rect(23px, 9999px, 3px, 0); }
        }
        @keyframes glitch-anim2 {
            0% { clip: rect(40px, 9999px, 16px, 0); }
            20% { clip: rect(18px, 9999px, 88px, 0); }
            40% { clip: rect(21px, 9999px, 63px, 0); }
            60% { clip: rect(87px, 9999px, 3px, 0); }
            80% { clip: rect(2px, 9999px, 76px, 0); }
            100% { clip: rect(69px, 9999px, 25px, 0); }
        }
    </style>
</head>
<body class="text-gray-300 antialiased min-h-screen flex flex-col">
    <header class="border-b border-gray-800 bg-black/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-3xl font-display font-black tracking-widest text-neon-blue text-glow hover:text-white transition duration-300">
                ANXIPUNK
            </a>
            <nav class="flex items-center space-x-8">
                <!-- High Contrast Toggle -->
                <button id="toggleContrast" class="text-gray-500 hover:text-white transition duration-300" title="Toggle High Contrast (B&W)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                
                <!-- Ambient Audio Toggle -->
                <button id="toggleAmbient" class="text-gray-500 hover:text-neon-blue transition duration-300" title="Şehir Sesi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                </button>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden text-neon-blue hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </nav>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenu" class="md:hidden hidden bg-black/95 backdrop-blur-xl border-b border-gray-800 absolute top-20 left-0 w-full z-40 transition-all duration-300">
            <nav class="flex flex-col p-4 space-y-4">
                <a href="{{ route('home') }}" class="font-display uppercase tracking-widest text-neon-green hover:text-white py-2 border-b border-gray-800">Hikayeler</a>
                <a href="{{ route('gallery.index') }}" class="font-display uppercase tracking-widest text-neon-blue hover:text-white py-2 border-b border-gray-800">Galeri</a>
                <a href="{{ route('lore.index') }}" class="font-display uppercase tracking-widest text-neon-pink hover:text-white py-2 border-b border-gray-800">Veri Bankası</a>
                <a href="{{ route('about') }}" class="font-display uppercase tracking-widest text-neon-purple hover:text-white py-2 border-b border-gray-800">Hakkında</a>
                @auth
                    <a href="{{ route('admin.stories.index') }}" class="font-display uppercase tracking-widest text-gray-500 hover:text-white py-2">Admin</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="border-t border-gray-800 bg-black py-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="font-display text-gray-600 tracking-widest">© 2025 ANXIPUNK // <span class="text-neon-green">SYSTEM_ONLINE</span></p>
        </div>
    </footer>

    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('SW registred: ', registration.scope))
                    .catch(err => console.log('SW registration failed: ', err));
            });
        }

        // Mobile Menu Logic
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // High Contrast Logic
        const contrastBtn = document.getElementById('toggleContrast');
        contrastBtn.addEventListener('click', () => {
            document.body.classList.toggle('contrast-active');
            contrastBtn.classList.toggle('text-white');
            contrastBtn.classList.toggle('text-gray-500');
        });

        // Ambient Audio Engine (Web Audio API)
        const toggleBtn = document.getElementById('toggleAmbient');
        let audioCtx;
        let gainNode;
        let isPlaying = false;
        
        function toggleAudio() {
             if (!audioCtx) {
                initAudio();
            }

            if (isPlaying) {
                gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1);
                toggleBtn.classList.remove('text-neon-blue', 'animate-pulse');
                toggleBtn.classList.add('text-gray-500');
                isPlaying = false;
            } else {
                audioCtx.resume();
                gainNode.gain.exponentialRampToValueAtTime(0.15, audioCtx.currentTime + 1);
                toggleBtn.classList.add('text-neon-blue', 'animate-pulse');
                toggleBtn.classList.remove('text-gray-500');
                isPlaying = true;
            }
        }

        if(toggleBtn) {
            toggleBtn.addEventListener('click', toggleAudio);
            toggleBtn.addEventListener('touchstart', (e) => {
                e.preventDefault(); // Prevent phantom clicks
                toggleAudio();
            }); // Mobile support
        }

        function initAudio() {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            audioCtx = new AudioContext();

            // Pink Noise Generator (Rain-like)
            const bufferSize = 2 * audioCtx.sampleRate;
            const noiseBuffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
            const output = noiseBuffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                const white = Math.random() * 2 - 1;
                output[i] = (lastOut + (0.02 * white)) / 1.02;
                lastOut = output[i];
                output[i] *= 3.5; 
            }
            let lastOut = 0;

            const noise = audioCtx.createBufferSource();
            noise.buffer = noiseBuffer;
            noise.loop = true;

            // Lowpass Filter (Muffle it for background feel)
            const filter = audioCtx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.value = 800;

            // Gain (Volume)
            gainNode = audioCtx.createGain();
            gainNode.gain.value = 0.001; // Start silent

            noise.connect(filter);
            filter.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            noise.start();
        }
    </script>
</body>
</html>
