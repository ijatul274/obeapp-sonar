<?php

/**
 * Sekali jalan: menghasilkan ikon PWA OBE (latar merah #D04747, teks "OBE"
 * putih Arial Black). Jalankan: php scripts/gen-pwa-icons.php
 */
$font = 'C:/Windows/Fonts/ariblk.ttf';
$outDir = __DIR__.'/../public/icons';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$red = [0xD0, 0x47, 0x47];
$white = [0xFF, 0xFF, 0xFF];

/**
 * @param  array{0:int,1:int,2:int}  $bg
 * @param  array{0:int,1:int,2:int}  $fg
 */
function makeIcon(string $path, int $size, float $textRatio, string $font, array $bg, array $fg): void
{
    $img = imagecreatetruecolor($size, $size);
    $bgColor = imagecolorallocate($img, $bg[0], $bg[1], $bg[2]);
    $fgColor = imagecolorallocate($img, $fg[0], $fg[1], $fg[2]);
    imagefilledrectangle($img, 0, 0, $size, $size, $bgColor);

    $text = 'OBE';
    // Cari ukuran font terbesar agar lebar teks ≈ textRatio dari ukuran ikon.
    $target = $size * $textRatio;
    $fontSize = $size;
    for ($fs = $size; $fs > 4; $fs -= 1) {
        $box = imagettfbbox($fs, 0, $font, $text);
        $w = abs($box[2] - $box[0]);
        if ($w <= $target) {
            $fontSize = $fs;
            break;
        }
    }

    $box = imagettfbbox($fontSize, 0, $font, $text);
    $w = abs($box[2] - $box[0]);
    $h = abs($box[7] - $box[1]);
    // Baseline: tempatkan puncak glyph di ($size-$h)/2, lalu turunkan ke baseline.
    $x = (int) (($size - $w) / 2 - $box[0]);
    $y = (int) (($size - $h) / 2 - $box[7]);

    imagettftext($img, $fontSize, 0, $x, $y, $fgColor, $font, $text);

    imagepng($img, $path);
    echo "wrote $path ({$size}px)\n";
}

makeIcon($outDir.'/icon-192.png', 192, 0.72, $font, $red, $white);
makeIcon($outDir.'/icon-512.png', 512, 0.72, $font, $red, $white);
// Maskable: teks lebih kecil agar berada dalam safe-zone 80%.
makeIcon($outDir.'/icon-maskable-512.png', 512, 0.52, $font, $red, $white);
makeIcon($outDir.'/apple-touch-icon.png', 180, 0.70, $font, $red, $white);

echo "DONE\n";
