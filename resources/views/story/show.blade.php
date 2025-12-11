@extends('layouts.frontend')

@section('title', $story->meta_baslik ?? $story->baslik)
@section('meta_description', $story->meta_aciklama ?? Str::limit(strip_tags($story->metin), 160))

@section('meta_tags')
    <meta property="og:title" content="{{ $story->meta_baslik ?? $story->baslik }}" />
    <meta property="og:description" content="{{ $story->meta_aciklama ?? Str::limit(strip_tags($story->metin), 160) }}" />
    <meta property="og:image" content="{{ asset($story->gorsel_url) }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@anxipunkart" />
    <meta name="twitter:title" content="{{ $story->meta_baslik ?? $story->baslik }}" />
    <meta name="twitter:description" content="{{ $story->meta_aciklama ?? Str::limit(strip_tags($story->metin), 160) }}" />
    <meta name="twitter:image" content="{{ asset($story->gorsel_url) }}" />
@endsection

@section('content')
<article class="min-h-screen">
    <!-- Header -->
    <header class="relative h-[70vh] flex items-end pb-20 bg-black">
        <div class="absolute inset-0">
             @if($story->gorsel_url)
                <img src="{{ $story->gorsel_url }}" alt="{{ $story->baslik }}" class="w-full h-full object-cover opacity-50">
            @endif
             <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/60 to-transparent"></div>
        </div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center w-full">
            <div class="inline-block border border-neon-green/30 px-3 py-1 mb-6 text-neon-green font-mono text-sm tracking-widest bg-black/50 backdrop-blur-sm">
                {{ $story->yayin_tarihi->format('d F Y') }} // {{ $story->konu }}
            </div>
            <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-8 leading-tight text-glow filter drop-shadow-lg">{{ $story->baslik }}</h1>
            
            <div class="flex justify-center gap-4 text-sm font-mono text-gray-400">
                @if($story->etiketler)
                    @foreach($story->etiketler as $etiket)
                        <span class="text-neon-blue">#{{ $etiket }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    </header>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-4 py-12 relative">
        <!-- Sidebar Line -->
        <div class="absolute left-4 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-neon-pink to-transparent hidden lg:block opacity-50"></div>

        <div class="prose prose-invert prose-lg max-w-none text-gray-300 font-sans leading-relaxed">
            {!! $story->metin !!}
        </div>

        <!-- Share Protocol -->
        <div class="mt-16 pt-8 border-t border-gray-800">
            <h4 class="font-display text-neon-blue mb-6 text-sm tracking-widest uppercase">Initiate Share Protocol</h4>
            <div class="flex flex-wrap gap-4">
                 <a href="https://twitter.com/intent/tweet?text={{ urlencode($story->baslik) }}&url={{ urlencode(route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-blue text-gray-300 hover:text-neon-blue px-6 py-3 transition duration-300 group">
                    <span class="font-mono text-xs">X_COM</span>
                 </a>
                 <a href="https://wa.me/?text={{ urlencode($story->baslik . ' ' . route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-green text-gray-300 hover:text-neon-green px-6 py-3 transition duration-300 group">
                    <span class="font-mono text-xs">WHATSAPP_NET</span>
                 </a>
                 <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('story.show', $story)) }}" target="_blank" class="flex items-center gap-2 bg-gray-900 border border-gray-700 hover:border-neon-pink text-gray-300 hover:text-neon-pink px-6 py-3 transition duration-300 group">
                    <span class="font-mono text-xs">FACEBOOK_LNK</span>
                 </a>
            </div>
        </div>
    </div>
</article>
@endsection
