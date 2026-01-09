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
            color: #111;
            line-height: 1.6;
        }
        
        /* --- Cover Page --- */
        .cover-page {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #050505;
            color: #fff;
            text-align: center;
            overflow: hidden;
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

        /* --- Interior Pages --- */
        .content-wrapper {
            margin: 2.5cm 2cm;
            font-size: 11pt;
            text-align: justify;
        }
        
        /* Force DejaVu Sans for Turkish Support on EVERYTHING */
        * {
            font-family: 'DejaVu Sans', sans-serif !important;
        }
        
        /* Headers */
        h1 {
            color: #000;
            font-size: 24pt;
            text-transform: uppercase;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-top: 50px; /* Space before chapter start */
            margin-bottom: 30px;
            page-break-before: always;
            text-align: left;
        }
        /* Style the ID within titles if present */
        h1 span.id-marker {
            font-size: 10pt;
            color: #666;
            float: right;
            margin-top: 15px;
        }

        h2, h3 {
            color: #333;
            margin-top: 20px;
        }

        p {
            margin-bottom: 15px;
            text-indent: 20px;
        }

        /* Images */
        img {
            max-width: 100%;
            height: auto;
        }
        .part-illustration {
            text-align: center;
            margin: 30px 0;
            page-break-inside: avoid;
        }
        .part-illustration img {
            border: 2px solid #000;
            box-shadow: 5px 5px 0px #ccc;
        }

        /* Footer / Page Numbers */
        .page-footer {
            position: fixed;
            bottom: 30px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            margin: 0 2cm;
            padding-top: 10px;
        }
        .page-number:after {
            content: counter(page);
        }

        /* Anxipunk Special Formatting */
        hr.part-divider {
            border: 0;
            height: 1px;
            background: #333;
            background-image: linear-gradient(to right, #ccc, #333, #ccc);
            margin: 40px 0;
        }
        
        /* Info Page styling */
        .info-page {
            page-break-after: always;
            display: table;
            width: 100%;
            height: 800px; /* Approximation of full page height context */
        }
        .info-content {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 50px;
        }
    </style>
</head>
<body>

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
