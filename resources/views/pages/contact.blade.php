@extends('layouts.frontend')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl text-gray-300">
    <h1 class="text-4xl font-bold text-neon-blue mb-8 glitch-text" data-text="İletişim">İletişim</h1>

    <div class="glass-panel p-8 space-y-6">
        <p>Bize ulaşmak, hikaye önerilerinde bulunmak veya telif hakkı bildirimleri için aşağıdaki e-posta adresini kullanabilirsiniz.</p>

        <div class="bg-black/50 p-6 rounded border border-neon-blue/30 text-center">
            <h3 class="text-xl text-neon-green mb-2">E-Posta</h3>
            <a href="mailto:contact@anxipunk.icu" class="text-2xl font-mono hover:text-white transition">contact@anxipunk.icu</a>
        </div>

        <div class="mt-8">
            <h3 class="text-xl font-bold text-neon-pink mb-4">Sosyal Medya</h3>
            <div class="flex gap-4">
                <a href="#" class="text-gray-400 hover:text-neon-blue transition">Twitter / X</a>
                <a href="#" class="text-gray-400 hover:text-neon-pink transition">Instagram</a>
            </div>
        </div>
    </div>
</div>
@endsection
