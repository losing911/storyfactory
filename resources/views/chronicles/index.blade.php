@extends('layouts.frontend')

@section('title', 'SİSTEM GÜNLÜKLERİ - NEO-PERA KRONİKLERİ')
@section('meta_description', 'Neo-Pera evreninin yapay zeka tarafından tutulan canlı tarihçesi ve olay kayıtları.')

@section('content')
<div class="pt-32 pb-20 min-h-screen">
    <div class="container mx-auto px-4">
        
        <div class="mb-20 text-center relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none">
                <span class="text-[10rem] font-black text-white font-display">CHRONICLE</span>
            </div>
            <h1 class="text-4xl md:text-6xl font-display font-black text-white mb-6 glitch-effect relative z-10" data-text="SİSTEM_GÜNLÜKLERİ">SİSTEM_GÜNLÜKLERİ</h1>
            <p class="font-mono text-neon-blue text-sm tracking-widest mb-8 max-w-2xl mx-auto leading-relaxed">
                /// CONNECTING TO CORE ARCHIVE...<br>
                Şehrin hafızası silinemez. Burada, Büyük Çöküş'ten bugüne, tüm olayların sentezlenmiş kayıtları yatar.
            </p>
        </div>

        <div class="max-w-5xl mx-auto bg-gray-900/50 border border-gray-800 p-8 md:p-12 hover:border-neon-blue transition duration-500 group relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-neon-blue/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-neon-pink/5 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>

            <div class="relative z-10">
                <h2 class="text-2xl font-display text-white mb-8 flex items-center gap-3 border-b border-gray-800 pb-4">
                    <span class="text-neon-pink text-3xl">///</span> 
                    CANLI AKIŞ ÖZETİ
                </h2>

                @if(view()->exists('lore.partials.history_generated'))
                    <!-- Comic/Book Layout Container -->
                    <div class="prose prose-invert prose-lg max-w-none font-serif leading-loose text-gray-300
                        md:columns-2 md:gap-12 md:rule-neon-blue/30
                        prose-headings:font-display prose-headings:font-black prose-headings:uppercase prose-headings:tracking-widest prose-headings:break-inside-avoid
                        prose-h1:text-6xl prose-h1:text-neon-pink prose-h1:text-glow prose-h1:mb-12 prose-h1:border-b-4 prose-h1:border-neon-pink
                        prose-h2:text-4xl prose-h2:text-neon-blue prose-h2:text-glow prose-h2:bg-gray-900/50 prose-h2:p-4 prose-h2:-ml-4 prose-h2:border-l-8 prose-h2:border-neon-blue prose-h2:mb-8 prose-h2:mt-12
                        prose-h3:text-2xl prose-h3:text-neon-green prose-h3:text-glow prose-h3:mt-8 prose-h3:mb-4 prose-h3:font-bold prose-h3:decoration-neon-green prose-h3:underline prose-h3:decoration-2 prose-h3:underline-offset-4
                        prose-p:text-justify prose-p:mb-6 prose-p:leading-8 prose-p:break-inside-avoid-orphans
                        prose-strong:text-white prose-strong:font-bold prose-strong:bg-neon-pink/20 prose-strong:px-1
                        prose-blockquote:border-l-0 prose-blockquote:border-t-4 prose-blockquote:border-neon-purple prose-blockquote:bg-gray-800 prose-blockquote:p-6 prose-blockquote:my-8 prose-blockquote:shadow-neon-purple prose-blockquote:not-italic prose-blockquote:font-mono prose-blockquote:text-sm
                        first-letter:text-5xl first-letter:font-black first-letter:text-neon-pink first-letter:float-left first-letter:mr-3 first-letter:mt-2">
                        
                        @include('lore.partials.history_generated')
                    </div>
                    
                    <div class="mt-12 pt-8 border-t border-gray-800 flex justify-between items-center text-xs font-mono text-gray-600">
                        <span>VOLUME: 01 // CYCLE: 2075</span>
                        <span>SOURCE: NEURAL_NET_ARCHIVE</span>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-700 mx-auto mb-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        <h3 class="text-xl font-display text-gray-500">VERİ AKIŞI BEKLENİYOR...</h3>
                        <p class="text-gray-600 mt-2 font-mono text-sm">Sistem henüz yeterli veri toplayamadı. Lütfen daha sonra tekrar deneyin.</p>
                        <p class="text-gray-800 mt-4 text-xs">ERR_CODE: EMPTY_DB_RESULT</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
