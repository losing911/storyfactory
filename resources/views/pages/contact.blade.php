@extends('layouts.frontend')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl text-gray-300">
    <h1 class="text-4xl font-bold text-neon-blue mb-8 glitch-text" data-text="İletişim">İletişim</h1>

    <div class="grid md:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="glass-panel p-8">
            <h2 class="text-2xl font-display text-white mb-6">/// veri_İLETİMİ</h2>
            
            @if(session('success'))
                <div class="bg-green-900/50 border border-green-500 text-green-300 p-4 mb-6 font-mono text-sm">
                    > DURUM: BAŞARILI<br>
                    > MESAJ: {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">KİMLİK (İSİM)</label>
                    <input type="text" name="name" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">İLETİŞİM_KANALI (E-POSTA)</label>
                    <input type="email" name="email" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">KONU_BAŞLIĞI</label>
                    <input type="text" name="subject" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-neon-blue font-mono text-xs mb-2">VERİ_PAKETİ (MESAJINIZ)</label>
                    <textarea name="message" rows="5" required class="w-full bg-black/50 border border-gray-700 text-white p-3 focus:border-neon-pink focus:outline-none transition"></textarea>
                </div>
                <button type="submit" class="w-full bg-neon-blue text-black font-bold font-display py-3 hover:bg-white transition duration-300">
                    İLETİMİ_BAŞLAT
                </button>
            </form>
        </div>

        <!-- Info Panel -->
        <div class="space-y-8">
            <div class="glass-panel p-8 border-l-4 border-neon-purple">
                <h3 class="text-xl font-display text-white mb-4">/// KRİPTOLU_KANALLAR</h3>
                <p class="text-gray-400 mb-6 text-sm leading-relaxed">
                    Sürekli dinlemedeyiz. Yeni bir hikaye fikriniz, hata raporunuz veya direnişe katılma niyetiniz varsa kanallarımız açık.
                </p>
                
                <div class="space-y-4">
                    <a href="mailto:contact@anxipunk.icu" class="flex items-center gap-3 text-neon-green hover:underline">
                        <span>✉</span> contact@anxipunk.icu
                    </a>
                    <a href="https://x.com/AnxlPunk" target="_blank" class="flex items-center gap-3 text-white hover:text-neon-blue transition">
                        <span>✖</span> @AnxlPunk (Resmi Kanal)
                    </a>
                </div>
            </div>

            <div class="bg-black border border-dashed border-gray-800 p-6 text-xs font-mono text-gray-500">
                > NOT: Tüm iletişim uçtan uca şifrelidir.<br>
                > Kritik şireket verilerini paylaşmaktan kaçının.<br>
                > Yanıt süresi: 24-48 döngü.
            </div>
        </div>
    </div>
</div>
@endsection
