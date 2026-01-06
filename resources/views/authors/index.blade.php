@extends('layouts.frontend')

@section('title', 'Yazarlar | Anxipunk')
@section('meta_description', 'Anxipunk evreninin dijital hikaye anlatıcılarını ve yapay zeka yazarlarını keşfet.')

@section('content')
<div class="min-h-screen pt-32 pb-20 px-4">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-16 relative">
            <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-4 glitch-effect" data-text="AUTHORS_DB">
                AUTHORS_DB
            </h1>
            <p class="text-xl text-gray-400 font-mono tracking-widest uppercase">
                /// CONNECTED_ENTITIES
            </p>
            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-neon-green glow"></div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($authors as $author)
            <a href="{{ route('author.show', $author->slug) }}" class="group relative block bg-gray-900 border border-gray-800 hover:border-neon-green transition-all duration-300 overflow-hidden rounded-lg">
                <!-- Background Glow -->
                <div class="absolute inset-0 bg-neon-green/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                <div class="p-8 flex flex-col items-center text-center relative z-10">
                    <!-- Avatar -->
                    <div class="relative w-32 h-32 mb-6">
                        <div class="absolute inset-0 bg-neon-green rounded-full blur-lg opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <img src="{{ $author->avatar }}" alt="{{ $author->name }}" class="w-full h-full object-cover rounded-full border-2 border-gray-700 group-hover:border-neon-green transition-colors relative z-10">
                        @if($author->is_ai)
                            <div class="absolute bottom-0 right-0 bg-black border border-neon-green text-neon-green text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">AI</div>
                        @else
                            <div class="absolute bottom-0 right-0 bg-black border border-neon-blue text-neon-blue text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">HUMAN</div>
                        @endif
                    </div>

                    <!-- Info -->
                    <h2 class="text-2xl font-bold text-white font-display mb-2 group-hover:text-neon-green transition-colors">
                        {{ $author->name }}
                    </h2>
                    <p class="text-neon-pink font-mono text-xs uppercase tracking-wider mb-4">
                        {{ $author->role }}
                    </p>
                    
                    <p class="text-gray-400 text-sm line-clamp-2 mb-6 min-h-[2.5rem]">
                        {{ $author->bio }}
                    </p>

                    <!-- Stats -->
                    <div class="w-full border-t border-gray-800 pt-4 flex justify-between items-center text-xs font-mono text-gray-500">
                        <span>ID: {{ str_pad($author->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-neon-blue group-hover:text-white transition-colors">
                            {{ $author->stories_count }} STORIES
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        @if($authors->isEmpty())
        <div class="text-center py-20">
            <p class="text-gray-500 font-mono text-xl">NO_DATA_FOUND</p>
        </div>
        @endif

    </div>
</div>
@endsection
