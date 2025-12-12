<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anxipunk Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cyber: '#0f0',
                        darkbg: '#0a0a0a'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-gray-200 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-black border-r border-gray-800 p-4">
            <h1 class="text-2xl font-bold text-cyber mb-8 tracking-widest">ANXIPUNK<span class="text-white text-xs block">ADMIN TERMINAL</span></h1>
            <nav class="space-y-4">
                <a href="{{ route('admin.stories.index') }}" class="block py-2 px-4 rounded hover:bg-gray-800 {{ request()->routeIs('admin.stories.index') ? 'bg-gray-800 text-cyber' : '' }}">Hikayeler</a>
                <a href="{{ route('admin.ai.create') }}" class="block py-2 px-4 rounded hover:bg-gray-800 {{ request()->routeIs('admin.ai.create') ? 'bg-gray-800 text-cyber' : '' }}">AI Üretim (Manuel)</a>
                <a href="{{ route('admin.stories.create') }}" class="block py-2 px-4 rounded hover:bg-gray-800 {{ request()->routeIs('admin.stories.create') ? 'bg-gray-800 text-cyber' : '' }}">Yeni Ekle</a>
                <a href="{{ route('admin.lore.index') }}" class="block py-2 px-4 rounded hover:bg-gray-800 {{ request()->routeIs('admin.lore.*') ? 'bg-gray-800 text-cyber' : '' }}">Lore Veritabanı</a>
                <a href="{{ route('admin.profile.edit') }}" class="block py-2 px-4 rounded hover:bg-gray-800 {{ request()->routeIs('admin.profile.edit') ? 'bg-gray-800 text-cyber' : '' }}">Profil Ayarları</a>
                <a href="/" target="_blank" class="block py-2 px-4 rounded hover:bg-gray-800 text-gray-500">Siteyi Görüntüle</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8">
            @if(session('success'))
                <div class="bg-green-900 text-green-100 p-4 rounded mb-6 border border-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-900 text-red-100 p-4 rounded mb-6 border border-red-700">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
