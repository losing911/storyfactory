@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">{{ isset($story) ? 'Hikayeyi Düzenle' : 'Yeni Hikaye Oluştur' }}</h2>

    <form action="{{ isset($story) ? route('admin.stories.update', $story) : route('admin.stories.store') }}" method="POST" class="space-y-6">
        @csrf
        @if(isset($story)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">Başlık</label>
                <input type="text" name="baslik" value="{{ old('baslik', $story->baslik ?? '') }}" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konu</label>
                <input type="text" name="konu" value="{{ old('konu', $story->konu ?? '') }}" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Metin</label>
            <textarea name="metin" rows="10" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white font-mono">{{ old('metin', $story->metin ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">Görsel URL</label>
                <input type="text" name="gorsel_url" value="{{ old('gorsel_url', $story->gorsel_url ?? '') }}" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Görsel Prompt</label>
                <input type="text" name="gorsel_prompt" value="{{ old('gorsel_prompt', $story->gorsel_prompt ?? '') }}" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium mb-1">Yayın Tarihi</label>
                <input type="datetime-local" name="yayin_tarihi" value="{{ old('yayin_tarihi', isset($story) ? $story->yayin_tarihi->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Durum</label>
                <select name="durum" class="w-full bg-gray-800 border-gray-700 rounded p-2 focus:ring-cyber focus:border-cyber text-white">
                    <option value="draft" {{ (old('durum', $story->durum ?? '') == 'draft') ? 'selected' : '' }}>Taslak</option>
                    <option value="published" {{ (old('durum', $story->durum ?? '') == 'published') ? 'selected' : '' }}>Yayınlandı</option>
                </select>
            </div>
        </div>

        <button type="submit" class="bg-cyber text-black font-bold py-2 px-6 rounded hover:bg-green-400">
            {{ isset($story) ? 'Güncelle' : 'Oluştur' }}
        </button>
    </form>
</div>
@endsection
