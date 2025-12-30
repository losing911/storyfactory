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

# Configuration
SERVER_URL = os.getenv("SERVER_URL", "https://anxipunk.icu/api")
SERVER_TOKEN = os.getenv("SERVER_TOKEN", "anxipunk_secret_worker_key_2025")

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
                      .replace("http://127.0.0.1", "https://anxipunk.icu")
                      
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

# Task: Generate via Pollinations.ai
def generate_image_pollinations(prompt, model='turbo'):
    # Style Injection: Vibrant Ghibli Cyberpunk
    # Style Injection: Vibrant High-End Anime (Makoto Shinkai, Ufotable, Cyberpunk)
    style = ", anime masterpiece, Makoto Shinkai style, Ufotable animation, vivid cyberpunk colors, highly detailed, perfect lighting, lens flare, cinematic angle, 8k, digital illustration, trending on artstation"
    clean_prompt = prompt.replace("photorealistic", "").replace("realistic", "") # Simple cleaning
    
    final_prompt = clean_prompt + style
    encoded_prompt = urllib.parse.quote(final_prompt)
    
    seed = random.randint(1, 99999)
    # Dynamic Model Usage
    url = f"https://image.pollinations.ai/prompt/{encoded_prompt}?width=1280&height=720&model={model}&nologo=true&seed={seed}&enhance=true"
    
    print(f"Requesting Pollinations ({model}): {url[:60]}...")
    
    try:
        resp = requests.get(url, timeout=60)
        if resp.status_code == 200:
            print("Pollinations Generation Success!")
            return [(f"pollinations_{seed}.jpg", resp.content)]
        elif resp.status_code == 429:
            print(f"⚠️ Pollinations RATE LIMIT (429)! Cooling down for 120s...")
            time.sleep(120)
            return []
        else:
            # Check for "Fake Success" (Error Image)
            # Pollinations sometimes returns a small image with text "Error" or "Rate Limit" and status 200
            if len(resp.content) < 4096: # Less than 4KB is suspicious for a stored image
                 print(f"⚠️ Suspiciously small image ({len(resp.content)} bytes). Likely an error placeholder. Retrying later...")
                 time.sleep(10)
                 return []
                 
            print("Pollinations Generation Success!")
            return [(f"pollinations_{seed}.jpg", resp.content)]
    except Exception as e:
        print(f"Pollinations Request Failed: {e}")
        return []

# API: Process and Upload
def process_job(job):
    print(f"Processing Job: {job['id']} (Pollinations Mode)")
    
    try:
        results = []
        if job['type'] == 'image_generation':
            # Use style_preset from API or default to turbo
            model = job.get('style_preset', 'turbo') 
            results = generate_image_pollinations(job['prompt'], model=model)
            
        if not results:
            print("Generation Failed.")
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
        print(f"Uploading result for Scene {job.get('scene_index', 0)}...")
        resp = requests.post(f"{SERVER_URL}/jobs/complete", json=payload, headers=headers)
        
        if resp.status_code == 200:
            data = resp.json()
            print(f"Job {job['id']} Uploaded! URL: {data.get('url')}")
            return data # Return response data (contains story_finished)
        else:
            print(f"⚠️ UPLOAD FAILED [Status {resp.status_code}]")
            print("Saving error details to 'last_error.html'...")
            with open("last_error.html", "w", encoding="utf-8") as f:
                f.write(resp.text)
            time.sleep(10)
            return None

    except Exception as e:
        print(f"Job Processing Failed: {e}")
        time.sleep(5)
        return None

def main():
    print(f"Worker Started. Engine: Pollinations.ai (Turbo)")
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
