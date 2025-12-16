import os
import json
import time
import requests
import websocket # pip install websocket-client
import urllib.request
import urllib.parse
from dotenv import load_dotenv

# Load Local Environment
load_dotenv()

# Configuration
SERVER_URL = os.getenv("SERVER_URL", "http://anxipunk.icu/api") # Remote Server
SERVER_TOKEN = os.getenv("SERVER_TOKEN", "anxipunk_secret_worker_key_2025")
COMFY_URL = "127.0.0.1:8000"
CLIENT_ID = "anxipunk_worker_1"

import random
import uuid
import base64

# API: Check for Jobs
def fetch_pending_task():
    try:
        headers = {"Authorization": f"Bearer {SERVER_TOKEN}", "Accept": "application/json"}
        resp = requests.get(f"{SERVER_URL}/jobs/pending", headers=headers, timeout=10)
        
        if resp.status_code == 200:
            job = resp.json()
            if job:
                print(f"New Job Found: {job['id']} ({job['type']})")
                return job
        elif resp.status_code == 401:
            print("Auth Failed! Check SERVER_TOKEN.")
        
        return None
    except Exception as e:
        print(f"Server check failed: {e}")
        return None

# API: Process and Upload
def process_job(job):
    print(f"Processing Job: {job['id']} - Prompt: {job['prompt'][:20]}...")
    
    try:
        results = []
        if job['type'] == 'image_generation':
            results = generate_image(job['prompt'])
            
        if not results:
            print("Generation Failed.")
            return

        # Upload Results
        filename, content = results[0] # Take first image
        b64_content = base64.b64encode(content).decode('utf-8')
        
        payload = {
            "job_id": job['id'],
            "type": job['type'],
            "filename": filename,
            "file_content": b64_content
        }
        
        headers = {"Authorization": f"Bearer {SERVER_TOKEN}", "Content-Type": "application/json"}
        resp = requests.post(f"{SERVER_URL}/jobs/complete", json=payload, headers=headers)
        
        if resp.status_code == 200:
            print(f"Job {job['id']} Completed & Uploaded! URL: {resp.json().get('url')}")
        else:
            print(f"Upload Failed: {resp.text}")

    except Exception as e:
        print(f"Job Processing Failed: {e}")
        import traceback
        traceback.print_exc()

# Helper: Upload Image to ComfyUI (Needed for Video Init)
def upload_image_to_comfy(image_path_or_bytes, filename=None):
    if filename is None:
        filename = f"init_{int(time.time())}.png"
    
    files = {}
    if isinstance(image_path_or_bytes, str):
        files = {"image": (filename, open(image_path_or_bytes, 'rb'))}
    else:
        files = {"image": (filename, image_path_or_bytes)}
        
    data = {"overwrite": "true"}
    resp = requests.post(f"http://{COMFY_URL}/upload/image", files=files, data=data)
    return resp.json()

# Helper: Load Workflow Template
def load_workflow(name):
    path = os.path.join(os.path.dirname(__file__), "workflows", name)
    with open(path, "r", encoding="utf-8") as f:
        return json.load(f)

# Task: Generate Image (Flux)
def generate_image(prompt):
    print(f"Generating Image with Flux... Prompt: {prompt[:30]}...")
    workflow = load_workflow("flux_schnell.json")
    
    # Modify Nodes
    # Node 6: Positive Prompt
    workflow["6"]["inputs"]["text"] = prompt
    # Node 31: KSampler (Random Seed)
    workflow["31"]["inputs"]["seed"] = random.randint(1, 999999999999999)
    
    return execute_workflow(workflow, output_node_id="9")

# Task: Generate Video (Wan 2.2)
def generate_video(image_bytes, prompt):
    print(f"Generating Video with Wan 2.2... Prompt: {prompt[:30]}...")
    
    # 1. Upload Init Image
    upload_resp = upload_image_to_comfy(image_bytes)
    uploaded_filename = upload_resp["name"]
    print(f"Init Image Uploaded: {uploaded_filename}")
    
    workflow = load_workflow("video_wan2_2_5B_ti2v.json")
    
    # Modify Nodes
    # Node 56: Load Image
    workflow["56"]["inputs"]["image"] = uploaded_filename
    # Node 6: Positive Prompt
    workflow["6"]["inputs"]["text"] = prompt
    # Node 3: KSampler (Random Seed)
    workflow["3"]["inputs"]["seed"] = random.randint(1, 999999999999999)
    
    # Output Node is 58 (SaveVideo) or checks if CreateVideo (57)
    return execute_workflow(workflow, output_node_id="58")

