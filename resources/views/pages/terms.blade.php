@extends('layouts.frontend')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl text-gray-300">
    <h1 class="text-4xl font-bold text-neon-blue mb-8 glitch-text" data-text="Kullanım Şartları">Kullanım Şartları</h1>

    <div class="glass-panel p-8 space-y-6">
        <p class="text-sm text-gray-500">Son Güncelleme: {{ date('d.m.Y') }}</p>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">1. Kabul</h2>
            <p>Anxipunk.icu web sitesini ziyaret ederek bu kullanım şartlarını kabul etmiş sayılırsınız.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">2. İçerik ve Telif Hakkı</h2>
            <p>Bu web sitesindeki hikayeler ve görseller yapay zeka destekli olarak üretilmektedir. İçerikler kişisel kullanım içindir. İzinsiz ticari kullanımı yasaktır.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">3. Sorumluluk Reddi</h2>
            <p>Sitedeki içerikler tamamen kurgusaldır. Gerçek kişi, kurum veya olaylarla benzerlikler tamamen tesadüftür. Sitedeki bilgilerin doğruluğu garanti edilmez.</p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-neon-pink mb-4">4. Değişiklikler</h2>
            <p>Bu şartları dilediğimiz zaman değiştirme hakkımız saklıdır.</p>
        </section>
    </div>
</div>
@endsection
