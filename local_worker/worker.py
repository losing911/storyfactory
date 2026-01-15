import os
import json
import time
import requests
import base64
import urllib.parse
import random
from dotenv import load_dotenv

# Load Local Environment
# Load Environment
# Try loading from parent directory (Laravel root)
parent_env_path = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), '.env')
if os.path.exists(parent_env_path):
    print(f"Loading .env from: {parent_env_path}")
    load_dotenv(parent_env_path)
else:
    print("Warning: Parent .env not found. Looking in current directory.")
    load_dotenv()

# ... (Imports preserved)
import uuid

# Configuration
SERVER_URL = os.getenv("SERVER_URL", "https://anxipunk.icu/api")
SERVER_TOKEN = os.getenv("SERVER_TOKEN", "anxipunk_secret_worker_key_2025")
COMFY_URL = os.getenv("COMFY_URL", "http://127.0.0.1:8188") # Default ComfyUI URL

# Twitter Config
TWITTER_CONSUMER_KEY = os.getenv('TWITTER_CONSUMER_KEY')
TWITTER_CONSUMER_SECRET = os.getenv('TWITTER_CONSUMER_SECRET')
TWITTER_ACCESS_TOKEN = os.getenv('TWITTER_ACCESS_TOKEN')
TWITTER_ACCESS_TOKEN_SECRET = os.getenv('TWITTER_ACCESS_TOKEN_SECRET')

try:
    import tweepy
except ImportError:
    print("Warning: 'tweepy' not installed. Twitter bot will not run. (pip install tweepy)")
    tweepy = None

# API: Check for Jobs
def fetch_pending_task():
    try:
        headers = {"Authorization": f"Bearer {SERVER_TOKEN}", "Accept": "application/json"}
        # Increased timeout for slow connections
        resp = requests.get(f"{SERVER_URL}/jobs/pending", headers=headers, timeout=20)
        
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

# Twitter Bot Logic
def post_tweet_if_finished(job_result):
    print(f"DEBUG: Story Finished Flag: {job_result.get('story_finished')}")
    if not job_result.get('story_finished'):
        return

    print("Story Finished! Starting Twitter Bot...")
    if not tweepy:
        print("Twitter Bot Skipped: Tweepy not installed.")
        return

    try:
        # Fetch Latest Story Details
        headers = {"Authorization": f"Bearer {SERVER_TOKEN}", "Accept": "application/json"}
        resp = requests.get(f"{SERVER_URL}/stories/latest", headers=headers)
        if resp.status_code != 200:
            print(f"Twitter Bot Error: Could not fetch story details (Status {resp.status_code})")
            return
            
        story = resp.json()
        print(f"DEBUG: Latest Story Fetched: {story.get('title')}")
        
        title = story['title']
        raw_url = story['url']
        summary = story['summary']
        
        # FIX: Ensure URL is Production (Not Localhost)
        link = raw_url.replace("http://localhost/anxipunk.art", "https://anxipunk.icu") \
                      .replace("http://localhost", "https://anxipunk.icu") \
                      .replace("http://127.0.0.1", "https://anxipunk.icu") \
                      .replace("anxipunk.art", "anxipunk.icu")
                      
        if not link.startswith("https://anxipunk.icu"):
             # If it's some other weird path, force it (assuming slug is correct)
             slug = raw_url.split('/')[-1]
             link = f"https://anxipunk.icu/story/{slug}"

        # Auth
        client = tweepy.Client(
            consumer_key=TWITTER_CONSUMER_KEY,
            consumer_secret=TWITTER_CONSUMER_SECRET,
            access_token=TWITTER_ACCESS_TOKEN,
            access_token_secret=TWITTER_ACCESS_TOKEN_SECRET
        )
        
        # Prepare Tweet
        hashtags = " ".join([f"#{tag.replace(' ', '')}" for tag in story.get('tags', [])[:3]])
        footer = f"\n\n🔗 Oku: {link}\n\n{hashtags} #Cyberpunk #AI"
        
        # Truncate summary
        limit = 280 - len(footer) - len(title) - 10
        if len(summary) > limit:
            summary = summary[:limit] + "..."
            
        text = f"🤖 {title}\n\n{summary}{footer}"
        
        print("Posting Tweet...")
        resp = client.create_tweet(text=text)
        print(f"✅ TWEET POSTED SUCCESSFULLY! ID: {resp.data['id']}")
        
    except Exception as e:
        print(f"❌ Twitter Bot Failed: {e}")
        import traceback
        traceback.print_exc()

