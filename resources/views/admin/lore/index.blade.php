@extends('admin.layout')

@section('content')
<div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-display text-white">Lore Database Management</h2>
    <a href="{{ route('admin.lore.create') }}" class="bg-neon-blue text-black px-6 py-2 font-bold font-display hover:bg-white transition">
        + NEW ENTRY
    </a>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-black text-gray-400 font-mono text-sm uppercase">
            <tr>
                <th class="p-4">Image</th>
                <th class="p-4">Title</th>
                <th class="p-4">Type</th>
                <th class="p-4">Visual Prompt (AI)</th>
                <th class="p-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @foreach($entries as $entry)
            <tr class="hover:bg-gray-800/50 transition">
                <td class="p-4">
                    @if($entry->image_url)
                        <img src="{{ $entry->image_url }}" class="w-12 h-12 object-cover rounded border border-gray-700">
                    @else
                        <div class="w-12 h-12 bg-gray-800 rounded flex items-center justify-center text-xs text-gray-600">N/A</div>
                    @endif
                </td>
                <td class="p-4 font-bold text-white">{{ $entry->title }}</td>
                <td class="p-4 text-neon-pink uppercase text-xs font-mono">{{ $entry->type }}</td>
                <td class="p-4 text-gray-500 text-xs italic">{{ Str::limit($entry->visual_prompt, 50) }}</td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.lore.edit', $entry) }}" class="text-neon-blue hover:text-white text-xs font-mono">[EDIT]</a>
                    <form action="{{ route('admin.lore.destroy', $entry) }}" method="POST" class="inline-block" onsubmit="return confirm('DELETE ENTRY?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-mono">[DELETE]</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">
        {{ $entries->links() }}
    </div>
</div>
@endsection
