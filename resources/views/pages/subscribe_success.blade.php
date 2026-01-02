@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-black flex items-center justify-center p-6 relative overflow-hidden">
    <!-- Matrix Rain Background Effect (Optional/Static for now) -->
    <div class="absolute inset-0 bg-[url('https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExbm95bm95bm95bm95bm95bm95bm95bm95bm95bm95bm95bm95bm95bm95/Code/giphy.gif')] opacity-10 bg-cover bg-center pointer-events-none"></div>
    
    <div class="max-w-2xl text-center relative z-10 border border-neon-green/30 bg-black/80 backdrop-blur p-12 shadow-[0_0_50px_rgba(0,255,0,0.2)]">
        <div class="inline-block bg-neon-green text-black font-bold font-mono px-4 py-1 mb-8 text-xl animate-pulse">
            CONNECTION_ESTABLISHED
        </div>
        
        <h1 class="text-4xl md:text-6xl font-display text-white mb-6 glitch-text" data-text="DİRENİŞE HOŞ GELDİNİZ">
            DİRENİŞE<br><span class="text-neon-green">HOŞ GELDİNİZ</span>
        </h1>
        
        <p class="text-gray-400 font-mono text-lg mb-8 leading-relaxed">
            Nöral bağlantınız (neural link) doğrulandı. Şirket güvenlik duvarını (corporate firewall) başarıyla aştınız.<br>
            Şifreli veri paketlerinin kısa süre içinde gelen kutunuza ulaşmasını bekleyin.
        </p>
        
        <div class="flex justify-center gap-4">
            <a href="{{ route('home') }}" class="bg-gray-800 hover:bg-white hover:text-black text-white font-bold px-8 py-3 transition font-mono border border-gray-600">
                RETURN_TO_BASE
            </a>
            <a href="{{ route('lore.index') }}" class="bg-neon-green text-black font-bold px-8 py-3 hover:bg-white transition font-mono shadow-[0_0_20px_rgba(0,255,0,0.4)]">
                BROWSE_ARCHIVES
            </a>
        </div>
    </div>
</div>
@endsection
