<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anxipunk Art | @yield('title', 'Cyberpunk Stories')</title>
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
    <style>
        body {
            background-color: #050505;
            background-image: 
                linear-gradient(rgba(0, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
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
                ANXIPUNK<span class="text-neon-pink text-sm ml-1">.ART</span>
            </a>
            <nav class="hidden md:flex space-x-8">
                <a href="{{ route('home') }}" class="font-display uppercase tracking-widest hover:text-neon-green transition">Stories</a>
                <a href="{{ route('about') }}" class="font-display uppercase tracking-widest hover:text-neon-purple transition">About</a>
                @auth
                    <a href="{{ route('admin.stories.index') }}" class="font-display uppercase tracking-widest text-gray-500 hover:text-white">Admin</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="border-t border-gray-800 bg-black py-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="font-display text-gray-600 tracking-widest">© 2025 ANXIPUNK.ART // SYSTEM_ONLINE</p>
        </div>
    </footer>
</body>
</html>
