@extends('layouts.frontend')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl text-gray-300">
    <h1 class="text-4xl font-bold text-neon-blue mb-8 glitch-text" data-text="Gizlilik Politikası">Gizlilik Politikası</h1>

    <div class="glass-panel p-8 space-y-6">
        <p class="text-sm text-gray-500">Son Güncelleme: {{ date('d.m.Y') }}</p>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">1. Giriş</h2>
            <p>Anxipunk ("biz", "sitemiz"), gizliliğinize önem verir. Bu Gizlilik Politikası, web sitemizi ziyaret ettiğinizde bilgilerinizin nasıl toplandığını, kullanıldığını ve korunduğunu açıklar.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">2. Toplanan Bilgiler</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong>Günlük Kayıtları (Log Files):</strong> IP adresi, tarayıcı türü, ziyaret süresi gibi standart sunucu kayıtları.</li>
                <li><strong>Çerezler (Cookies):</strong> Tercihlerinizi hatırlamak ve site trafiğini analiz etmek için çerezler kullanabiliriz.</li>
                <li><strong>Analitik:</strong> Google Analytics gibi üçüncü taraf hizmetler aracılığıyla anonim kullanım verileri toplanabilir.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">3. Reklamlar (Google AdSense)</h2>
            <p>Sitemizde reklam yayınlamak için Google AdSense kullanıyoruz. Google, kullanıcıların sitemizi ve internetteki diğer siteleri ziyaretlerine dayalı reklamlar sunmak için çerezleri (örneğin DART çerezleri) kullanabilir.</p>
            <p>Kullanıcılar, Google reklam ve içerik ağı gizlilik politikasını ziyaret ederek DART çerezinin kullanımını devre dışı bırakabilirler.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">4. İletişim</h2>
            <p>Gizlilik politikamızla ilgili sorularınız için <a href="{{ route('contact') }}" class="text-neon-blue hover:underline">İletişim</a> sayfasından bize ulaşabilirsiniz.</p>
        </section>
    </div>
</div>
@endsection
