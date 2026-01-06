@extends('admin.layout')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Yazarı Düzenle: {{ $author->name }}</h2>
        <a href="{{ route('admin.authors.index') }}" class="text-gray-400 hover:text-white">← Geri</a>
    </div>

    <form action="{{ route('admin.authors.update', $author) }}" method="POST" class="bg-gray-800 p-6 rounded-lg border border-gray-700 space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Name -->
        <div>
            <label class="block text-gray-400 text-sm font-bold mb-2">Yazar İsmi</label>
            <input type="text" name="name" value="{{ $author->name }}" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white focus:border-neon-green focus:outline-none" required>
        </div>

        <!-- Role -->
        <div>
            <label class="block text-gray-400 text-sm font-bold mb-2">Rol / Unvan</label>
            <input type="text" name="role" value="{{ $author->role }}" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white focus:border-neon-green focus:outline-none">
        </div>

        <!-- Avatar URL -->
        <div>
            <label class="block text-gray-400 text-sm font-bold mb-2">Avatar URL (Görsel Adresi)</label>
            <input type="url" name="avatar" value="{{ $author->avatar }}" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white focus:border-neon-green focus:outline-none">
            
            @if($author->avatar)
                <div class="mt-2">
                    <img src="{{ $author->avatar }}" class="w-16 h-16 rounded-full object-cover border border-gray-600">
                </div>
            @endif
        </div>

        <!-- Bio -->
        <div>
            <label class="block text-gray-400 text-sm font-bold mb-2">Biyografi</label>
            <textarea name="bio" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-white focus:border-neon-green focus:outline-none">{{ $author->bio }}</textarea>
        </div>

        <!-- IS AI Checkbox -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_ai" value="1" id="isAi" class="w-5 h-5 bg-gray-900 border-gray-700 rounded focus:ring-neon-green text-neon-green" {{ $author->is_ai ? 'checked' : '' }}>
            <label for="isAi" class="text-gray-300 select-none cursor-pointer">Bu bir Yapay Zeka (AI) Persona'sıdır</label>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded transition shadow-lg mt-4">
            GÜNCELLE
        </button>
    </form>
</div>
@endsection
