<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $ebook->title }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #000;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #fff;
            width: 100%;
            height: 100%;
        }
        
        /* --- Cover Page --- */
        .cover-page {
            position: absolute; /* Force full page coverage */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            background-color: #050505;
            color: #fff;
            text-align: center;
            overflow: hidden;
            z-index: -1;
        }
        .cover-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.4;
            z-index: 1;
        }
        .cover-content {
            position: relative;
            z-index: 2;
            padding-top: 30%;
        }
        .brand-logo {
            font-size: 14pt;
            letter-spacing: 5px;
            color: #00ffff;
            margin-bottom: 2rem;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }
        .cover-title {
            font-size: 42pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 0 50px 20px 50px;
            font-family: sans-serif;
            text-shadow: 2px 2px 0px #ff00ff;
            border-bottom: 4px solid #00ffff;
            display: inline-block;
            padding-bottom: 10px;
        }
        .cover-volume {
            font-size: 24pt;
            letter-spacing: 8px;
            color: #ff00ff;
            margin-bottom: 50px;
        }
        .cover-footer {
            position: absolute;
            bottom: 50px;
            width: 100%;
            font-size: 10pt;
            color: #888;
        }

        /* --- Interior Pages (Comic/Light Novel Style) --- */
        .content-wrapper {
            margin: 1cm;
            font-size: 12pt;
            line-height: 1.5;
        }
        
        /* Force DejaVu Sans */
        * {
            font-family: 'DejaVu Sans', sans-serif !important;
        }
        
        /* --- Cyberpunk Visuals --- */
        
        /* Drop Cap (First Letter) */
        .drop-cap {
            float: left;
            font-size: 38pt;
            line-height: 0.8;
            font-weight: 900;
            color: #000;
            margin-right: 8px;
            margin-bottom: -5px;
            font-family: 'DejaVu Sans', sans-serif;
            text-shadow: 2px 2px 0px #ccc;
        }

        /* Cyberpunk Divider */
        hr.part-divider {
            border: 0;
            height: 10px;
            background-color: #000;
            border-bottom: 2px solid #fff; /* Stripe effect */
            border-top: 2px solid #fff;
            margin: 60px 20px;
            position: relative;
        }
        /* Page Frame / Corner Elements */
        .page-frame-top-left {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            border-top: 5px solid #000;
            border-left: 5px solid #000;
        }
        .page-frame-bottom-right {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-bottom: 5px solid #000;
            border-right: 5px solid #000;
        }
        .page-frame-top-right {
           position: fixed;
           top: 20px;
           right: 20px;
           content: "/// NET-2084";
           font-size: 8pt;
           color: #666;
           font-weight: bold;
        }

        /* Comic Panel Images (REVISED: Full Page / White BG) */
        .part-illustration {
            text-align: center;
            margin: 10px 0; 
            page-break-inside: avoid;
            background-color: transparent; /* White/Transparent BG */
            padding: 0; 
            box-shadow: none; 
            width: 100%; /* Full width */
        }
        .part-illustration img {
            border: none;
            display: block;
            width: 100%;
            height: auto; 
            max-height: 900px; /* Prevent spanning too many pages */
            object-fit: contain;
            margin: 0 auto;
        }

        /* Action Headers */
        h1 {
            color: #000;
            font-size: 24pt; 
            font-weight: 900;
            font-style: italic;
            text-transform: uppercase;
            border-bottom: 5px solid #000;
            padding-bottom: 5px;
            margin-top: 40px;
            margin-bottom: 30px;
            page-break-before: always;
            text-shadow: 3px 3px 0px #ccc; /* Pop */
            line-height: 1.2;
        }
        /* Style the ID within titles */
        h1 span.id-marker {
            display: block;
            font-size: 9pt;
            font-weight: normal;
            font-style: normal;
            color: #555;
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 2px;
            margin-top: 5px;
        }

        h2, h3 {
            font-weight: 900;
            background: #000; /* Inverted header */
            color: #fff;
            padding: 5px 10px;
            margin-top: 40px;
            margin-bottom: 20px;
            text-transform: uppercase;
            display: inline-block;
            transform: skew(-10deg); /* Cyberpunk skew */
        }

        /* Dialog/Text Style */
        p {
            margin-bottom: 15px;
            text-indent: 0;
            padding-left: 0;
            text-align: justify;
        }

        /* Footer / Page Numbers (Comic Style) */
        .page-footer {
            position: fixed;
            bottom: 20px;
            right: 20px; /* Corner page numbers */
            text-align: right;
            font-size: 10pt;
            font-weight: bold;
            color: #fff;
            background: #000;
            padding: 2px 8px;
            transform: skew(-10deg);
        }
        .page-number:after {
            content: counter(page);
        }
        
        /* Info Page styling (SIMPLIFIED for Stability) */
        .info-page {
            page-break-after: always;
            width: 100%;
            /* Removed display:table to prevent overlap issues */
            text-align: center;
            padding-top: 150px; 
            box-sizing: border-box;
        }
        .info-content {
            width: 80%;
            margin: 0 auto;
            border: 8px solid #000;
            padding: 40px;
            background: #fff;
        }
        /* Spacing for Info Page paragraphs */
        .info-content p {
            margin-bottom: 25px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <!-- Page Frame Elements (Repeats on every page due to fixed pos) -->
    <div class="page-frame-top-left"></div>
    <div class="page-frame-bottom-right"></div>
    <div class="page-frame-top-right"></div>

    <!-- Cover Page -->
    <div class="cover-page">
        @if(isset($coverPath) && $coverPath)
            <img src="{{ $coverPath }}" class="cover-bg">
        @elseif($ebook->cover_image_url) <!-- Fallback just in case -->
             <img src="{{ public_path($ebook->cover_image_url) }}" class="cover-bg">
        @endif
        
        <div class="cover-content">
            <div class="brand-logo">/// ANXIPUNK ARŞİVLERİ</div>
            <div class="cover-title">{{ $ebook->title }}</div>
            <div class="cover-volume">CİLT {{ str_pad($ebook->volume_number, 2, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="cover-footer">
            SİBERPUNK KRONİKLERİ KOLEKSİYONU<br>
            ANXIPUNK.ICU TARAFINDAN OLUŞTURULDU
        </div>
    </div>

    <!-- Copyright / Info Page -->
    <div class="info-page">
        <div class="info-content">
            <h2 style="font-size:16pt; margin-bottom: 20px;">SİSTEM VERİSİ</h2>
            <p style="text-indent:0; text-align:center;">
                <strong>Cilt Kimliği:</strong> {{ $ebook->slug }}<br>
                <strong>Oluşturulma Tarihi:</strong> {{ $ebook->created_at->format('Y-m-d H:i:s') }}<br>
                <strong>Kaynak Düğüm:</strong> Anxipunk.icu
            </p>
            <br>
            <p style="text-indent:0; text-align:center; font-size: 10pt; color: #555; max-width: 400px; margin: 0 auto;">
                Bu belge, Neo-Pera sektöründen yetkilendirilmiş anıları içerir. 
                Bu veri akışının izinsiz değiştirilmesi, 
                2084 Bilgi Koruma Yasası'nın ihlalidir.
            </p>
        </div>
    </div>

    <!-- Footer for content pages -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "ANXIPUNK // CİLT {{ $ebook->volume_number }} // SAYFA {PAGE_NUM}";
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 9;
            $color = array(0.4, 0.4, 0.4);
            $y = $pdf->get_height() - 40;
            $x = $pdf->get_width() / 2 - ($fontMetrics->get_text_width($text, $font, $size) / 2);
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>


    <!-- Main Content -->
    <div class="content-wrapper">
        {!! $content !!}
    </div>

</body>
</html>
