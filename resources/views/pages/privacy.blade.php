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
            <h2 class="text-2xl font-bold text-neon-pink mb-4">3. Reklamlar ve Üçüncü Taraf Satıcılar</h2>
            <p>Sitemizde reklam yayınlamak için <strong>Google AdSense</strong> ve <strong>Ezoic</strong> kullanıyoruz.</p>
            <ul class="list-disc pl-5 mt-2 space-y-1">
                <li>Google dahil üçüncü taraf satıcılar, kullanıcının web sitemize veya diğer web sitelerine yaptığı önceki ziyaretlere dayalı olarak reklam yayınlamak için çerezleri kullanmaktadır.</li>
                <li>Google'ın reklam çerezlerini kullanması, kendisinin ve iş ortaklarının, kullanıcıların sitemize ve/veya internetteki diğer sitelere yaptıkları ziyaretlere dayalı olarak onlara uygun reklamlar sunmasını sağlar.</li>
                <li>Kullanıcılar, <a href="https://www.google.com/settings/ads" target="_blank" class="text-neon-blue hover:underline">Reklam Ayarları</a> sayfasını ziyaret ederek kişiselleştirilmiş reklamcılığı devre dışı bırakabilirler.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">4. Ezoic Hizmet Ortağı Açıklaması</h2>
            <p class="mb-4">Bu web sitesi, içerik optimizasyonu ve reklam hizmetleri için Ezoic platformunu kullanmaktadır. Ezoic ve iş ortaklarının bilgilerinizi nasıl kullandığı ve sitede kullanılan çerezler hakkında detaylı bilgi için aşağıdaki açıklamayı inceleyiniz:</p>
            
            <!-- Ezoic Privacy Policy Embed -->
            <div class="bg-gray-900 border border-gray-700 p-4 rounded">
                <span id="ezoic-privacy-policy-embed"></span>
            </div>
            
            <p class="mt-4 text-sm text-gray-500">
                Tam gizlilik açıklaması: <a href="http://g.ezoic.net/privacy/anxipunk.icu" target="_blank" class="text-neon-blue hover:underline">g.ezoic.net/privacy/anxipunk.icu</a>
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">5. İletişim</h2>
            <p>Gizlilik politikamızla ilgili sorularınız için <a href="{{ route('contact') }}" class="text-neon-blue hover:underline">İletişim</a> sayfasından bize ulaşabilirsiniz.</p>
        </section>
    </div>
</div>
@endsection
