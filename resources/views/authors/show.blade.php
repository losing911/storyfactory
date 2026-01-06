@extends('layouts.frontend')

@section('title', $author->name . ' - Netrunner Profile')

@section('content')
<div class="min-h-screen bg-[#050505] text-gray-300">
    
    <!-- Profile Header -->
    <div class="relative py-20 border-b border-gray-800 bg-black/50 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
             <!-- Abstract tech background -->
             <div class="absolute inset-0 bg-[url('https://res.cloudinary.com/demo/image/upload/v1688755734/grid_fs8fw9.png')] bg-cover opacity-10"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 flex flex-col md:flex-row items-center gap-10">
            <!-- Avatar -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-neon-green to-neon-blue rounded-full blur opacity-50 group-hover:opacity-100 transition duration-500"></div>
                <img src="{{ $author->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed='.$author->slug }}" class="relative w-40 h-40 rounded-full border-2 border-black object-cover bg-gray-900" alt="{{ $author->name }}">
            </div>

            <!-- Info -->
            <div class="text-center md:text-left">
                <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-2">{{ $author->name }}</h1>
                <p class="text-neon-green font-mono tracking-widest uppercase mb-4">{{ $author->role ?? 'UNKNOWN_CLASS' }}</p>
                
                <div class="max-w-2xl text-gray-400 font-sans italic leading-relaxed">
                    "{{ $author->bio ?? 'No bio data available in the neural cloud.' }}"
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap justify-center md:justify-start gap-6 mt-6 font-mono text-sm text-gray-500">
                    <div class="flex flex-col">
                        <span class="text-white text-xl">{{ $author->stories->count() }}</span>
                        <span class="text-xs uppercase tracking-widest">Stories</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-white text-xl">{{ $author->created_at->format('Y') }}</span>
                        <span class="text-xs uppercase tracking-widest">Since</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-white text-xl">{{ rand(90, 99) }}%</span>
                        <span class="text-xs uppercase tracking-widest">Sync Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stories Grid -->
    <div class="container mx-auto px-4 py-16">
        <h2 class="text-2xl font-display text-white mb-8 border-l-4 border-neon-blue pl-4">/// ARCHIVED_MEMORIES</h2>
        
        @if($author->stories->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($author->stories as $story)
                    <a href="{{ route('story.show', $story) }}" class="group block bg-gray-900 border border-gray-800 hover:border-neon-green transition duration-300 rounded overflow-hidden">
                        <div class="relative h-48 overflow-hidden">
                            @if($story->gorsel_url)
                                <img src="{{ $story->gorsel_url }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700 opacity-70 group-hover:opacity-100">
                            @else
                                <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                                    <span class="font-mono text-xs text-gray-600">NO_DATA</span>
                                </div>
                            @endif
                            <!-- Date Tag -->
                            <div class="absolute top-2 right-2 bg-black/80 backdrop-blur text-neon-green text-xs font-mono px-2 py-1 border border-gray-700">
                                {{ $story->yayin_tarihi->format('d.m.Y') }}
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-display text-white mb-2 truncate group-hover:text-neon-blue transition">{{ $story->baslik }}</h3>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-4">{{ $story->social_ozet ?? Str::limit(strip_tags($story->metin), 80) }}</p>
                            <div class="flex justify-between items-center text-xs font-mono text-gray-600">
                                <span>READ_TIME: 2m</span>
                                <span class="group-hover:text-neon-green transition">ACCESS -></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-20 border border-dashed border-gray-800 rounded">
                <p class="font-mono text-xl text-gray-600">DATA_CORRUPTION: NO STORIES FOUND</p>
            </div>
        @endif
    </div>

</div>
@endsection
