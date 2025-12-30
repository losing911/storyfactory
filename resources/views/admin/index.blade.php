@extends('admin.layout')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold">Hikaye Veritabanı</h2>
    <a href="{{ route('admin.ai.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow-lg glow">
        ⚡ AI Hikaye Üret
    </a>
</div>

<div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700">
    <table class="w-full text-left text-gray-400">
        <thead class="bg-gray-900 uppercase text-gray-500 text-sm">
            <tr>
                <th class="p-4">ID</th>
                <th class="p-4">Başlık</th>
                <th class="p-4">Durum</th>
                <th class="p-4">Yayın Tarihi</th>
                <th class="p-4 text-right">İşlemler</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @foreach($stories as $story)
            <tr class="hover:bg-gray-750">
                <td class="p-4">{{ $story->id }}</td>
                <td class="p-4 font-medium text-white">
                    {{ $story->baslik }}
                    <a href="{{ route('story.show', $story) }}" target="_blank" class="text-xs text-gray-500 ml-2 hover:text-white">👁️ Önizle</a>
                </td>
                <td class="p-4">
                    @php
                        $statusColors = [
                            'published' => 'bg-green-900 text-green-300',
                            'pending_visuals' => 'bg-yellow-900 text-yellow-300',
                            'draft' => 'bg-blue-900 text-blue-300',
                            'taslak' => 'bg-blue-900 text-blue-300'
                        ];
                        $colorClass = $statusColors[$story->durum] ?? 'bg-gray-700 text-gray-300';
                    @endphp
                    <span class="px-2 py-1 rounded text-xs {{ $colorClass }}">
                        {{ $story->durum }}
                    </span>
                </td>
                <td class="p-4">{{ $story->yayin_tarihi->format('d.m.Y H:i') }}</td>
                <td class="p-4 text-right space-x-2">
                    @if($story->durum !== 'published')
                        <form action="{{ route('admin.stories.publish', $story) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-400 hover:text-green-300 font-bold border border-green-900 px-2 py-1 rounded bg-green-900/20 hover:bg-green-900/40 transition">YAYINLA</button>
                        </form>
                    @endif

                    <a href="{{ route('admin.stories.edit', $story) }}" class="text-blue-400 hover:text-blue-300">Düzenle</a>
                    <form action="{{ route('admin.stories.destroy', $story) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Silmek istediğine emin misin?')">Sil</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $stories->links() }}
</div>
@endsection