# ComfyUI Helper Functions
def queue_prompt(workflow):
    p = {"prompt": workflow, "client_id": str(uuid.uuid4())}
    data = json.dumps(p).encode('utf-8')
    headers = {'Content-Type': 'application/json'}
    req = requests.post(f"{COMFY_URL}/prompt", data=data, headers=headers)
    return req.json()

def get_history(prompt_id):
    resp = requests.get(f"{COMFY_URL}/history/{prompt_id}")
    return resp.json()

def get_image(filename, subfolder, folder_type):
    data = {"filename": filename, "subfolder": subfolder, "type": folder_type}
    resp = requests.get(f"{COMFY_URL}/view", params=data)
    return resp.content

def generate_music_comfyui(prompt, duration=30):
    print(f"🎵 Connecting to ComfyUI for Music: {prompt[:50]}...")
    
    # Load Workflow
    workflow_path = os.path.join(os.path.dirname(__file__), "workflows", "audio_stable_audio_example.json")
    if not os.path.exists(workflow_path):
        print(f"❌ Workflow file not found: {workflow_path}")
        return None

    with open(workflow_path, "r", encoding="utf-8") as f:
        workflow = json.load(f)

    # Inject Prompt & Duration
    # Strategy: Hardcode Node IDs for 'audio_stable_audio_example.json'
    # Node 6: Positive Prompt (CLIPTextEncode)
    # Node 11: Duration (EmptyLatentAudio, seconds)
    
    try:
        # 1. Set Prompt
        if "6" in workflow:
             workflow["6"]["inputs"]["text"] = prompt
             print(f"Updated Prompt in Node 6")
        else:
            print("⚠️ Node 6 (Positive Prompt) not found in workflow!")

        # 2. Set Duration
        if "11" in workflow:
             # Ensure duration is float
             workflow["11"]["inputs"]["seconds"] = float(duration)
             print(f"Updated Duration in Node 11 to {duration}s")
        else:
             print("⚠️ Node 11 (Duration) not found in workflow!")

    except Exception as e:
        print(f"⚠️ Error injecting prompt/duration: {e}")
        return None

    try:
        # 1. Queue Job
        resp = queue_prompt(workflow)
        if 'error' in resp:
             print(f"❌ ComfyUI Queue Error: {resp['error']}")
             return None
             
        prompt_id = resp['prompt_id']
        print(f"ComfyUI Job Queued: {prompt_id}")
        
        # 2. Poll Status
        while True:
            history = get_history(prompt_id)
            if prompt_id in history:
                print("Job Completed in ComfyUI.")
                outputs = history[prompt_id]['outputs']
                
                # Find Audio Output
                # Node 19 is SaveAudioMP3 in this workflow
                found_audio = False
                for node_id, output_data in outputs.items():
                    if 'audio' in output_data:
                        # Depending on node type (SaveAudio vs SaveAudioMP3), keys might differ
                        # Usually it is a list of objects
                        for audio_item in output_data['audio']:
                            filename = audio_item['filename']
                            subfolder = audio_item['subfolder']
                            folder_type = audio_item['type']
                            
                            print(f"Downloading Audio: {filename}...")
                            audio_content = get_image(filename, subfolder, folder_type) # Reusing get_image for audio
                            return [(filename, audio_content)]
                        found_audio = True
                
                if not found_audio:
                     print("❌ No audio output found in history.")
                     return None
                break
            else:
                time.sleep(1)
                
    except Exception as e:
        print(f"ComfyUI Error: {e}")
        return None
    
    return None

