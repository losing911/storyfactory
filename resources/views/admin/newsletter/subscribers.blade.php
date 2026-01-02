@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6 text-white font-mono">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
            <h1 class="text-2xl font-display text-white">
                SUBSCRIBER_DB <span class="text-neon-blue">/// LIST</span>
            </h1>
            <a href="{{ route('admin.newsletter.index') }}" class="text-gray-500 hover:text-white transition text-xs flex items-center gap-2">
                &larr; BACK_TO_CAMPAIGNS
            </a>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-xs uppercase text-gray-500 border-b border-gray-800">
                        <th class="p-4 w-10">#</th>
                        <th class="p-4">EMAIL</th>
                        <th class="p-4">IP_LOG</th>
                        <th class="p-4">JOINED</th>
                        <th class="p-4 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($subscribers as $sub)
                        <tr class="border-b border-gray-800 hover:bg-gray-800 transition">
                            <td class="p-4 text-gray-600">{{ $sub->id }}</td>
                            <td class="p-4 text-white font-mono">{{ $sub->email }}</td>
                            <td class="p-4 text-gray-500 text-xs">{{ $sub->ip_address }}</td>
                            <td class="p-4 text-gray-500 text-xs">{{ $sub->created_at->format('Y-m-d') }}</td>
                            <td class="p-4 text-right">
                                <form action="{{ route('admin.newsletter.subscribers.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Kalc olarak siliniyor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-white px-2 transition text-xs font-bold">[X]</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-600 italic">
                                NO_SUBSCRIBERS_YET
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 px-4">
            {{ $subscribers->links() }}
        </div>
    </div>
</div>
@endsection
