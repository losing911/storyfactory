@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-[#050505] p-6 text-white font-mono">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-display text-white mb-2 flex items-center gap-2">
            <span class="text-neon-pink">///</span> E-BOOK COMPILER PROTOCOL
        </h1>
        <p class="text-gray-500 mb-8">System: Ready to compile Volume {{ $volume }} from Story ID {{ $startId }}+</p>

        @if($eligibleStories < 20)
            <div class="bg-red-900/30 border border-red-500/50 p-4 rounded mb-6">
                <h3 class="text-red-400 font-bold mb-2">WARNING: INSUFFICIENT DATA</h3>
                <p>Only {{ $eligibleStories }} stories available. Protocol requires 20.</p>
                <div class="mt-4">
                     <button onclick="overrideProtocol()" class="bg-red-800 text-white px-4 py-2 hover:bg-red-700 transition">FORCE COMPILE (DANGEROUS)</button>
                </div>
            </div>
        @endif

        <div id="control-panel" class="bg-gray-900 border border-gray-800 p-6 rounded mb-8">
            <button id="start-btn" onclick="startCompilation()" class="w-full bg-neon-blue text-black font-bold py-4 hover:bg-white transition flex justify-center items-center gap-2">
                <span>INITIALIZE COMPILATION</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </button>
        </div>

        <!-- Terminal Output -->
        <div class="bg-black border border-gray-800 rounded p-4 h-96 overflow-y-auto font-mono text-xs" id="terminal">
            <div class="text-green-500">$ system_check... OK</div>
            <div class="text-gray-500">Waiting for user input...</div>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-4 bg-gray-900 h-2 rounded overflow-hidden">
            <div id="progress-bar" class="bg-neon-green h-full w-0 transition-all duration-300"></div>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const terminal = document.getElementById('terminal');
    const progressBar = document.getElementById('progress-bar');
    let fullHtml = "";
    let suggestedTitle = "";

    function log(msg, type = 'info') {
        const div = document.createElement('div');
        div.className = type === 'error' ? 'text-red-500' : (type === 'success' ? 'text-neon-green' : 'text-gray-400');
        div.innerText = `> ${msg}`;
        terminal.appendChild(div);
        terminal.scrollTop = terminal.scrollHeight;
    }

    async function startCompilation() {
        document.getElementById('start-btn').disabled = true;
        document.getElementById('start-btn').classList.add('opacity-50', 'cursor-not-allowed');
        log('Initializing Compilation Sequence...', 'info');

        try {
            // Step 1: Init
            const initRes = await fetch("{{ route('admin.ebooks.init') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({})
            });
            const initData = await initRes.json();

            if(initData.status === 'error') {
                throw new Error(initData.message);
            }

            log(`Target: Volume ${initData.volume}`, 'info');
            log(`Found ${initData.total_parts} Chunks to process.`, 'info');

            // Step 2: Loop Chunks
            for (const chunk of initData.chunks) {
                log(`Processing Chunk ${chunk.part} / ${initData.total_parts}... (AI + Image)`, 'info');
                progressBar.style.width = `${(chunk.part / initData.total_parts) * 90}%`;

                const chunkRes = await fetch("{{ route('admin.ebooks.chunk') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({
                        story_ids: chunk.story_ids,
                        volume: initData.volume,
                        part: chunk.part,
                        total_parts: initData.total_parts
                    })
                });
                
                const chunkText = await chunkRes.text();
                let chunkResult;
                try {
                    chunkResult = JSON.parse(chunkText);
                } catch (e) {
                    console.error("Non-JSON Response", chunkText);
                    throw new Error("Server Error (HTML Response): " + chunkText.substring(0, 50));
                }

                if(chunkResult.status === 'error') throw new Error(chunkResult.message);
                
                fullHtml += chunkResult.html;
                if(chunkResult.extracted_title) suggestedTitle = chunkResult.extracted_title;
                
                log(`Chunk ${chunk.part} Completed.`, 'success');
            }

            // Step 3: Finalize
            log('Finalizing E-Book (Creating Cover & saving)...', 'info');
            progressBar.style.width = '95%';

            const finalRes = await fetch("{{ route('admin.ebooks.finalize') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({
                    volume: initData.volume,
                    full_html: fullHtml,
                    start_id: initData.start_id,
                    end_id: initData.end_id,
                    suggested_title: suggestedTitle
                })
            });

            const finalResult = await finalRes.json();
            if(finalResult.status === 'error') throw new Error(finalResult.message);

            progressBar.style.width = '100%';
            log('COMPILATION COMPLETE! Redirecting...', 'success');
            
            setTimeout(() => {
                window.location.href = finalResult.redirect_url;
            }, 1000);

        } catch (err) {
            log(`CRITICAL ERROR: ${err.message}`, 'error');
            document.getElementById('start-btn').disabled = false;
            document.getElementById('start-btn').classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
    
    function overrideProtocol() {
        alert('Override not implemented in UI yet (Safe Mode). Please add stories.');
    }
</script>
@endsection
