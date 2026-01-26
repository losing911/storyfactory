@extends('admin.layout')

@section('title', 'SEO Tools')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8">
        <h1 class="text-3xl font-display font-bold text-white mb-8 flex items-center gap-3">
            <svg class="w-8 h-8 text-neon-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            SEO Tools
        </h1>

        @if(session('success'))
            <div class="bg-green-900/50 border border-green-500 text-green-200 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Sitemap Section -->
        <div class="bg-gray-900 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-display text-neon-blue">Sitemap Yönetimi</h2>
                <span class="text-xs font-mono text-gray-500">sitemap.xml</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Sitemap Status -->
                <div class="bg-gray-800 p-4 rounded border border-gray-700">
                    <div class="text-sm text-gray-400 mb-2">Sitemap Durumu</div>
                    @if(file_exists(public_path('sitemap.xml')))
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                            <span class="text-green-400 font-mono text-sm">Aktif</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            Son Güncelleme: {{ date('d.m.Y H:i', filemtime(public_path('sitemap.xml'))) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Boyut: {{ round(filesize(public_path('sitemap.xml')) / 1024, 2) }} KB
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                            <span class="text-red-400 font-mono text-sm">Bulunamadı</span>
                        </div>
                    @endif
                </div>

                <!-- URL Count -->
                <div class="bg-gray-800 p-4 rounded border border-gray-700">
                    <div class="text-sm text-gray-400 mb-2">Toplam URL</div>
                    <div class="text-2xl font-bold text-white">
                        {{ \App\Models\Story::where('durum', 'published')->count() + 10 }}
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        {{ \App\Models\Story::where('durum', 'published')->count() }} hikaye + 10 statik sayfa
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <!-- Generate Sitemap Button -->
                <form action="{{ route('admin.seo.generate-sitemap') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-neon-green text-black px-6 py-3 rounded font-bold hover:bg-white transition duration-300 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sitemap Oluştur
                    </button>
                </form>

                @if(file_exists(public_path('sitemap.xml')))
                <!-- View Sitemap -->
                <a href="{{ asset('sitemap.xml') }}" target="_blank" class="bg-gray-700 text-white px-6 py-3 rounded font-mono text-sm hover:bg-gray-600 transition duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Görüntüle
                </a>

                <!-- Download Sitemap -->
                <a href="{{ asset('sitemap.xml') }}" download class="bg-gray-700 text-white px-6 py-3 rounded font-mono text-sm hover:bg-gray-600 transition duration-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    İndir
                </a>
                @endif
            </div>
        </div>

        <!-- Google Search Console Section -->
        <div class="bg-gray-900 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-display text-neon-pink">Google Search Console</h2>
                <a href="https://search.google.com/search-console" target="_blank" class="text-xs text-gray-500 hover:text-neon-pink transition">
                    Google'da Aç →
                </a>
            </div>

            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-neon-pink rounded-full flex items-center justify-center text-black font-bold">1</div>
                    <div class="flex-1">
                        <h3 class="text-white font-bold mb-2">Sitemap URL'ini Kopyala</h3>
                        <div class="bg-gray-800 p-3 rounded border border-gray-700 flex items-center justify-between">
                            <code class="text-neon-green text-sm">{{ url('/sitemap.xml') }}</code>
                            <button onclick="copyToClipboard('{{ url('/sitemap.xml') }}')" class="bg-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-600 transition">
                                Kopyala
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-neon-pink rounded-full flex items-center justify-center text-black font-bold">2</div>
                    <div class="flex-1">
                        <h3 class="text-white font-bold mb-2">Google Search Console'a Git</h3>
                        <a href="https://search.google.com/search-console" target="_blank" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm">
                            Search Console Aç →
                        </a>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-neon-pink rounded-full flex items-center justify-center text-black font-bold">3</div>
                    <div class="flex-1">
                        <h3 class="text-white font-bold mb-2">Sitemap Submit Et</h3>
                        <ol class="text-sm text-gray-400 space-y-2 list-decimal list-inside">
                            <li>Sol menüden "Sitemaps" sekmesine tıkla</li>
                            <li>"Yeni site haritası ekle" kutusuna URL'i yapıştır</li>
                            <li>"GÖNDER" butonuna tıkla</li>
                            <li>Durum: "Başarılı" olarak görünmeli</li>
                        </ol>
                    </div>
                </div>

                <!-- Auto Submit (Future Feature) -->
                <div class="flex gap-4 opacity-50">
                    <div class="flex-shrink-0 w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center text-gray-500 font-bold">4</div>
                    <div class="flex-1">
                        <h3 class="text-gray-500 font-bold mb-2">Otomatik Submit (Yakında)</h3>
                        <p class="text-xs text-gray-600">Google Search Console API ile otomatik sitemap gönderimi gelecek güncellemede eklenecek.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Robots.txt Section -->
        <div class="bg-gray-900 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-display text-neon-purple">Robots.txt</h2>
                <span class="text-xs font-mono text-gray-500">robots.txt</span>
            </div>

            <div class="bg-gray-800 p-4 rounded border border-gray-700 mb-4">
                <pre class="text-xs text-gray-300 font-mono overflow-x-auto">{{ file_exists(public_path('robots.txt')) ? file_get_contents(public_path('robots.txt')) : 'robots.txt bulunamadı' }}</pre>
            </div>

            <div class="flex gap-3">
                <a href="{{ asset('robots.txt') }}" target="_blank" class="bg-gray-700 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 transition">
                    Görüntüle
                </a>
                <a href="{{ route('admin.stories.index') }}" class="bg-gray-700 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 transition">
                    ← Hikayelere Dön
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Sitemap URL kopyalandı!');
    });
}
</script>
@endsection
