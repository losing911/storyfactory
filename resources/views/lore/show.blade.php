@extends('layouts.frontend')

@section('title', $entry->title . ' - DATABASE')
@section('meta_description', Str::limit($entry->description, 150))

@section('content')
<div class="min-h-screen pt-24 pb-20">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Breadcrumb -->
        <a href="{{ route('lore.index') }}" class="inline-flex items-center gap-2 text-neon-green font-mono text-xs mb-8 hover:underline">
            <span><</span>
            <span>BACK_TO_ARCHIVES</span>
        </a>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            
            <!-- Left: Image & Stats -->
            <div class="md:col-span-1">
                <div class="border-2 border-neon-blue/30 p-1 bg-black/50 shadow-[0_0_15px_rgba(0,255,255,0.1)] mb-6">
                    @if($entry->image_url)
                        <img src="{{ Str::startsWith($entry->image_url, 'http') ? $entry->image_url : asset($entry->image_url) }}" class="w-full h-auto grayscale hover:grayscale-0 transition duration-500">
                    @else
                        <div class="w-full aspect-square bg-gray-900 flex items-center justify-center">
                            <span class="font-mono text-gray-600">NO_VISUAL</span>
                        </div>
                    @endif
                </div>

                <!-- Data Block -->
                <div class="bg-gray-900 border border-gray-800 p-4 font-mono text-xs space-y-2">
                    <div class="flex justify-between border-b border-gray-800 pb-1">
                        <span class="text-gray-500">TYPE</span>
                        <span class="text-neon-pink uppercase">{{ $entry->type }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-800 pb-1">
                        <span class="text-gray-500">STATUS</span>
                        <span class="text-neon-green">ACTIVE</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-800 pb-1">
                        <span class="text-gray-500">LAST_SIGHTING</span>
                        <span class="text-white">{{ now()->subDays(rand(1,30))->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>

            <!-- Right: Description -->
            <div class="md:col-span-2">
                <h1 class="text-4xl md:text-5xl font-display font-black text-white mb-6 leading-tight">{{ $entry->title }}</h1>
                
                <div class="prose prose-invert prose-lg text-gray-300 font-sans leading-relaxed border-l-4 border-gray-800 pl-6">
                    <p>{{ $entry->description }}</p>
                </div>
            </div>
        </div>

        <!-- Related Stories -->
        @if($relatedStories->count() > 0)
        <div class="border-t border-gray-800 pt-12">
            <h3 class="font-mono text-neon-purple text-sm mb-6 uppercase tracking-widest">/// RELATED STREAMS</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($relatedStories as $story)
                    <a href="{{ route('story.show', $story) }}" class="block p-4 border border-gray-800 hover:border-gray-600 transition bg-gray-900/30">
                        <h4 class="text-white font-bold text-sm mb-1 truncate">{{ $story->baslik }}</h4>
                        <span class="text-xs text-gray-500 font-mono">{{ $story->created_at->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
