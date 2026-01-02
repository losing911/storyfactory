@extends('layouts.frontend')

@section('title', 'Kütüphane - Digital Archive')

@section('content')
<div class="min-h-screen bg-[#050505] text-gray-300">
    
    <!-- Hero -->
    <div class="relative py-24 text-center border-b border-gray-800">
        <h1 class="text-6xl font-display font-black text-neon-pink text-glow tracking-widest mb-4">KÜTÜPHANE</h1>
        <p class="font-mono text-neon-blue">ARCHIVE_ACCESS_LEVEL_5 // ALL_VOLUMES</p>
    </div>

    <!-- Shelf Grid -->
    <div class="container mx-auto px-4 py-16">
        @if($books->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                @foreach($books as $book)
                    <a href="{{ route('ebooks.show', $book->slug) }}" class="group relative block bg-gray-900 border border-gray-800 hover:border-neon-pink transition-all duration-500 overflow-hidden rounded-lg shadow-2xl hover:shadow-neon-pink/50">
                        
                        <!-- Cover Image -->
                        <div class="h-96 w-full overflow-hidden relative">
                            @if($book->cover_image_url)
                                <img src="{{ asset($book->cover_image_url) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-110 group-hover:grayscale-0 grayscale" alt="{{ $book->title }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-800">
                                    <span class="font-display text-4xl text-gray-700">NO COVER</span>
                                </div>
                            @endif
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90"></div>
                        </div>

                        <!-- Info -->
                        <div class="absolute bottom-0 left-0 w-full p-6">
                            <span class="block text-neon-green font-mono text-xs tracking-widest mb-2">VOLUME {{ str_pad($book->volume_number, 2, '0', STR_PAD_LEFT) }}</span>
                            <h2 class="text-3xl font-display font-bold text-white leading-none mb-2 truncate">{{ $book->title }}</h2>
                            <p class="text-xs font-mono text-gray-500 uppercase">
                                Includes {{ $book->start_story_id }} - {{ $book->end_story_id }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-20 border border-dashed border-gray-800 rounded">
                <p class="font-mono text-xl text-gray-600">NO_VOLUMES_FOUND_IN_ARCHIVE</p>
                <p class="text-sm text-gray-700 mt-2">Sistem henüz 20 hikaye limiti dolmadığı için cilt derlemedi.</p>
            </div>
        @endif
    </div>

</div>
@endsection
