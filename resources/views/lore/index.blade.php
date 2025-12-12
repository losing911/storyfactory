@extends('layouts.frontend')

@section('title', 'NEXUS PROTOCOL - DATABASE')
@section('meta_description', 'Access the Anxipunk Lore Database. Information on Cities, Factions, and Characters.')

@section('content')
<div class="pt-32 pb-20 min-h-screen">
    <div class="container mx-auto px-4">
        
        <!-- Header -->
        <div class="mb-16 text-center">
            <h1 class="text-4xl md:text-6xl font-display font-black text-white mb-4 glitch-effect" data-text="NEXUS_PROTOCOL">NEXUS_PROTOCOL</h1>
            <p class="font-mono text-neon-green text-sm tracking-widest">/// ACCESSING ENCRYPTED ARCHIVES...</p>
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