# Task: Generate via Pollinations.ai
def generate_image_pollinations(prompt, model='turbo'):
    # Style Injection: Vibrant High-End Anime (Makoto Shinkai, Ufotable, Cyberpunk)
    style = ", anime masterpiece, Makoto Shinkai style, Ufotable animation, vivid cyberpunk colors, highly detailed, perfect lighting, lens flare, cinematic angle, 8k, digital illustration, trending on artstation"
    clean_prompt = prompt.replace("photorealistic", "").replace("realistic", "") # Simple cleaning
    
    if not clean_prompt.lower().startswith("sgbl artstyle"):
        final_prompt = "sgbl artstyle, " + clean_prompt + style
    else:
        final_prompt = clean_prompt + style
    encoded_prompt = urllib.parse.quote(final_prompt)
    
    seed = random.randint(1, 99999)
    
    # Dynamic Model Usage
    if model:
        # Standard Request
        url = f"https://image.pollinations.ai/prompt/{encoded_prompt}?width=1280&height=720&model={model}&nologo=true&seed={seed}&enhance=true"
        print(f"Requesting Pollinations ({model}): {url[:60]}...")
    else:
        # Fallback Request (No Model, No Enhance - Safest Mode)
        url = f"https://image.pollinations.ai/prompt/{encoded_prompt}?width=1280&height=720&nologo=true&seed={seed}"
        print(f"Requesting Pollinations (DEFAULT/FALLBACK): {url[:60]}...")
    
    try:
        resp = requests.get(url, timeout=120)
        if resp.status_code == 200:
            # Check for "Fake Success" (Error Image)
            # "Sign Up" images or Error placeholders are often small (< 30KB)
            if len(resp.content) < 30000: # ~30KB threshold
                 print(f"⚠️ Suspiciously small image ({len(resp.content)} bytes). Likely an error/signup placeholder. REJECTING.")
                 return []
                 
            print("Pollinations Generation Success!")
            return [(f"pollinations_{seed}.jpg", resp.content)]
            
        elif resp.status_code == 429:
            print(f"⚠️ Pollinations RATE LIMIT (429)! Cooling down for 60s...")
            time.sleep(60) # Reduced cool down for retry
            return []
        else:
            print(f"Pollinations Error: {resp.status_code}")
            return []
    except Exception as e:
        print(f"Pollinations Request Failed: {e}")
        return []

# Task: Generate Image via ComfyUI (Flux Schnell Workflow)
def generate_image_comfyui(prompt):
    print(f"🎨 Connecting to ComfyUI (Flux Schnell) for Image: {prompt[:50]}...")
    
    workflow_path = os.path.join(os.path.dirname(__file__), "workflows", "flux_schnell.json")
    if not os.path.exists(workflow_path):
        print(f"❌ Workflow file not found: {workflow_path}")
        return None

    try:
        with open(workflow_path, "r", encoding="utf-8") as f:
            workflow = json.load(f)
    except Exception as e:
         print(f"❌ Failed to load workflow JSON: {e}")
         return None

    # Node Mapping for Flux Schnell:
    # Node 6: Positive Prompt (CLIPTextEncode)
    # Node 31: KSampler (Seed)
    # Node 9: SaveImage
    
    try:
        # 1. Set Prompt
        if "6" in workflow:
             # Enhance prompt for Flux
             if not prompt.lower().startswith("sgbl artstyle"):
                 enhanced_prompt = f"sgbl artstyle, {prompt}, (anime style:1.2), masterpiece, best quality, vibrant colors"
             else:
                 enhanced_prompt = f"{prompt}, (anime style:1.2), masterpiece, best quality, vibrant colors"
             workflow["6"]["inputs"]["text"] = enhanced_prompt
             print(f"Updated Prompt in Node 6")
        else:
            print("⚠️ Node 6 (Positive Prompt) not found in workflow!")

        # 2. Set Seed
        seed = random.randint(1, 99999999999999)
        if "31" in workflow:
             workflow["31"]["inputs"]["seed"] = int(seed)
             print(f"Updated Seed in Node 31 to {seed}")
        
    except Exception as e:
        print(f"⚠️ Error injecting prompt/seed: {e}")
        return None

    try:
        # 1. Queue Job
        resp = queue_prompt(workflow)
        if 'error' in resp:
             print(f"❌ ComfyUI Queue Error: {resp['error']}")
             return None
             
        prompt_id = resp['prompt_id']
        print(f"ComfyUI Image Job Queued: {prompt_id}")
        
        # 2. Poll Status
        while True:
            history = get_history(prompt_id)
            if prompt_id in history:
                print("Job Completed in ComfyUI.")
                outputs = history[prompt_id]['outputs']
                
                # Find Image Output (Node 9 is SaveImage)
                for node_id, output_data in outputs.items():
                    if 'images' in output_data:
                        for img_item in output_data['images']:
                            filename = img_item['filename']
                            subfolder = img_item['subfolder']
                            folder_type = img_item['type']
                            
                            print(f"Downloading Image: {filename}...")
                            img_content = get_image(filename, subfolder, folder_type)
                            return [(filename, img_content)]
                
                print("❌ No image output found in history.")
                return None
            else:
                time.sleep(1)
                
    except Exception as e:
        print(f"ComfyUI Error: {e}")
        return None

