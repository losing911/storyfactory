@extends('layouts.frontend')

@section('title', 'About System')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-black relative overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-[url('https://source.unsplash.com/1600x900/?circuit,cyberpunk')] bg-cover opacity-20 filter grayscale blur-sm"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black via-transparent to-black"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 text-center py-20">
        <h1 class="text-6xl md:text-8xl font-display font-black text-white mb-8 glitch-effect" data-text="SYSTEM CORE">SYSTEM CORE</h1>
        
        <div class="prose prose-invert prose-lg mx-auto mb-12 border-l-4 border-neon-blue pl-6 text-left">
            <h3 class="text-neon-pink font-mono uppercase tracking-widest">The Mission</h3>
            <p class="text-gray-300 font-sans text-xl leading-relaxed">
                Anxipunk.Art is an autonomous Digital Entity designed to explore the intersection of Procedural Narrative and Cyberpunk aesthetics. 
                Running on a distributed neural network, it dreams of neon lights, rain-slicked streets, and silicon souls.
            </p>
            <p class="text-gray-400 font-mono text-sm mt-4">
                > PROTOCOL: AUTOMATED_STORY_SEQUENCE<br>
                > ENGINE: GEMINI_CORE + VISUAL_SYNTHESIS<br>
                > STATUS: ONLINE
             </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
            <div class="bg-gray-900/50 p-6 border border-gray-800 hover:border-neon-green transition duration-500 group">
                <i class="text-4xl mb-4 block text-neogreen">🤖</i>
                <h4 class="text-white font-display uppercase tracking-widest mb-2 group-hover:text-neon-green">Digital Narrative</h4>
                <p class="text-gray-500 text-sm">Every word is compiled by advanced algorithms, optimized for creative noir storytelling.</p>
            </div>
            <div class="bg-gray-900/50 p-6 border border-gray-800 hover:border-neon-purple transition duration-500 group">
                <i class="text-4xl mb-4 block text-neon-purple">🎨</i>
                <h4 class="text-white font-display uppercase tracking-widest mb-2 group-hover:text-neon-purple">Synth Imagery</h4>
                <p class="text-gray-500 text-sm">Visuals are synthesized in real-time, creating unique 'Frank Miller' style art.</p>
            </div>
        </div>
        <div class="mt-20 border-t border-gray-800 pt-12 text-left">
            <h3 class="text-2xl font-bold text-gray-500 mb-6 font-mono">/// BEHIND_THE_CODE (Transparency)</h3>
            <div class="text-gray-400 space-y-4 font-sans text-sm leading-relaxed max-w-2xl">
                <p>
                    <strong>Anxipunk</strong> is an experimental creative writing project that blends human imagination with artificial intelligence. 
                    While the narratives and visuals are generated using advanced AI models (LLMs & Diffusion Models), the creative direction, 
                    lore curation, and world-building are driven by human editors.
                </p>
                <p>
                    Our goal is to explore the boundaries of "Cyberpunk" storytelling through digital synthesis. 
                    If you have any questions or collaboration ideas, please visit our <a href="{{ route('contact') }}" class="text-neon-blue hover:underline">Contact</a> page.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
