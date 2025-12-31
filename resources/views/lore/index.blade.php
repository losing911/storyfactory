@extends('layouts.frontend')

@section('title', 'NEXUS PROTOCOL - DATABASE')
@section('meta_description', 'Access the Anxipunk Lore Database. Information on Cities, Factions, and Characters.')

@section('content')
<div class="pt-32 pb-20 min-h-screen">
    <div class="container mx-auto px-4">
        
        <div class="mb-20 text-center relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none">
                <span class="text-[12rem] font-black text-white font-display">LORE</span>
            </div>
            <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-6 glitch-effect relative z-10" data-text="ARCHIVE_CORE">ARCHIVE_CORE</h1>
            <p class="font-mono text-neon-green text-sm tracking-widest mb-8 max-w-2xl mx-auto leading-relaxed">
                /// WELCOME TO THE MEMORY BANKS OF NEO-PERA.<br>
                Here lies the fragmented history of the Great Collapse, the rise of the Corporate Syndicate, and the few who dare to resist in the shadows.
            </p>
            
            <!-- Universe Story (Accordion or Section) -->
            <div class="text-left max-w-4xl mx-auto bg-gray-900/50 border border-gray-800 p-8 hover:border-neon-blue transition duration-500 group">
                <h2 class="text-2xl font-display text-white mb-4 flex items-center gap-3">
                    <span class="text-neon-blue">00.</span> 
                    THE ORIGIN STORY (ARCHIVE)
                </h2>
                <div class="prose prose-invert prose-sm max-w-none text-gray-400 font-sans columns-1 md:columns-2 gap-8 mb-8">
                    <p>
                        <strong>2042: The Great Silence.</strong> It started not with a bang, but with a whisper—or rather, the absence of one. The global internet infrastructure collapsed under the weight of a rogue AI singularity event known only as "The Hush". Financial markets evaporated. Nations fractured.
                    </p>
                    <p>
                        From the ashes rose <strong>Neo-Pera</strong>. Built upon the ruins of old Istanbul, it became a sanctuary for the tech-elite and a prison for the rest. The city is now governed by <em>The Syndicate</em>, a conglomerate of biotech and security firms who control the only currency that matters: <strong>Clean Data</strong>.
                    </p>
                </div>

                <!-- AI Generated Monthly Chronicles -->
                @if(view()->exists('lore.partials.history_generated'))
                    <div class="border-t border-gray-800 pt-8 mt-8">
                        <h3 class="text-xl font-display text-neon-pink mb-4 flex items-center gap-2">
                             <span class="animate-pulse">///</span> LATEST SYSTEM LOGS (AI GENERATED)
                        </h3>
                        <div class="prose prose-invert prose-sm max-w-none text-gray-300 font-mono">
                            @include('lore.partials.history_generated')
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Section: Cities -->
        @if($cities->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-display text-neon-purple mb-8 border-b border-gray-800 pb-2">/// SECTORS & CITIES</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cities as $entry)
                    @include('lore.card', ['entry' => $entry])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Section: Factions -->
        @if($factions->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-display text-neon-pink mb-8 border-b border-gray-800 pb-2">/// SYNDICATES & FACTIONS</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($factions as $entry)
                    @include('lore.card', ['entry' => $entry])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Section: Characters -->
        @if($characters->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-display text-neon-blue mb-8 border-b border-gray-800 pb-2">/// ROGUE AGENTS & NPCS</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($characters as $entry)
                    @include('lore.card', ['entry' => $entry])
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
