@extends('layouts.frontend')

@section('title', 'Sistem Hakkında')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-black relative overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 bg-[url('https://source.unsplash.com/1600x900/?circuit,cyberpunk')] bg-cover opacity-20 filter grayscale blur-sm"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black via-transparent to-black"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 text-center py-20">
        <h1 class="text-6xl md:text-8xl font-display font-black text-white mb-8 glitch-effect" data-text="SİSTEM ÇEKİRDEĞİ">SİSTEM ÇEKİRDEĞİ</h1>
        
        <div class="prose prose-invert prose-lg mx-auto mb-12 border-l-4 border-neon-blue pl-6 text-left">
            <h3 class="text-neon-pink font-mono uppercase tracking-widest">Misyon</h3>
            <p class="text-gray-300 font-sans text-xl leading-relaxed">
                Anxipunk.Art, İstanbul'un (Neo-Pera) distopik geleceğini belgeleyen kolektif bir dijital arşivdir. 
                Siberuzayın derinliklerinden gelen sinyalleri yakalar; neon ışıklarını, yağmurlu sokakları ve silikon ruhları hikayeleştirir.
            </p>
            <p class="text-gray-400 font-mono text-sm mt-4">
                > PROTOKOL: HİKAYE_ARŞİVİ_V2<br>
                > KAYNAK: NEO-PERA_VERİ_AKIŞI<br>
                > DURUM: ÇEVRİMİÇİ
             </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
            <div class="bg-gray-900/50 p-6 border border-gray-800 hover:border-neon-green transition duration-500 group">
                <i class="text-4xl mb-4 block text-neogreen">📠</i>
                <h4 class="text-white font-display uppercase tracking-widest mb-2 group-hover:text-neon-green">Siber Arşiv</h4>
                <p class="text-gray-500 text-sm">Her hikaye, şehrin farklı bir köşesinden toplanan verilerin işlenmesiyle oluşturulan birer suç kaydıdır.</p>
            </div>
            <div class="bg-gray-900/50 p-6 border border-gray-800 hover:border-neon-purple transition duration-500 group">
                <i class="text-4xl mb-4 block text-neon-purple">📸</i>
                <h4 class="text-white font-display uppercase tracking-widest mb-2 group-hover:text-neon-purple">Görsel Kayıtlar</h4>
                <p class="text-gray-500 text-sm">Sitedeki görseller, güvenlik kameraları ve siber-göz implantlarından alınan anlık görüntülerdir.</p>
            </div>
        </div>
        <div class="mt-20 border-t border-gray-800 pt-12 text-left">
            <h3 class="text-2xl font-bold text-gray-500 mb-6 font-mono">/// KODUN_ARKASI (Manifesto)</h3>
            <div class="text-gray-400 space-y-4 font-sans text-sm leading-relaxed max-w-2xl">
                <p>
                    <strong>Anxipunk</strong>, teknolojinin insan doğası üzerindeki etkilerini inceleyen bir bilim kurgu projesidir. 
                    Burada okuduğunuz her satır, izlediğiniz her kare; olası bir geleceğin karamsar ama bir o kadar da estetik yansımasıdır.
                </p>
                <p>
                    Amacımız, dijital sentez yoluyla "Cyberpunk" hikaye anlatıcılığının sınırlarını keşfetmektir. 
                    Herhangi bir sorunuz veya işbirliği fikriniz varsa, lütfen <a href="{{ route('contact') }}" class="text-neon-blue hover:underline">İletişim</a> sayfamızı ziyaret edin.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
