@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6 text-white font-mono">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
            <h1 class="text-3xl font-display text-white border-l-4 border-neon-purple pl-4">
                INBOX <span class="text-gray-500 text-sm align-middle ml-2">/// ENCRYPTED_CHANNELS</span>
            </h1>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-white transition text-xs flex items-center gap-2">
                &larr; BACK_TO_COMMAND
            </a>
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
                        <th class="p-4 w-10">#</th>
                        <th class="p-4">SENDER</th>
                        <th class="p-4">SUBJECT</th>
                        <th class="p-4">DATE</th>
                        <th class="p-4 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($messages as $msg)
                        <tr class="border-b border-gray-800 hover:bg-gray-800 transition group {{ $msg->is_read ? 'opacity-60' : 'font-bold text-white' }}">
                            <td class="p-4 text-gray-600">{{ $msg->id }}</td>
                            <td class="p-4">
                                <span class="block text-neon-blue">{{ $msg->name }}</span>
                                <span class="text-xs text-gray-500">{{ $msg->email }}</span>
                            </td>
                            <td class="p-4 text-gray-300 group-hover:text-neon-pink transition">
                                {{ $msg->subject }}
                                @if(!$msg->is_read)
                                    <span class="ml-2 bg-neon-purple text-black text-[10px] px-1 rounded">NEW</span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-500 text-xs">
                                {{ $msg->created_at->diffForHumans() }}
                            </td>
                            <td class="p-4 text-right flex justify-end gap-2">
                                <a href="{{ route('admin.inbox.show', $msg->id) }}" class="bg-gray-700 hover:bg-white hover:text-black text-white px-3 py-1 text-xs border border-gray-600 transition">READ</a>
                                
                                <form action="{{ route('admin.inbox.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Siliniyor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-900/30 hover:bg-red-600 text-red-500 hover:text-white px-3 py-1 text-xs border border-red-900/50 transition">DEL</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-600 italic">
                                NO_TRANSMISSIONS_DETECTED
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $messages->links() }}
        </div>
    </div>
</div>
@endsection
