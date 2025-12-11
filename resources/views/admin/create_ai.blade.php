@extends('admin.layout')

@section('content')
<div class="max-w-2xl mx-auto text-center pt-10">
    <div class="mb-8">
        <div class="inline-block p-4 rounded-full bg-gray-800 mb-4 border border-purple-500 shadow-[0_0_20px_rgba(168,85,247,0.5)]">
            <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <h2 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-600">AI Story Generator</h2>
        <p class="text-gray-400 mt-2">Gemini API ile Cyberpunk içerik üret.</p>
    </div>

    <form action="{{ route('admin.ai.generate') }}" method="POST" class="bg-gray-800 p-8 rounded-xl border border-gray-700 shadow-2xl">
        @csrf
        <div class="mb-6 text-left">
            <label class="block text-sm font-medium mb-2 text-gray-300">Konu (Opsiyonel)</label>
            <input type="text" name="topic" placeholder="Örn: Yağmurlu bir gece, kayıp bir android..." class="w-full bg-gray-900 border-gray-600 rounded p-3 focus:ring-purple-500 focus:border-purple-500 text-white placeholder-gray-600">
            <p class="text-xs text-gray-500 mt-1">Boş bırakılırsa AI rastgele bir konu seçecektir.</p>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 rounded-lg transform transition hover:scale-105">
            Otomatik Hikaye Üret & Yayınla
        </button>
    </form>
    
    <div class="mt-8 text-sm text-gray-500">
        <p>İşlem yaklaşık 30-60 saniye sürebilir.</p>
    </div>
</div>
@endsection
