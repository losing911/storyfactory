@extends('admin.layout')

@section('content')
<div class="max-w-2xl mx-auto text-center pt-10">
    <div class="mb-8">
        <div class="inline-block p-4 rounded-full bg-gray-800 mb-4 border border-purple-500 shadow-[0_0_20px_rgba(168,85,247,0.5)]">
            <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <h2 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-600">AI Story Generator</h2>
        <p class="text-gray-400 mt-2">Gemini API ile Cyberpunk içerik üret.</p>
    </div>

    <form action="{{ route('admin.ai.generate') }}" method="POST" class="bg-gray-800 p-8 rounded-xl border border-gray-700 shadow-2xl">
        @csrf
        <div class="mb-6 text-left">
            <label class="block text-sm font-medium mb-2 text-gray-300">Konu (Opsiyonel)</label>
            <input type="text" name="topic" placeholder="Örn: Yağmurlu bir gece, kayıp bir android..." class="w-full bg-gray-900 border-gray-600 rounded p-3 focus:ring-purple-500 focus:border-purple-500 text-white placeholder-gray-600">
            <p class="text-xs text-gray-500 mt-1">Boş bırakılırsa AI rastgele bir konu seçecektir.</p>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 rounded-lg transform transition hover:scale-105">
            Otomatik Hikaye Üret & Yayınla
        </button>
    </form>
    
    <div class="mt-8 text-sm text-gray-500">
        <p>İşlem yaklaşık 30-60 saniye sürebilir.</p>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black/90 z-50 hidden flex flex-col items-center justify-center font-mono">
        <div class="text-neon-pink text-4xl mb-4 glitch-effect" data-text="NEURAL LINK ACTIVE">NEURAL LINK ACTIVE</div>
        <div class="w-2/3 md:w-1/3 bg-gray-900 border border-green-500 p-4 h-48 overflow-y-auto shadow-[0_0_20px_rgba(0,255,0,0.2)]">
            <div id="terminalLog" class="text-green-500 text-xs space-y-1">
                <span class="typing-effect">> Initializing connection...</span>
            </div>
        </div>
        <div class="mt-4 flex space-x-2">
            <div class="w-3 h-3 bg-neon-blue animate-pulse"></div>
            <div class="w-3 h-3 bg-neon-blue animate-pulse delay-75"></div>
            <div class="w-3 h-3 bg-neon-blue animate-pulse delay-150"></div>
        </div>
    </div>
</div>

<script>
    const form = document.querySelector('form');
    
    // Auto-Start Logic
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('auto_start')) {
        const topic = urlParams.get('topic');
        if(topic) document.querySelector('input[name="topic"]').value = topic;
        
        // Wait 500ms for UI to settle then trigger
        setTimeout(() => {
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }, 500);
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault(); // Stop normal submission
        
        // UI Elements
        const overlay = document.getElementById('loadingOverlay');
        const log = document.getElementById('terminalLog');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        function appendLog(msg, color = 'text-green-500') {
            const div = document.createElement('div');
            div.className = color;
            div.textContent = "> " + msg;
            log.appendChild(div);
            log.scrollTop = log.scrollHeight;
        }

        try {
            const formData = new FormData(this);
            const csrfToken = document.querySelector('input[name="_token"]').value;
            const headers = { 
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            // STEP 1: Generate Story Text
            appendLog("Phase 1: Neural Text Synthesis...", "text-neon-blue");
            
            const storyRes = await fetch("{{ route('admin.ai.step.story') }}", {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ topic: formData.get('topic') })
            });
            
            const storyJson = await storyRes.json();
            if(storyJson.status !== 'success') throw new Error(storyJson.message);
            
            const storyData = storyJson.data;
            const slug = storyJson.slug;
            const dateFolder = storyJson.dateFolder;
            const visual_constraints = storyData.meta_visual_prompts; // Capture constraints
            
            appendLog("Text Structure Generated for: " + storyData.baslik);
            appendLog("Scenes Identified: " + storyData.scenes.length);
            if(visual_constraints) appendLog("Visual Constraints Active: " + visual_constraints.substring(0, 30) + "...", "text-neon-green");

            // STEP 2: Generate Images Chunk-by-Chunk
            appendLog("Phase 2: Visual Rendering (Seq by Seq)...", "text-neon-pink");
            
            const images = {}; // Store local URLs

            for (let i = 0; i < storyData.scenes.length; i++) {
                const scene = storyData.scenes[i];
                appendLog(`Rendering Scene ${i+1}/${storyData.scenes.length}...`);
                
                const imgRes = await fetch("{{ route('admin.ai.step.image') }}", {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        prompt: scene.img_prompt,
                        slug: slug,
                        index: i,
                        dateFolder: dateFolder,
                        visual_constraints: visual_constraints // Pass to image gen
                    })
                });
                
                const imgJson = await imgRes.json();
                if(imgJson.status === 'success') {
                    images[i] = imgJson.localUrl;
                    appendLog(`Scene ${i+1} [OK]`, "text-green-400");
                } else {
                    appendLog(`Scene ${i+1} [FAIL] - Using Placeholder`, "text-red-500");
                    images[i] = imgJson.localUrl; // Fallback
                }
            }

            // STEP 3: Store Final Story
            appendLog("Phase 3: Archiving to Core Memory...", "text-purple-500");
            
            const finalPayload = {
                ...storyData,
                slug: slug,
                images: images
            };

            const storeRes = await fetch("{{ route('admin.ai.step.store') }}", {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(finalPayload)
            });

            const storeJson = await storeRes.json();
            
            if(storeJson.status === 'success') {
                appendLog("Complete! Redirecting...", "text-white");
                setTimeout(() => {
                    window.location.href = storeJson.redirect;
                }, 1000);
            } else {
                throw new Error(storeJson.message);
            }

        } catch (error) {
            appendLog("CRITICAL ERROR: " + error.message, "text-red-600 font-bold");
            // Do not hide overlay so user can read error
        }
    });
</script>
@endsection
