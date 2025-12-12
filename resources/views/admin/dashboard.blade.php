@extends('admin.layout')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Card 1 -->
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-lg">
        <h3 class="text-neon-blue font-mono text-sm uppercase tracking-widest mb-2">Total Stories</h3>
        <p class="text-4xl font-display text-white">{{ $stats['total_stories'] }}</p>
    </div>

    <!-- Stat Card 2 -->
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-lg">
        <h3 class="text-neon-green font-mono text-sm uppercase tracking-widest mb-2">Published</h3>
        <p class="text-4xl font-display text-white">{{ $stats['published_stories'] }}</p>
    </div>

    <!-- Stat Card 3 -->
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-lg">
        <h3 class="text-neon-pink font-mono text-sm uppercase tracking-widest mb-2">Generated Images</h3>
        <p class="text-4xl font-display text-white">{{ $stats['total_images'] }}</p>
    </div>

    <!-- Stat Card 4 -->
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-lg">
        <h3 class="text-neon-purple font-mono text-sm uppercase tracking-widest mb-2">Total Comments</h3>
        <p class="text-4xl font-display text-white">{{ $stats['total_comments'] }}</p>
    </div>

    <!-- Stat Card 5 -->
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-lg">
        <h3 class="text-yellow-400 font-mono text-sm uppercase tracking-widest mb-2">Active Votes</h3>
        <p class="text-4xl font-display text-white">{{ $stats['active_votes'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Quick Actions -->
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-lg">
        <h2 class="text-2xl font-display text-white mb-6">Quick Actions</h2>
        <div class="space-y-4">
            <a href="{{ route('admin.ai.create') }}" class="block w-full bg-neon-blue/10 border border-neon-blue text-neon-blue hover:bg-neon-blue hover:text-black py-4 px-6 text-center font-display uppercase tracking-widest transition duration-300">
                Trigger New AI Story
            </a>
            <a href="{{ route('admin.stories.index') }}" class="block w-full bg-gray-800 border border-gray-700 text-gray-300 hover:border-white hover:text-white py-4 px-6 text-center font-display uppercase tracking-widest transition duration-300">
                Manage Stories
            </a>
        </div>
    </div>

    <!-- Latest Activity -->
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-lg">
        <h2 class="text-2xl font-display text-white mb-6">Latest Generation</h2>
        @if($stats['last_story'])
            <div class="flex gap-4">
                <div class="w-1/3">
                    <img src="{{ $stats['last_story']->gorsel_url }}" class="w-full h-auto rounded border border-gray-700">
                </div>
                <div class="w-2/3">
                    <h3 class="text-xl text-white font-bold mb-2">{{ $stats['last_story']->baslik }}</h3>
                    <p class="text-xs text-neon-green font-mono mb-2">{{ $stats['last_story']->created_at->diffForHumans() }}</p>
                    <a href="{{ route('story.show', $stats['last_story']) }}" target="_blank" class="text-neon-pink hover:text-white text-sm underline decoration-neon-pink">View Live ></a>
                </div>
            </div>
        @else
            <p class="text-gray-500">No stories generated yet.</p>
        @endif
    </div>
</div>
@endsection
