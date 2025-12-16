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
    auth = tweepy.OAuth1UserHandler(
        TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET,
        TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET
    )
    api = tweepy.API(auth)
    client = tweepy.Client(
        consumer_key=TWITTER_CONSUMER_KEY,
        consumer_secret=TWITTER_CONSUMER_SECRET,
        access_token=TWITTER_ACCESS_TOKEN,
        access_token_secret=TWITTER_ACCESS_TOKEN_SECRET
    )

    # 3. Fetch Trends (Turkey)
    trending_hashtags = []
    try:
        # WOEID for Turkey is 23424969
        trends = api.get_place_trends(id=23424969)
        if trends:
            # Get top 3 trends that start with # (to avoid plain text phrases if possible, or convert them)
            count = 0
            for trend in trends[0]['trends']:
                name = trend['name']
                if name.startswith('#'):
                    trending_hashtags.append(name)
                    count += 1
                if count >= 3:
                     break
        print(f"Turkey Trends fetched: {trending_hashtags}")
    except Exception as e:
        print(f"Could not fetch trends (Permission/RateLimit): {e}")
        # Fallback tags if trends fail
        trending_hashtags = ["#Gündem", "#Türkiye", "#Haber"]

    # 4. Prepare Content
    title = story.get('title')
    link = story.get('url') # Link to the live site
    story_tags = story.get('tags', [])
    
    # Combine: Story Tags (Generic) + Trends (Traffic)
    # Limit story tags to 3 to save space
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
    
    # 6. Post Tweet
    try:
        print("Posting tweet...")
        if media_id:
             response = client.create_tweet(text=text, media_ids=[media_id])
        else:
             response = client.create_tweet(text=text)
        
        print(f"Tweet posted successfully! ID: {response.data['id']}")
        
        # Cleanup
        if image_file and os.path.exists(image_file):
            os.remove(image_file)
            
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
