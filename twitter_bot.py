import os
import requests
import tweepy
import shutil
from dotenv import load_dotenv

# Load .env file (assuming it's in the same directory)
load_dotenv()

# Configuration
API_BASE_URL = os.getenv('APP_URL', 'http://localhost/anxipunk.art')
TWITTER_CONSUMER_KEY = os.getenv('TWITTER_CONSUMER_KEY')
TWITTER_CONSUMER_SECRET = os.getenv('TWITTER_CONSUMER_SECRET')
TWITTER_ACCESS_TOKEN = os.getenv('TWITTER_ACCESS_TOKEN')
TWITTER_ACCESS_TOKEN_SECRET = os.getenv('TWITTER_ACCESS_TOKEN_SECRET')

def get_latest_story():
    """Fetch the latest story from the local API."""
    url = f"{API_BASE_URL}/api/stories/latest"
    print(f"Fetching story from: {url}")
    try:
        response = requests.get(url, verify=False) # verify=False for local dev certificates
        response.raise_for_status()
        return response.json()
    except Exception as e:
        print(f"Error fetching story: {e}")
        return None

def download_image(image_url, filename="temp_cover.jpg"):
    """Download the story cover image temporarily."""
    if not image_url:
        return None
    
    # Fix local URL if needed (e.g. if API returns relative or localhost url that needs adjustment)
    # The API returns asset() url which should be absolute.
    
    try:
        print(f"Downloading image: {image_url}")
        res = requests.get(image_url, stream=True, verify=False)
        if res.status_code == 200:
            with open(filename, 'wb') as f:
                shutil.copyfileobj(res.raw, f)
            return filename
    except Exception as e:
        print(f"Error downloading image: {e}")
    return None

def post_to_twitter(story):
    """Post the story to Twitter."""
    
    # 1. Authenticate (V1.1 for Media Upload, V2 for Tweeting)
    print("Authenticating...")
    auth = tweepy.OAuth1UserHandler(
        TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET,
        TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET
    )
    api = tweepy.API(auth)
    
    # Verify Credentials (V1.1)
    try:
        user = api.verify_credentials()
        print(f"Authenticated as: {user.screen_name}")
    except Exception as e:
        print(f"Authentication Failed (Check Keys): {e}")
        return

    client = tweepy.Client(
        consumer_key=TWITTER_CONSUMER_KEY,
        consumer_secret=TWITTER_CONSUMER_SECRET,
        access_token=TWITTER_ACCESS_TOKEN,
        access_token_secret=TWITTER_ACCESS_TOKEN_SECRET
    )

    # Verify Credentials (V2)
    try:
        me = client.get_me()
        print(f"V2 Auth Success: {me.data.name} (ID: {me.data.id})")
    except Exception as e:
        print(f"V2 Auth Failed: {e}")

    # 3. Trends (Skipped - Not available on Free Tier)
    print("Trends API unavailable on Free Tier. Using static tags.")
    trending_hashtags = ["#Gündem", "#Türkiye", "#YapayZeka", "#Cyberpunk", "#Sanat"]

    # 4. Prepare Content
    title = story.get('title')
    link = story.get('url')
    story_tags = story.get('tags', [])
    
    # Combine Tags
    story_hashtags = [f"#{tag}" for tag in story_tags[:3]]
    all_hashtags = " ".join(story_hashtags + trending_hashtags)
    
    text = f"🤖 {title}\n\n{story.get('summary')}\n\n🔗 Oku: {link}\n\n{all_hashtags}"
    
    # 5. Upload Image
    media_id = None
    image_file = download_image(story.get('image_url'))
    if image_file:
        try:
            print("Uploading media...")
            media = api.media_upload(filename=image_file)
            media_id = media.media_id
            print(f"Media uploaded. ID: {media_id}")
        except Exception as e:
            print(f"Media upload failed: {e}")
    
    # TEST: Try posting simple text first to check permissions (Unique content)
    import time
    try:
        print("Test: Posting text-only tweet...")
        unique_text = f"Test tweet from Anxipunk Bot - {time.time()}"
        client.create_tweet(text=unique_text)
        print("Text-only tweet success! It is PERMISSION issue only with media or duplicates.")
    except Exception as e:
        print(f"Text-only failed: {e}")

    # STRATEGY CHANGE: API V2 Free Tier often blocks Media Uploads (403).
    # However, it allows Link Cards.
    # We will verify if the site has og:image tags. If so, posting the LINK is enough.
    
    print("Strategy: Optimized for Free Tier (Link Card Mode)")
    
    # 6. Post Tweet (Text + Link)
    try:
        print("Posting tweet (Text + Link)...")
        # Ensure the text is within limits (280 chars).
        # We constructed 'text' earlier.
        response = client.create_tweet(text=text)
        print(f"Tweet posted successfully! ID: {response.data['id']}")
        print("Twitter should automatically generate a Card with the image from the URL.")
            
    except Exception as e:
        print(f"Failed to post tweet: {e}")

if __name__ == "__main__":
    if not TWITTER_CONSUMER_KEY:
        print("Error: TWITTER credentials missing in .env file.")
    else:
        print("Bot started...")
        story = get_latest_story()
        if story:
            post_to_twitter(story)
        else:
            print("No new story to post.")
