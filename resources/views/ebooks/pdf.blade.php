<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $ebook->title }}</title>
    <style>
        /* CYBERPUNK ANIME/MANGA STYLE PDF LAYOUT */
        
        @page {
            margin: 0px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            line-height: 1.8;
            font-size: 13px;
            position: relative;
        }

        /* === COVER PAGE === */
        .cover-page {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            overflow: hidden;
            page-break-after: always;
        }

        .cover-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.4;
            filter: brightness(0.6) contrast(1.2);
        }

        .cover-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, transparent 30%, rgba(0,0,0,0.8) 100%);
            z-index: 1;
        }

        .cover-content {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            display: table;
        }

        .cover-content-inner {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 60px;
        }

        /* Neon Corner Brackets */
        .cover-content-inner::before,
        .cover-content-inner::after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border: 3px solid #00FFFF;
            box-shadow: 0 0 15px #00FFFF, inset 0 0 15px #00FFFF;
        }

        .cover-content-inner::before {
            top: 30px;
            left: 30px;
            border-right: none;
            border-bottom: none;
        }

        .cover-content-inner::after {
            bottom: 30px;
            right: 30px;
            border-left: none;
            border-top: none;
        }

        .cover-title {
            font-size: 56px;
            font-weight: 900;
            text-transform: uppercase;
            color: #FFFFFF;
            text-shadow: 
                0 0 10px #00FFFF,
                0 0 20px #00FFFF,
                0 0 30px #00FFFF,
                3px 3px 0 #FF00FF,
                -3px -3px 0 #FF00FF;
            margin-bottom: 30px;
            letter-spacing: 4px;
            line-height: 1.2;
        }

        .cover-author {
            font-size: 24px;
            font-weight: bold;
            color: #00FFFF;
            text-transform: uppercase;
            letter-spacing: 8px;
            text-shadow: 0 0 10px #00FFFF;
        }

        .cover-version {
            font-size: 10px;
            color: #666;
            margin-top: 40px;
            font-family: monospace;
            letter-spacing: 2px;
        }

        /* === CONTENT SECTION === */
        .content-section {
            background: #0a0a0a;
            padding-top: 60px;
            position: relative;
        }

        /* Tech Frame Corners (on every page) */
        .tech-frame-tl,
        .tech-frame-br {
            position: fixed;
            width: 80px;
            height: 80px;
            border: 2px solid #00FFFF;
            opacity: 0.3;
        }

        .tech-frame-tl {
            top: 20px;
            left: 20px;
            border-right: none;
            border-bottom: none;
        }

        .tech-frame-br {
            bottom: 20px;
            right: 20px;
            border-left: none;
            border-top: none;
        }

        /* === TYPOGRAPHY === */
        p, ul, ol, li, blockquote {
            padding: 0 60px;
            margin-bottom: 1.2em;
            color: #d0d0d0;
        }

        h1, h2, h3 {
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            page-break-after: avoid;
            padding: 20px 60px;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 32px;
            color: #FFFFFF;
            background: linear-gradient(90deg, #FF00FF 0%, transparent 100%);
            border-left: 6px solid #00FFFF;
            text-shadow: 2px 2px 0 #000, 0 0 10px #FF00FF;
            page-break-before: always;
        }

        .content-section > h1:first-child {
            page-break-before: auto;
        }

        h2 {
            font-size: 24px;
            color: #00FFFF;
            border-left: 4px solid #FF00FF;
            text-shadow: 0 0 8px #00FFFF;
            background: rgba(0, 255, 255, 0.05);
        }

        h3 {
            font-size: 18px;
            color: #FF00FF;
            border-bottom: 2px solid #FF00FF;
            padding-bottom: 10px;
        }

        /* Drop Cap */
        .drop-cap {
            float: left;
            font-size: 4em;
            line-height: 0.85;
            font-weight: 900;
            margin-right: 8px;
            margin-top: 6px;
            color: #00FFFF;
            text-shadow: 
                0 0 10px #00FFFF,
                2px 2px 0 #FF00FF;
        }

        /* === IMAGES (COMIC PANEL STYLE) === */
        img {
            display: block;
            width: calc(100% - 120px);
            max-width: 100%;
            height: auto;
            margin: 40px auto;
            border: 5px solid #FFFFFF;
            box-shadow: 
                0 0 20px #00FFFF,
                0 0 40px rgba(0, 255, 255, 0.3),
                inset 0 0 20px rgba(255, 255, 255, 0.1);
            transform: rotate(-0.5deg);
            page-break-inside: avoid;
            background: #000;
            padding: 8px;
        }

        img:nth-of-type(even) {
            transform: rotate(0.5deg);
        }

        /* === DIVIDERS === */
        hr {
            border: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                #FF00FF 20%, 
                #00FFFF 50%, 
                #FF00FF 80%, 
                transparent 100%);
            margin: 50px 60px;
            box-shadow: 0 0 10px #00FFFF;
        }

        /* === LINKS === */
        a {
            color: #00FFFF;
            text-decoration: underline;
            text-shadow: 0 0 5px #00FFFF;
        }

        /* === SCAN LINE EFFECT (subtle) === */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(255, 255, 255, 0.02) 2px,
                rgba(255, 255, 255, 0.02) 4px
            );
            pointer-events: none;
            z-index: 9999;
            opacity: 0.3;
        }

    </style>
</head>
<body>

    <!-- Scan Line Overlay (via ::before pseudo-element) -->

    <!-- Cover Page -->
    <div class="cover-page">
        @if(isset($coverPath) && $coverPath)
            <img src="{{ $coverPath }}" class="cover-bg" alt="Cover">
        @endif
        <div class="cover-overlay"></div>
        
        <div class="cover-content">
            <div class="cover-content-inner">
                <h1 class="cover-title">{{ $ebook->title }}</h1>
                <p class="cover-author">ANXIPUNK CHRONICLES</p>
                <div class="cover-version">v4.0 // CYBERPUNK EDITION</div>
            </div>
        </div>
    </div>

    <!-- Tech Frames (repeated on every content page) -->
    <div class="tech-frame-tl"></div>
    <div class="tech-frame-br"></div>

    <!-- Main Content -->
    <div class="content-section">
        {!! $content !!}
    </div>

</body>
</html>
