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

        <!-- Book Details (No Reading Here) -->
        <article class="text-center">
             <div class="mb-12 border border-neon-blue/30 p-8 rounded bg-gray-900/50 backdrop-blur-md">
                 <h2 class="text-2xl font-mono text-neon-green mb-4">RESTRICTED ACCESS</h2>
                 <p class="text-gray-400 mb-6">This volume contains high-density narrative data. Direct neural interface (web reading) is disabled for safety protocol.</p>
                 
                 <a href="{{ route('ebooks.download', $ebook->slug) }}" class="inline-block bg-neon-blue text-black font-bold font-display text-xl px-12 py-4 rounded hover:bg-white hover:scale-105 transition duration-300 shadow-[0_0_20px_rgba(0,255,255,0.4)]">
                     DOWNLOAD E-BOOK (.PDF)
                 </a>
                 <div class="mt-4 text-xs text-gray-500 font-mono">
                    <p>Professional Edition // A4 Format // Optimized for e-Readers</p>
                 </div>
             </div>
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
