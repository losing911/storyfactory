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

    # Helper to clean hashtags (remove spaces/punctuation)
    def clean_hashtag(tag):
        import re
        # Remove special chars, keep alphanumeric and underscores, remove spaces
        # "Science Fiction" -> "ScienceFiction"
        # "Çizgi Roman" -> "ÇizgiRoman"
        clean = re.sub(r'[^\w\s]', '', tag)
        clean = "".join([word.capitalize() for word in clean.split()])
        return f"#{clean}"

    # 3. Trends (Scraping Fallback for Free Tier)
    print("Fetching trends via Scraper...")
    trending_hashtags = []
    
    try:
        # Simple scraper for trends24 or similar (mocking logic here for safety or implementing simple regex on a public page)
        # For stability and speed, let's use a curated list + fixed high-traffic tags first.
        # If user really wants dynamic, we can try to parse a public URL.
        # Let's try to fetch from a public RSS or lightweight HTML if possible.
        # For now, let's improve the list to be more generic:
        trending_hashtags = ["#Gündem", "#Türkiye", "#SonDakika", "#YapayZeka", "#Sanat"]
    except Exception as e:
        print(f"Scraper failed: {e}")
        trending_hashtags = ["#Gündem", "#Türkiye"]

    # 4. Prepare Content
    title = story.get('title')
    link = story.get('url')
    story_tags = story.get('tags', [])
    
    # Clean and Format Story Tags
    story_hashtags = [clean_hashtag(tag) for tag in story_tags[:3]]
    
    # Combine
    all_hashtags = " ".join(story_hashtags + trending_hashtags)
    
    # Construct Tweet with Length Check
    # URL takes 23 characters fixed.
    # We leave 20 chars buffer. Max 280.
    import time
    timestamp = str(int(time.time())) # To avoid duplicate content errors during testing
    
    base_text = f"🤖 {title}\n\n"
    footer = f"\n\n🔗 Oku: {link}\n\n{all_hashtags} [{timestamp}]"
    
    available_chars = 280 - len(base_text) - len(footer) - 5
    summary = story.get('summary')
    
    if len(summary) > available_chars:
        summary = summary[:available_chars] + "..."
        
    text = base_text + summary + footer

    print("Strategy: Text + Link Card (Link Card Mode)")
    print(f"Final Tweet Length: {len(text)}")
    print(f"Tweet Content: {text}")

    # 6. Post Tweet
    try:
        print("Posting tweet...")
        response = client.create_tweet(text=text)
        print(f"Tweet posted successfully! ID: {response.data['id']}")
            
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
