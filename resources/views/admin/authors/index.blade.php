@extends('admin.layout')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold">Yazar Yönetimi</h2>
    <a href="{{ route('admin.authors.create') }}" class="bg-neon-green hover:bg-green-600 text-black font-bold px-4 py-2 rounded shadow-lg">
        + Yeni Yazar
    </a>
</div>

<div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700">
    <table class="w-full text-left text-gray-400">
        <thead class="bg-gray-900 uppercase text-gray-500 text-sm">
            <tr>
                <th class="p-4">Av</th>
                <th class="p-4">İsim</th>
                <th class="p-4">Rol</th>
                <th class="p-4">Tür</th>
                <th class="p-4">Hikaye Sayısı</th>
                <th class="p-4 text-right">İşlemler</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @foreach($authors as $author)
            <tr class="hover:bg-gray-750">
                <td class="p-4">
                    <img src="{{ $author->avatar }}" class="w-10 h-10 rounded-full object-cover border border-gray-600">
                </td>
                <td class="p-4 font-medium text-white">{{ $author->name }}</td>
                <td class="p-4 text-neon-blue text-xs uppercase tracking-wider">{{ $author->role }}</td>
                <td class="p-4">
                    @if($author->is_ai)
                        <span class="bg-gray-900 text-neon-green border border-neon-green/30 px-2 py-1 rounded text-xs font-mono">AI</span>
                    @else
                        <span class="bg-gray-900 text-blue-400 border border-blue-400/30 px-2 py-1 rounded text-xs font-mono">HUMAN</span>
                    @endif
                </td>
                <td class="p-4">{{ $author->stories_count }}</td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.authors.edit', $author) }}" class="text-blue-400 hover:text-blue-300">Düzenle</a>
                    <form action="{{ route('admin.authors.destroy', $author) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Bu yazarı silmek istediğine emin misin?')">Sil</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $authors->links() }}
</div>
@endsection
