@extends('admin.layout')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-display text-white">{{ isset($entry) ? 'EDIT: ' . $entry->title : 'NEW LORE ENTRY' }}</h2>
        <a href="{{ route('admin.lore.index') }}" class="text-gray-500 hover:text-white font-mono text-xs"><< BACK_TO_LIST</a>
    </div>

    <form action="{{ isset($entry) ? route('admin.lore.update', $entry) : route('admin.lore.store') }}" method="POST" enctype="multipart/form-data" class="bg-gray-900 border border-gray-800 p-8 rounded-lg space-y-6">
        @csrf
        @if(isset($entry)) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div>
                <label class="block font-mono text-xs text-neon-blue mb-2">TITLE / NAME</label>
                <input type="text" name="title" value="{{ old('title', $entry->title ?? '') }}" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-neon-blue focus:outline-none" required>
            </div>

            <!-- Type -->
            <div>
                <label class="block font-mono text-xs text-neon-pink mb-2">ENTITY TYPE</label>
                <select name="type" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-neon-pink focus:outline-none">
                    @foreach(['city', 'character', 'faction', 'location'] as $type)
                        <option value="{{ $type }}" {{ (old('type', $entry->type ?? '') == $type) ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block font-mono text-xs text-gray-400 mb-2">LORE DESCRIPTION (PUBLIC)</label>
            <textarea name="description" rows="5" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-white focus:outline-none">{{ old('description', $entry->description ?? '') }}</textarea>
        </div>

        <!-- Visual Prompt (AI) -->
        <div class="bg-gray-800/50 p-6 border border-gray-700 rounded relative">
            <span class="absolute top-0 right-0 bg-neon-purple text-black text-[10px] font-bold px-2 py-1">AI CONFIG</span>
            <label class="block font-mono text-xs text-neon-green mb-2">MASTER VISUAL PROMPT (DEFAULT)</label>
            <p class="text-xs text-gray-500 mb-2">Describe the physical appearance rigidly. This is the base look.</p>
            <textarea name="visual_prompt" rows="3" placeholder="e.g. A cyborg with a red mechanical eye..." class="w-full bg-black border border-gray-700 text-neon-green px-4 py-2 focus:border-neon-green focus:outline-none font-mono text-sm mb-4">{{ old('visual_prompt', $entry->visual_prompt ?? '') }}</textarea>

            <h3 class="text-neon-pink font-mono text-xs mb-4 pt-4 border-t border-gray-700">VISUAL VARIATIONS (SITUATIONAL)</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-mono text-[10px] text-gray-400 mb-1">COMBAT-READY VERSION</label>
                    <input type="text" name="variation_combat" value="{{ $entry->visual_variations['combat'] ?? '' }}" placeholder="With drawn katana, fierce expression..." class="w-full bg-black/50 border border-gray-700 text-gray-300 text-sm px-3 py-2 focus:border-neon-pink focus:outline-none">
                </div>
                <div>
                    <label class="block font-mono text-[10px] text-gray-400 mb-1">ACTION POSE (RUNNING/JUMPING)</label>
                    <input type="text" name="variation_action" value="{{ $entry->visual_variations['action'] ?? '' }}" placeholder="Mid-air, dynamic motion blur..." class="w-full bg-black/50 border border-gray-700 text-gray-300 text-sm px-3 py-2 focus:border-neon-pink focus:outline-none">
                </div>
                <div>
                    <label class="block font-mono text-[10px] text-gray-400 mb-1">DRAMATIC SCENE (EMOTIONAL/CINEMATIC)</label>
                    <input type="text" name="variation_dramatic" value="{{ $entry->visual_variations['dramatic'] ?? '' }}" placeholder="Close up, teary eyes, dramatic lighting..." class="w-full bg-black/50 border border-gray-700 text-gray-300 text-sm px-3 py-2 focus:border-neon-pink focus:outline-none">
                </div>
                <div>
                    <label class="block font-mono text-[10px] text-gray-400 mb-1">FORM: HÜCRE-34 UNIFORM</label>
                    <input type="text" name="variation_uniform" value="{{ $entry->visual_variations['uniform'] ?? '' }}" placeholder="Wearing the black tactical vest with 34 logo..." class="w-full bg-black/50 border border-gray-700 text-gray-300 text-sm px-3 py-2 focus:border-neon-pink focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Image Upload -->
        <div>
            <label class="block font-mono text-xs text-gray-400 mb-2">REFERENCE IMAGE (UPLOAD)</label>
            @if(isset($entry) && $entry->image_url)
                <div class="mb-2">
                    <img src="{{ $entry->image_url }}" class="h-32 object-cover border border-gray-600">
                </div>
            @endif
            <input type="file" name="image" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-800 file:text-neon-blue hover:file:bg-gray-700">
        </div>

        <!-- Submit -->
        <div class="pt-4 border-t border-gray-800 text-right">
            <button type="submit" class="bg-neon-green text-black px-8 py-3 font-display font-bold hover:bg-white transition uppercase tracking-widest">
                {{ isset($entry) ? 'UPDATE_DATABASE' : 'INITIATE_ENTRY' }}
            </button>
        </div>
    </form>
</div>
@endsection