# Core: Execute and Wait
def execute_workflow(workflow, output_node_id):
    ws = websocket.WebSocket()
    ws.connect(f"ws://{COMFY_URL}/ws?clientId={CLIENT_ID}")
    
    # Send Prompt
    prompt_id = queue_prompt(workflow)['prompt_id']
    print(f"Queued: {prompt_id}")
    
    # Wait for Completion via WebSocket
    while True:
        out = ws.recv()
        if isinstance(out, str):
            message = json.loads(out)
            if message['type'] == 'executing':
                data = message['data']
                if data['node'] is None and data['prompt_id'] == prompt_id:
                    print("Execution Complete!")
                    break # Done
                elif data['prompt_id'] == prompt_id:
                    print(f"Executing Node: {data['node']}")
    
    # Fetch History to find output filename
    history = get_history(prompt_id)[prompt_id]
    outputs = history['outputs'][output_node_id]
    
    # Handle Image or Video output
    file_data = []
    
    if 'images' in outputs:
        for img in outputs['images']:
            fname = img['filename']
            subfolder = img['subfolder']
            ftype = img['type']
            content = get_image(fname, subfolder, ftype)
            file_data.append((fname, content))
            
    elif 'gifs' in outputs: # Sometimes videos are under gifs/videos key
         for vid in outputs['gifs']:
            fname = vid['filename']
            subfolder = vid['subfolder']
            ftype = vid['type']
            content = get_image(fname, subfolder, ftype)
            file_data.append((fname, content))
            
    # ComfyUI SaveVideo often returns 'gifs' or custom keys depending on node.
    # We will assume standard output structure or modify based on observation.
    
    return file_data

def queue_prompt(workflow):
    p = {"prompt": workflow, "client_id": CLIENT_ID}
    data = json.dumps(p).encode('utf-8')
    req = urllib.request.Request(f"http://{COMFY_URL}/prompt", data=data)
    return json.loads(urllib.request.urlopen(req).read())

def get_image(filename, subfolder, folder_type):
    data = {"filename": filename, "subfolder": subfolder, "type": folder_type}
    url_values = urllib.parse.urlencode(data)
    with urllib.request.urlopen(f"http://{COMFY_URL}/view?{url_values}") as response:
        return response.read()

def get_history(prompt_id):
    with urllib.request.urlopen(f"http://{COMFY_URL}/history/{prompt_id}") as response:
        return json.loads(response.read())

def run_test():
    print("=== STARTING TEST DRIVE ===")
    
    # 1. Test Image (Flux)
    # New Style: Hayao Miyazaki Cyberpunk
    prompt = "A cyberpunk street scene in the style of Hayao Miyazaki and Studio Ghibli. Vibrant colors, anime style, lush details, soft shading, cel shaded, breathable atmosphere. A futuristic cat with tech goggles sitting on a mossy pipe."
    print(f"STEP 1: Generating Image... '{prompt}'")
    
    try:
        results = generate_image(prompt)
        if not results:
            print("Image Generation Failed (No Output).")
            return

        filename, content = results[0]
        with open("test_flux.png", "wb") as f:
            f.write(content)
        print(f"STEP 1 SUCCESS: Saved 'test_flux.png'")
        
        # 2. Test Video (Wan)
        print("STEP 2: Generating Video from Image...")
        vid_results = generate_video(content, prompt + ", blinking eyes, looking around, movement")
        
        if not vid_results:
            print("Video Generation Failed (No Output).")
            return
            
        v_filename, v_content = vid_results[0]
        with open("test_wan.mp4", "wb") as f:
            f.write(v_content)
        print(f"STEP 2 SUCCESS: Saved 'test_wan.mp4'")
        
        print("=== TEST DRIVE COMPLETE ===")
        print("Check the 'local_worker' folder for results!")

    except Exception as e:
        print(f"TEST FAILED: {e}")
        import traceback
        traceback.print_exc()

def main():
    print(f"Worker Started. Connecting to {SERVER_URL}...")
    print(f"ComfyUI Target: {COMFY_URL}")
    
    # Test Connection
    try:
        requests.get(f"http://{COMFY_URL}")
        print("ComfyUI Connection: OK")
    except:
        print("ERROR: Could not connect to ComfyUI. Is it running?")
        return

    # Production Loop
    print("Worker is Active & Polling...")
    
    while True:
        try:
            job = fetch_pending_task() # This is currently mocked to return None
            if job:
                process_job(job)
            else:
                time.sleep(5) # Poll every 5 seconds
        except KeyboardInterrupt:
            print("Worker Stopped.")
            break
        except Exception as e:
            print(f"Error in Loop: {e}")
            time.sleep(5)

if __name__ == "__main__":
    main()