# API: Process and Upload
def process_job(job):
    print(f"Processing Job: {job['id']} (Type: {job['type']})")
    
    try:
        results = []
        
        if job['type'] == 'image_generation':
            # 1. Try Local ComfyUI (RTX 4060 Power!)
            results = generate_image_comfyui(job['prompt'])
            
            # 2. Fallback to Pollinations if Local fails
            if not results:
                print("⚠️ Local ComfyUI failed. Falling back to Pollinations Cloud...")
                # Use style_preset from API or default to turbo
                model = job.get('style_preset', 'flux') # Default to Flux for quality
                results = generate_image_pollinations(job['prompt'], model=model)
        
        elif job['type'] == 'music_generation':
             results = generate_music_comfyui(job['prompt'], duration=job.get('duration', 30))
            
        if not results:
            print("❌ Generation Failed.")
            return None
 
        # Upload Results
        filename, content = results[0] # Take first image
        b64_content = base64.b64encode(content).decode('utf-8')
        
        payload = {
            "job_id": job['id'],
            "type": job['type'],
            "filename": filename,
            "file_content": b64_content,
            "scene_index": job.get('scene_index', 0)
        }
        
        headers = {"Authorization": f"Bearer {SERVER_TOKEN}", "Content-Type": "application/json"}
        print(f"Uploading result used for {job['type']}...")
        resp = requests.post(f"{SERVER_URL}/jobs/complete", json=payload, headers=headers)
        
        if resp.status_code == 200:
            data = resp.json()
            print(f"Job {job['id']} Uploaded! URL: {data.get('url')}")
            return data # Return response data (contains story_finished)
        else:
            print(f"⚠️ UPLOAD FAILED [Status {resp.status_code}]")
            return None
            
    except Exception as e:
        print(f"Job Processing Failed: {e}")
        time.sleep(5)
        return None

def main():
    print(f"Worker Started. Engines: Pollinations (Image) & ComfyUI (Music)")
    print("Worker is Active & Polling (Calm Mode)...")
    
    while True:
        try:
            job = fetch_pending_task()
            if job:
                result = process_job(job)
                
                # Twitter Check
                if result:
                    post_tweet_if_finished(result)
                
                # "Sakin Sakin" - Cooldown after work
                print("Cooling down for 45 seconds...")
                time.sleep(45) 
            else:
                # "Sakin Sakin" - Long Poll
                print("No jobs. Sleeping for 60s...")
                time.sleep(60) # Poll every 60 seconds
                
        except KeyboardInterrupt:
            print("Worker Stopped.")
            break
        except Exception as e:
            print(f"Error in Loop: {e}")
            time.sleep(30) # Error cooldown

if __name__ == "__main__":
    main()
