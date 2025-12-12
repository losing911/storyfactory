<a href="{{ route('lore.show', $entry->slug) }}" class="group block bg-gray-900 border border-gray-800 hover:border-white transition-all duration-300 relative overflow-hidden">
    <!-- ID Badge -->
    <div class="absolute top-2 right-2 bg-black/80 text-[10px] font-mono text-gray-400 px-2 py-1 border border-gray-800 z-10 group-hover:text-white group-hover:border-neon-green">
        ID: {{ substr(md5($entry->id), 0, 6) }}
    </div>

    @if($entry->image_url)
        <div class="h-48 overflow-hidden relative">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent z-10"></div>
            <img src="{{ Str::startsWith($entry->image_url, 'http') ? $entry->image_url : asset($entry->image_url) }}" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition duration-500">
        </div>
    @else
        <div class="h-48 bg-gray-800 flex items-center justify-center relative overflow-hidden">
             <!-- Glitch Noise -->
             <div class="absolute inset-0 opacity-10 bg-[url('https://media.giphy.com/media/oEI9uBYSzLpBK/giphy.gif')] bg-cover mix-blend-overlay"></div>
             <span class="font-mono text-gray-600 text-xs">NO_IMAGE_DATA</span>
        </div>
    @endif

    <div class="p-4 relative z-20">
        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-neon-cyan transition-colors">{{ $entry->title }}</h3>
        <p class="text-xs text-gray-400 font-mono line-clamp-2 leading-relaxed">
            {{ Str::limit($entry->description, 100) }}
        </p>
    </div>
</a>
