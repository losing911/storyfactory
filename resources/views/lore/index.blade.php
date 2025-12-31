@extends('layouts.frontend')

@section('title', 'NEXUS PROTOKOLÜ - VERİ BANKASI')
@section('meta_description', 'Anxipunk Lore Veritabanına Erişim. Şehirler, Hizipler ve Karakterler hakkında bilgiler.')

@section('content')
<div class="pt-32 pb-20 min-h-screen">
    <div class="container mx-auto px-4">
        
        <div class="mb-20 text-center relative">
            <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none">
                <span class="text-[12rem] font-black text-white font-display">LORE</span>
            </div>
            <h1 class="text-5xl md:text-7xl font-display font-black text-white mb-6 glitch-effect relative z-10" data-text="ARŞİV_ÇEKİRDEĞİ">ARŞİV_ÇEKİRDEĞİ</h1>
            <p class="font-mono text-neon-green text-sm tracking-widest mb-8 max-w-2xl mx-auto leading-relaxed">
                /// NEO-PERA HAFIZA BANKALARINA HOŞGELDİNİZ.<br>
                Burada Büyük Çöküş'ün parça parça tarihi, Şirketler Sendikası'nın yükselişi ve gölgelerde direnmeye cesaret eden azınlığın kayıtları yatar.
            </p>
            
                <!-- Universe Story (Accordion or Section) -->
            <div class="text-left max-w-4xl mx-auto bg-gray-900/50 border border-gray-800 p-8 hover:border-neon-blue transition duration-500 group">
                <h2 class="text-2xl font-display text-white mb-4 flex items-center gap-3">
                    <span class="text-neon-blue">00.</span> 
                    BAŞLANGIÇ HİKAYESİ (ARŞİV)
                </h2>
                <div class="prose prose-invert prose-sm max-w-none text-gray-400 font-sans columns-1 md:columns-2 gap-8 mb-8">
                    <p>
                        <strong>2042: Büyük Sessizlik.</strong> Bir patlamayla değil, bir fısıltıyla başladı—daha doğrusu fısıltının yokluğuyla. Küresel internet altyapısı, sadece "The Hush" (Sessizlik) olarak bilinen bir veri tekilliği olayının ağırlığı altında çöktü. Finansal piyasalar buharlaştı. Uluslar parçalandı.
                    </p>
                    <p>
                        Küllerden <strong>Neo-Pera</strong> doğdu. Eski İstanbul'un harabeleri üzerine inşa edilen bu şehir, teknoloji elitleri için bir sığınak, geri kalanlar içinse bir hapishane oldu. Şehir artık biyoteknoloji ve güvenlik firmalarından oluşan <em>Sendika (The Syndicate)</em> tarafından yönetiliyor. Tek geçerli para birimi: <strong>Temiz Veri</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section: Cities -->
        @if($cities->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-display text-neon-purple mb-8 border-b border-gray-800 pb-2">/// ÇÖKÜNTÜ ALANLARI & ŞEHİRLER</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cities as $entry)
                    @include('lore.card', ['entry' => $entry])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Section: Factions -->
        @if($factions->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-display text-neon-pink mb-8 border-b border-gray-800 pb-2">/// SENDİKALAR & HİZİPLER</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($factions as $entry)
                    @include('lore.card', ['entry' => $entry])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Section: Characters -->
        @if($characters->count() > 0)
        <div class="mb-16">
            <h2 class="text-2xl font-display text-neon-blue mb-8 border-b border-gray-800 pb-2">/// KAÇAK AJANLAR & NPCS</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($characters as $entry)
                    @include('lore.card', ['entry' => $entry])
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
