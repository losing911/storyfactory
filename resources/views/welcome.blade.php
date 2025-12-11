@extends('layouts.frontend')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden h-[60vh] flex items-center justify-center bg-black">
    <div class="absolute inset-0 bg-cover bg-center opacity-40 grayscale hover:grayscale-0 transition duration-1000 transform hover:scale-105" style="background-image: url('https://source.unsplash.com/1600x900/?cyberpunk,city,neon,rain');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-transparent to-transparent"></div>
    <div class="relative z-10 text-center px-4">
        <h1 class="text-6xl md:text-8xl font-display font-black text-white mb-4 glitch-effect" data-text="NEON DREAMS">NEON DREAMS</h1>
        <p class="text-xl md:text-2xl text-neon-blue font-light tracking-[0.5em] uppercase">Daily Tales from the Circuit City</p>
    </div>
</div>

<!-- Latest Stories -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($stories as $story)
        <a href="{{ route('story.show', $story) }}" class="group block relative bg-gray-900 border border-gray-800 hover:border-neon-pink transition duration-300 overflow-hidden rounded-sm hover:shadow-neon-pink">
            <div class="h-64 bg-gray-800 overflow-hidden relative">
                @if($story->gorsel_url)
                    <img src="{{ $story->gorsel_url }}" alt="{{ $story->baslik }}" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700 opacity-80 group-hover:opacity-100">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-700 font-display">NO SIGNAL</div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>
            </div>
            <div class="p-6 relative">
                <div class="text-neon-green text-xs font-mono mb-2 flex justify-between">
                    <span>{{ $story->yayin_tarihi->format('d.m.Y') }}</span>
                    <span>#{{ $story->id }}</span>
                </div>
                <h3 class="text-2xl font-display font-bold text-white mb-2 group-hover:text-neon-pink transition duration-300 truncate">{{ $story->baslik }}</h3>
                <p class="text-gray-400 line-clamp-3 text-sm font-light mb-4">{{ Str::limit(strip_tags($story->metin), 120) }}</p>
                <div class="flex flex-wrap gap-2">
                    @if($story->etiketler)
                        @foreach(array_slice($story->etiketler, 0, 3) as $etiket)
                            <span class="text-xs border border-gray-700 px-2 py-1 text-gray-500 uppercase rounded-sm group-hover:border-neon-blue group-hover:text-neon-blue transition">{{ $etiket }}</span>
                        @endforeach
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="mt-12">
        {{ $stories->links() }}
    </div>
</div>
@endsection
