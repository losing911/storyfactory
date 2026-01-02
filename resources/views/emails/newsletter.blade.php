<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background-color: #000; color: #ddd; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #111; border: 1px solid #333; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0ff; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #fff; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .content { line-height: 1.6; color: #ccc; }
        .footer { margin-top: 30px; border-top: 1px solid #333; padding-top: 10px; font-size: 10px; text-align: center; color: #555; }
        a { color: #0ff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ANXIPUNK</h1>
        </div>
        
        <div class="content">
            {!! $campaign->content !!}
        </div>

        <div class="footer">
            <p>Sent from Neo-Pera | <a href="{{ route('subscribe.unsubscribe', $subscriber->unsubscribe_token) }}">Unsubscribe / System Exit</a></p>
        </div>
    </div>
</body>
</html>
