@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6 text-white font-mono">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
            <h1 class="text-2xl font-display text-white">
                READING_TRANSMISSION <span class="text-neon-purple">#{{ $message->id }}</span>
            </h1>
            <a href="{{ route('admin.inbox.index') }}" class="text-gray-500 hover:text-white transition text-xs flex items-center gap-2">
                &larr; RETURN_TO_LIST
            </a>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b border-gray-800 pb-6">
                <div>
                    <span class="block text-gray-500 text-xs uppercase tracking-widest mb-1">FROM</span>
                    <div class="text-lg text-neon-blue font-bold">{{ $message->name }}</div>
                    <div class="text-sm text-gray-400">&lt;{{ $message->email }}&gt;</div>
                </div>
                <div class="md:text-right">
                    <span class="block text-gray-500 text-xs uppercase tracking-widest mb-1">RECEIVED</span>
                    <div class="text-white">{{ $message->created_at->format('d.m.Y H:i') }}</div>
                    <div class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</div>
                </div>
            </div>

            <div class="mb-8">
                <span class="block text-gray-500 text-xs uppercase tracking-widest mb-2">SUBJECT</span>
                <h2 class="text-xl font-display text-white">{{ $message->subject }}</h2>
            </div>

            <div class="mb-8">
                 <span class="block text-gray-500 text-xs uppercase tracking-widest mb-2">MESSAGE_BODY</span>
                 <div class="bg-black/50 p-6 rounded border border-gray-800 text-gray-300 whitespace-pre-wrap leading-relaxed">
{{ $message->message }}
                 </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-800">
                <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="bg-neon-blue text-black px-6 py-2 font-bold hover:bg-white transition text-sm">
                    REPLY
                </a>
                
                <form action="{{ route('admin.inbox.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Siliniyor?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-900/30 hover:bg-red-600 text-red-500 hover:text-white px-6 py-2 font-bold border border-red-900/50 transition text-sm">
                        DELETE
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
