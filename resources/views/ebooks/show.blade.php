@extends('layouts.frontend')

@section('title', $ebook->title)

@section('content')
<div class="min-h-screen bg-[#0a0a0a] text-gray-300 font-serif selection:bg-neon-pink selection:text-white pb-20">
    
    <!-- Cover Header -->
    <div class="relative h-[60vh] w-full overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-transparent to-transparent z-10"></div>
        <img src="{{ asset($ebook->cover_image_url) }}" class="w-full h-full object-cover opacity-60 blur-sm scale-110" alt="Cover Art">
        
        <div class="absolute bottom-0 left-0 w-full z-20 container mx-auto px-6 pb-12">
            <h1 class="font-display font-black text-6xl md:text-8xl text-neon-blue text-glow mb-4">{{ $ebook->title }}</h1>
            <div class="flex items-center space-x-4 font-mono text-neon-pink tracking-widest uppercase">
                <span>Volume {{ str_pad($ebook->volume_number, 2, '0', STR_PAD_LEFT) }}</span>
                <span>//</span>
                <span>Collected Works</span>
            </div>
        </div>
    </div>

    <!-- Reader Interface -->
    <div class="container mx-auto px-4 md:px-0 max-w-4xl mt-12">
        
        <!-- Controls -->
        <div class="flex justify-end mb-8 space-x-4 text-sm font-mono sticky top-24 z-30 mix-blend-difference">
            <button onclick="document.getElementById('reader').classList.toggle('text-lg'); document.getElementById('reader').classList.toggle('text-xl')" class="hover:text-neon-green">[ Aa+ ]</button>
            <button onclick="toggleLightMode()" class="hover:text-neon-yellow">[ ☀/☾ ]</button>
        </div>

        <!-- Book Content -->
        <article id="reader" class="prose prose-invert prose-xl max-w-none 
            prose-p:leading-loose prose-p:text-justify prose-p:mb-8 text-gray-300
            prose-headings:font-display prose-headings:text-neon-pink prose-headings:mt-16 prose-headings:mb-8
            prose-h1:text-center prose-h1:text-6xl prose-h1:hidden
            prose-h2:text-4xl prose-h2:border-b-2 prose-h2:border-neon-blue prose-h2:pb-4
            drop-cap-first">
            
            {!! $ebook->content !!}
        </article>

        <!-- Footer / Navigation -->
        <div class="mt-24 border-t border-gray-800 pt-12 flex justify-between items-center font-mono text-sm text-gray-500">
            <a href="{{ route('ebooks.index') }}" class="hover:text-white">← Library</a>
            <span>END OF VOLUME</span>
        </div>
    </div>

</div>

<script>
function toggleLightMode() {
    // Cyberpunk 'Paper' Mode?
    const reader = document.getElementById('reader');
    if (reader.classList.contains('text-gray-300')) {
        // Switch to 'Paper' (Sepia/Light)
        reader.classList.remove('text-gray-300', 'prose-invert');
        reader.classList.add('text-gray-900', 'bg-[#d4d4d4]', 'p-12', 'rounded');
        document.body.style.backgroundColor = '#d4d4d4';
    } else {
        // Revert
        reader.classList.add('text-gray-300', 'prose-invert');
        reader.classList.remove('text-gray-900', 'bg-[#d4d4d4]', 'p-12', 'rounded');
        document.body.style.backgroundColor = '#050505';
    }
}
</script>
@endsection
