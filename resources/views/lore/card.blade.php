<div class="group relative bg-gray-900 border border-gray-800 hover:border-neon-pink transition-all duration-300 overflow-hidden">
    @if($entry->image_url)
        <div class="h-48 overflow-hidden">
            <img src="{{ $entry->image_url }}" alt="{{ $entry->title }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500 opacity-60 group-hover:opacity-100 mix-blend-luminosity hover:mix-blend-normal">
        </div>
    @else
        <div class="h-48 bg-gray-800 flex items-center justify-center border-b border-gray-700">
            <span class="font-mono text-neon-green text-xs tracking-widest">[NO_VISUAL_DATA]</span>
        </div>
    @endif
    
    <div class="p-6">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-xl font-display text-white group-hover:text-neon-pink transition">{{ $entry->title }}</h3>
            <span class="text-xs font-mono text-gray-500 py-1 px-2 border border-gray-700 rounded uppercase">{{ $entry->type }}</span>
        </div>
        
        <p class="text-gray-400 text-sm font-sans mb-4 line-clamp-3 leading-relaxed">
            {{ Str::limit($entry->description, 120) }}
        </p>

        <a href="{{ route('lore.show', $entry->slug) }}" class="inline-flex items-center text-neon-blue text-xs font-mono uppercase tracking-widest hover:text-white transition">
            <span>> ACCESS_FILE</span>
        </a>
    </div>
    
    <!-- Corner Decoration -->
    <div class="absolute top-0 right-0 w-2 h-2 bg-neon-pink opacity-0 group-hover:opacity-100 transition"></div>
</div>
