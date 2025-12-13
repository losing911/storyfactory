@extends('layouts.frontend')

@section('title', 'Visual Archive')
@section('meta_description', 'Archive of all generated cyberpunk visuals, locations, and characters.')

@section('content')
<div class="container mx-auto px-4 py-12">
    <!-- Header -->
    <div class="mb-12 text-center relative">
        <h1 class="text-5xl md:text-7xl font-display font-black text-transparent bg-clip-text bg-gradient-to-r from-neon-blue via-white to-neon-pink animate-pulse tracking-tighter">
            VISUAL_ARCHIVE
        </h1>
        <p class="mt-4 text-neon-blue font-mono text-sm uppercase tracking-[0.2em] glitch-effect" data-text="System.load_resources(ALL)">
            System.load_resources(ALL)
        </p>
    </div>

    <!-- Gallery Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($paginatedImages as $image)
        <div class="group relative aspect-video bg-gray-900 rounded-xl overflow-hidden border border-gray-800 hover:border-neon-pink transition duration-500 shadow-lg hover:shadow-neon-pink">
            
            <!-- Image -->
            <img src="{{ $image->url }}" alt="{{ $image->title }}" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700 ease-in-out filter brightness-75 group-hover:brightness-100">
            
            <!-- Overlay Content -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-end p-6">
                <span class="text-xs font-mono text-neon-green mb-1 uppercase tracking-widest">{{ $image->type }}</span>
                <h3 class="text-xl font-display font-bold text-white leading-tight mb-2">{{ Str::limit($image->title, 40) }}</h3>
                
                <a href="{{ $image->link }}" class="inline-flex items-center text-sm font-mono text-neon-blue hover:text-white transition">
                    <span>ACCESS_FILE</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <!-- Glitch Overlay (Decorative) -->
            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-20 pointer-events-none mix-blend-overlay"></div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-16 flex justify-center">
        {{ $paginatedImages->links() }}
    </div>
</div>

<style>
    /* Pagination Customization */
    .pagination { @apply flex space-x-2; }
    .page-item { @apply inline-block; }
    .page-link { 
        @apply px-4 py-2 bg-gray-900 border border-gray-700 text-gray-400 font-mono text-sm hover:text-neon-blue hover:border-neon-blue transition rounded;
    }
    .page-item.active .page-link {
        @apply bg-gray-800 text-neon-pink border-neon-pink;
    }
</style>
@endsection
