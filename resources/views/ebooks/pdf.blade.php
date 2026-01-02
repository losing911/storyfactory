<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $ebook->title }}</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'DejaVu Sans', serif; /* UTF-8 support */
            margin-top: 50px;
            margin-bottom: 50px;
            margin-left: 50px;
            margin-right: 50px;
            color: #1a1a1a;
            line-height: 1.6;
            font-size: 12pt;
        }
        /* Cover Page */
        .cover-page {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            page-break-after: always;
            background-color: #000;
            color: #fff;
            padding-top: 300px;
        }
        .cover-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            opacity: 0.6;
        }
        .cover-title {
            font-size: 48pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-shadow: 0 0 10px #00ffff;
        }
        .cover-subtitle {
            font-size: 18pt;
            letter-spacing: 4px;
            color: #00ffff;
        }
        
        /* Content */
        h1 {
            color: #000;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-top: 50px;
            page-break-before: always;
        }
        h2 {
            border-bottom: 1px solid #ccc;
        }
        p {
            margin-bottom: 15px;
            text-align: justify;
        }
        .part-illustration img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 20px auto;
            border: 1px solid #000;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
            color: #777;
        }
    </style>
</head>
<body>
    
    <!-- Cover -->
    <div class="cover-page">
        @if($ebook->cover_image_url)
            <img src="{{ public_path($ebook->cover_image_url) }}" class="cover-image">
        @endif
        <div class="cover-title">{{ $ebook->title }}</div>
        <div class="cover-subtitle">VOLUME {{ str_pad($ebook->volume_number, 2, '0', STR_PAD_LEFT) }}</div>
    </div>

    <!-- Info Page -->
    <div style="page-break-after: always; padding-top: 200px; text-align: center;">
        <h2>ANXIPUNK ARCHIVES</h2>
        <p>This document is a collection of digitally preserved memories from Neo-Pera.</p>
        <p><strong>Volume:</strong> {{ $ebook->volume_number }}</p>
        <p><strong>Generated:</strong> {{ $ebook->created_at->format('Y-m-d H:i') }}</p>
        <p><strong>Source:</strong> anxipunk.icu</p>
        <br><br>
        <p style="font-size: 8pt; color: #999;">NOT FOR COMMERCIAL REDISTRIBUTION WITHOUT AUTHORIZATION.</p>
    </div>

    <!-- Content -->
    <div class="content">
        {!! $ebook->content !!}
    </div>

</body>
</html>
