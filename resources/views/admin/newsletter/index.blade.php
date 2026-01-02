@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6 text-white font-mono">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
            <h1 class="text-3xl font-display text-white border-l-4 border-neon-pink pl-4">
                NEWSLETTER_OPS <span class="text-gray-500 text-sm align-middle ml-2">/// BROADCAST_SYSTEM</span>
            </h1>
            <div class="flex gap-4">
                <a href="{{ route('admin.newsletter.subscribers') }}" class="text-gray-400 hover:text-white transition text-xs flex items-center gap-2 border border-gray-700 px-4 py-2 hover:bg-gray-800">
                    MANAGE_SUBSCRIBERS
                </a>
                <a href="{{ route('admin.newsletter.create') }}" class="bg-neon-pink text-black font-bold px-4 py-2 hover:bg-white transition text-xs flex items-center gap-2">
                    + NEW_CAMPAIGN
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-neon-green/10 border border-neon-green text-neon-green p-4 mb-6 text-sm">
                > {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-900 border border-gray-800 rounded overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-xs uppercase text-gray-500 border-b border-gray-800">
                        <th class="p-4">CAMPAIGN</th>
                        <th class="p-4">STATUS</th>
                        <th class="p-4">SENT / TOTAL</th>
                        <th class="p-4">DATE</th>
                        <th class="p-4 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($campaigns as $campaign)
                        <tr class="border-b border-gray-800 hover:bg-gray-800 transition">
                            <td class="p-4">
                                <span class="block text-white font-bold">{{ $campaign->subject }}</span>
                                <span class="text-xs text-gray-500">ID: #{{ $campaign->id }}</span>
                            </td>
                            <td class="p-4">
                                @if($campaign->status == 'sent')
                                    <span class="text-neon-green text-[10px] border border-neon-green px-2 py-0.5 rounded">SENT</span>
                                @elseif($campaign->status == 'sending')
                                    <span class="text-neon-blue text-[10px] border border-neon-blue px-2 py-0.5 rounded animate-pulse">SENDING...</span>
                                @else
                                    <span class="text-gray-500 text-[10px] border border-gray-600 px-2 py-0.5 rounded">DRAFT</span>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-gray-400">
                                {{ $campaign->sent_count }} / {{ $campaign->total_recipients }}
                            </td>
                            <td class="p-4 text-gray-500 text-xs">
                                {{ $campaign->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="p-4 text-right flex justify-end gap-2">
                                @if($campaign->status == 'draft')
                                    <form action="{{ route('newsletter.send', $campaign->id) }}" method="POST" onsubmit="return confirm('Tüm abonelere gönderilecek. Emin misin?');">
                                        @csrf
                                        <button type="submit" class="bg-neon-blue text-black px-3 py-1 text-xs font-bold hover:bg-white transition">
                                            SEND NOW
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('newsletter.destroy', $campaign->id) }}" method="POST" onsubmit="return confirm('Siliniyor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-white px-3 py-1 text-xs transition">DEL</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-600 italic">
                                NO_CAMPAIGNS_FOUND
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection
