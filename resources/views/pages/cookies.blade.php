@extends('layouts.frontend')

@section('title', 'Çerez Politikası')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-4xl text-gray-300">
    <h1 class="text-4xl font-bold text-neon-blue mb-8 glitch-effect" data-text="Çerez Politikası">Çerez Politikası</h1>

    <div class="glass-panel p-8 space-y-6 bg-black/50 border border-gray-800">
        <p class="text-sm text-gray-500">Son Güncelleme: {{ date('d.m.Y') }}</p>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">1. Çerez Nedir?</h2>
            <p>Çerezler (cookies), web sitemizi ziyaret ettiğinizde tarayıcınız aracılığıyla cihazınıza veya ağ sunucusuna depolanan küçük metin dosyalarıdır. Bu dosyalar, sitemizin daha verimli çalışmasını ve kullanıcı deneyiminizin iyileştirilmesini sağlar.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">2. Hangi Çerezleri Kullanıyoruz?</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong>Zorunlu Çerezler:</strong> Sitenin düzgün çalışması için gereklidir (örneğin oturum açma işlemleri).</li>
                <li><strong>Analitik Çerezler:</strong> Google Analytics gibi araçlarla site trafiğini analiz etmek ve performansı artırmak için kullanılır.</li>
                <li><strong>Reklam Çerezleri:</strong> İlgi alanlarınıza uygun reklamlar göstermek amacıyla Google AdSense ve iş ortakları tarafından kullanılabilir.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">3. Çerezleri Nasıl Kontrol Edebilirsiniz?</h2>
            <p>Tarayıcı ayarlarınızdan çerezleri dilediğiniz zaman silebilir veya engelleyebilirsiniz. Ancak, çerezleri devre dışı bırakmak sitemizin bazı özelliklerinin çalışmamasına neden olabilir.</p>
            <p>Google reklam tercihlerinizi <a href="https://myadcenter.google.com/" target="_blank" class="text-neon-blue hover:underline">Google Reklam Merkezi</a> üzerinden yönetebilirsiniz.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">4. İletişim</h2>
            <p>Çerez politikamızla ilgili sorularınız için <a href="{{ route('contact') }}" class="text-neon-blue hover:underline">İletişim</a> sayfasından bize ulaşabilirsiniz.</p>
        </section>
    </div>
</div>
@endsection
