@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-black text-gray-300 py-20 px-4">
    <div class="max-w-md mx-auto bg-gray-900 border border-gray-800 p-8 shadow-[0_0_20px_rgba(0,0,0,0.5)]">
        <h2 class="text-3xl font-display text-white mb-8 border-b border-gray-700 pb-4">/// SYSTEM_USER_PROFILE</h2>

        @if(session('success'))
            <div class="mb-6 p-4 bg-neon-green/10 border border-neon-green/50 text-neon-green font-mono text-sm">
                >> SUCCESS: RECORD UPDATED
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-neon-pink/10 border border-neon-pink/50 text-neon-pink font-mono text-sm">
                >> ERROR: INVALID INPUT DETECTED
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block font-mono text-xs text-neon-blue mb-2">USER_ID (NAME)</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-neon-purple focus:outline-none transition">
            </div>

            <div>
                <label class="block font-mono text-xs text-neon-blue mb-2">COMM_LINK (EMAIL)</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-neon-purple focus:outline-none transition">
            </div>

            <div>
                <label class="block font-mono text-xs text-neon-pink mb-2">NEW_CIPHER (PASSWORD) [LEAVE BLANK TO KEEP]</label>
                <input type="password" name="password" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-neon-pink focus:outline-none transition">
            </div>

            <div>
                <label class="block font-mono text-xs text-neon-pink mb-2">VERIFY_CIPHER</label>
                <input type="password" name="password_confirmation" class="w-full bg-black border border-gray-700 text-white px-4 py-2 focus:border-neon-pink focus:outline-none transition">
            </div>

            <div class="pt-4 flex justify-between items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-mono text-gray-500 hover:text-white transition"><< RETURN_ROOT</a>
                <button type="submit" class="bg-gray-800 border border-gray-600 text-white px-6 py-2 font-display hover:bg-neon-purple hover:border-neon-purple hover:text-black transition duration-300">
                    UPDATE_RECORD
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
