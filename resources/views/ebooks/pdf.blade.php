<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $ebook->title }}</title>
    <style>
        /* 
         * GLOBAL PAGE SETUP 
         * Zero margins for the paper itself to allow full-bleed images/backgrounds.
         */
        @page {
            margin: 0px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1a1a1a;
            line-height: 1.6;
            font-size: 14px;
        }

        /* 
         * COVER PAGE 
         * Absolute positioning to ensure it covers 100% of the first page.
         */
        .cover-page {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 10;
            page-break-after: always;
        }

        .cover-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures image fills the page, cropping if necessary */
            z-index: -1;
        }

        .cover-content {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            display: table; /* For vertical centering fallback */
        }
        
        .cover-content-inner {
            display: table-cell;
            vertical-align: bottom; /* Position title at bottom, or change as needed */
            padding: 50px;
            color: white;
            text-shadow: 2px 2px 4px #000000;
        }

        .cover-title {
            font-size: 48px;
            font-weight: bold;
            margin: 0 0 20px 0;
            text-transform: uppercase;
        }

        .cover-author {
            font-size: 24px;
            margin: 0;
        }

        /* 
         * CONTENT PAGES
         * Since page margin is 0, we must manually add padding to text elements.
         * Images will remain strictly full-width.
         */
        .content-section {
            padding-top: 50px; /* Space top of subsequent pages */
        }

        /* Text Elements: Inset from the edge */
        p, h1, h2, h3, h4, h5, h6, ul, ol, li, blockquote {
            padding-left: 50px;
            padding-right: 50px;
            margin-bottom: 1em;
        }

        /* Images: Full Width, No Padding */
        img {
            width: 100%;
            height: auto;
            display: block;
            margin: 30px 0; /* Vertical spacing */
            object-fit: contain; /* Ensures entire image is visible */
            page-break-inside: avoid;
        }

        /* Typography Defaults */
        h1, h2 { 
            margin-top: 1.5em; 
            color: #000;
            page-break-after: avoid;
        }
        h1 { font-size: 28px; border-bottom: 2px solid #000; padding-bottom: 10px; margin-right: 50px; /* Border respects padding */ }
        h2 { font-size: 22px; }
        
        a { color: #000; text-decoration: none; }

        /* Drop Cap Styling (if Controller injects it) */
        .drop-cap {
            float: left;
            font-size: 3.5em; /* Large */
            line-height: 0.8;
            font-weight: bold;
            margin-right: 8px;
            margin-top: 4px;
            color: #000;
        }

        /* Divider */
        hr {
            border: 0;
            border-top: 2px solid #000;
            margin: 40px 50px; /* Match text side margins */
        }
        
        /* New Chapter Logic */
        h1 {
            page-break-before: always;
        }
        /* Except the very first H1 if it follows the cover immediately */
        .content-section > h1:first-child {
            page-break-before: auto;
        }

    </style>
</head>
<body>

    <!-- Cover Page -->
    <div class="cover-page">
        @if(isset($coverPath) && $coverPath)
            <img src="{{ $coverPath }}" class="cover-bg" alt="Cover Image">
        @else
            <!-- Fallback if no image -->
            <div style="width:100%; height:100%; background:#222;"></div>
        @endif
        
        <div class="cover-content">
            <div class="cover-content-inner">
                <h1 class="cover-title">{{ $ebook->title }}</h1>
                <p class="cover-author">Anxipunk Chronicles</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-section">
        {!! $content !!}
    </div>

</body>
</html>
