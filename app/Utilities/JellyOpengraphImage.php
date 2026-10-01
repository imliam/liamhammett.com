<?php

namespace App\Utilities;

use GdImage;

/**
 * Social share images in the site's "Jelly" style: a peach dotted page, a chunky
 * outlined card with a hard shadow, and an orange goo blob holding the avatar.
 *
 * Drawn with GD at 3x and scaled down, since GD's shapes aren't anti-aliased.
 */
class JellyOpengraphImage
{
    public const WIDTH = 1280;

    public const HEIGHT = 640;

    private const SCALE = 3;

    private const INK = '#2a1810';

    private const INK_SOFT = '#5b4336';

    private const PEACH = '#ffeede';

    private const DOTS = '#f6d2b4';

    private const CREAM = '#fffaf4';

    private const ORANGE = '#ff6a1f';

    private const YELLOW = '#ffd644';

    private GdImage $image;

    public function __construct(
        private string $title,
        private ?string $kicker = null,
        private ?string $date = null,
        private ?string $description = null,
        private ?string $sticker = null,
    ) {}

    public function render(): string
    {
        $this->image = imagecreatetruecolor(self::WIDTH * self::SCALE, self::HEIGHT * self::SCALE);
        imagealphablending($this->image, true);

        $this->background();
        $this->blob();
        $this->card();

        if ($this->sticker) {
            $this->pill(905, 92, $this->sticker, $this->font('JetBrainsMono-Bold'), 22, self::YELLOW, self::INK);
        }

        $output = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled($output, $this->image, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, self::WIDTH * self::SCALE, self::HEIGHT * self::SCALE);

        ob_start();
        imagepng($output, null, 9);

        return ob_get_clean();
    }

    public function save(string $path): string
    {
        $png = $this->render();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $png);

        return $png;
    }

    private function background(): void
    {
        imagefilledrectangle($this->image, 0, 0, self::WIDTH * self::SCALE, self::HEIGHT * self::SCALE, $this->color(self::PEACH));

        for ($x = 14; $x < self::WIDTH; $x += 28) {
            for ($y = 14; $y < self::HEIGHT; $y += 28) {
                $this->circle($x, $y, 1.6, self::DOTS);
            }
        }
    }

    // The goo blob is a union of circles: shadow, then outline, then fill, so the outline wraps the whole shape
    private function blob(): void
    {
        $lumps = [
            [1045, 325, 226],
            [1190, 185, 82],
            [1176, 470, 104],
            [905, 498, 70],
            [878, 212, 46],
            [962, 572, 52],
            [956, 640, 32],
        ];

        foreach ([[12, 12, 7, self::INK], [0, 0, 7, self::INK], [0, 0, 0, self::ORANGE]] as [$dx, $dy, $grow, $color]) {
            foreach ($lumps as [$x, $y, $r]) {
                $this->circle($x + $dx, $y + $dy, $r + $grow, $color);
            }
        }

        // A stray droplet that's broken away
        foreach ([[5, 5, 5, self::INK], [0, 0, 5, self::INK], [0, 0, 0, self::ORANGE]] as [$dx, $dy, $grow, $color]) {
            $this->circle(860 + $dx, 590 + $dy, 16 + $grow, $color);
        }

        $this->avatar(1045, 325, 150);
    }

    private function avatar(float $cx, float $cy, float $r): void
    {
        $this->circle($cx + 8, $cy + 8, $r + 7, self::INK);
        $this->circle($cx, $cy, $r + 7, self::INK);

        $source = imagecreatefromjpeg(public_path('images/avatar.jpg'));
        $size = (int) round($r * 2 * self::SCALE);
        $avatar = imagecreatetruecolor($size, $size);
        imagecopyresampled($avatar, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

        $left = (int) round(($cx - $r) * self::SCALE);
        $top = (int) round(($cy - $r) * self::SCALE);
        $radius = $size / 2;

        // Copy only the pixels inside the circle
        for ($y = 0; $y < $size; $y++) {
            $span = sqrt(max(0, $radius ** 2 - ($y + 0.5 - $radius) ** 2));
            $from = (int) floor($radius - $span);
            $to = (int) ceil($radius + $span);
            if ($to > $from) {
                imagecopy($this->image, $avatar, $left + $from, $top + $y, $from, $y, $to - $from, 1);
            }
        }
    }

    private function card(): void
    {
        [$x1, $y1, $x2, $y2] = [56, 64, 812, 572];

        $this->roundedRect($x1 + 14, $y1 + 14, $x2 + 14, $y2 + 14, 44, self::INK);
        $this->roundedRect($x1, $y1, $x2, $y2, 44, self::INK);
        $this->roundedRect($x1 + 6, $y1 + 6, $x2 - 6, $y2 - 6, 38, self::CREAM);

        $left = $x1 + 52;
        $width = $x2 - $x1 - 104;

        // Kicker pills
        $pillX = $left;
        if ($this->kicker) {
            $pillX += $this->pill($pillX, $y1 + 48, mb_strtoupper($this->kicker), $this->font('JetBrainsMono-Bold'), 18, self::ORANGE, '#ffffff') + 12;
        }
        if ($this->date) {
            $this->pill($pillX, $y1 + 48, mb_strtoupper($this->date), $this->font('JetBrainsMono-Bold'), 18, self::CREAM, self::INK);
        }

        // Footer: logo blob and the domain
        $footerY = $y2 - 64;
        $this->circle($left + 26 + 4, $footerY + 4, 26 + 3, self::INK);
        $this->circle($left + 26, $footerY, 26 + 3, self::INK);
        $this->circle($left + 26, $footerY, 26, self::ORANGE);
        $this->text('L', $this->ostrich(), 30, $left + 26, $footerY + 13, '#ffffff', center: true, bold: 1.2);
        $this->text('liamhammett.com', $this->font('Figtree-ExtraBold'), 26, $left + 68, $footerY + 11, self::INK);

        // Title: as big as fits between the pills and the footer
        $titleTop = $y1 + 120;
        $titleBottom = $footerY - 50 - ($this->description ? 80 : 0);
        $title = mb_strtoupper($this->title);

        foreach ([104, 96, 88, 80, 72, 64, 58, 52, 46] as $size) {
            $lines = $this->wrap($title, $this->ostrich(), $size, $width);
            $lineHeight = $size * 1.32;
            if (count($lines) * $lineHeight <= $titleBottom - $titleTop) {
                break;
            }
        }

        $baseline = $titleTop + $size * 1.05;
        foreach ($lines as $line) {
            // A hard orange offset behind faux-bold ink, like the headings on the site
            $this->text($line, $this->ostrich(), $size, $left + $size * 0.06, $baseline + $size * 0.06, self::ORANGE, bold: $size * 0.03);
            $this->text($line, $this->ostrich(), $size, $left, $baseline, self::INK, bold: $size * 0.03);
            $baseline += $lineHeight;
        }

        if ($this->description) {
            $lines = array_slice($this->wrap($this->description, $this->font('Figtree-SemiBold'), 22, $width), 0, 2);
            $y = $baseline - $lineHeight + 58;
            foreach ($lines as $line) {
                $this->text($line, $this->font('Figtree-SemiBold'), 22, $left, $y, self::INK_SOFT);
                $y += 34;
            }
        }
    }

    // A rounded pill with an ink outline; returns its width
    private function pill(float $x, float $y, string $text, string $font, float $size, string $background, string $color): float
    {
        $box = $this->measure($text, $font, $size);
        $height = $size * 2.1;
        $width = $box + $size * 1.6;

        $this->roundedRect($x + 4, $y + 4, $x + $width + 4, $y + $height + 4, $height / 2, self::INK);
        $this->roundedRect($x, $y, $x + $width, $y + $height, $height / 2, self::INK);
        $this->roundedRect($x + 3, $y + 3, $x + $width - 3, $y + $height - 3, $height / 2 - 3, $background);
        $this->text($text, $font, $size, $x + $width / 2, $y + $height / 2 + $size * 0.38, $color, center: true);

        return $width;
    }

    private function wrap(string $text, string $font, float $size, float $width): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";
            if ($line !== '' && $this->measure($candidate, $font, $size) > $width) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }

        return [...$lines, $line];
    }

    private function measure(string $text, string $font, float $size): float
    {
        $box = imagettfbbox($size * self::SCALE, 0, $font, $text);

        return ($box[2] - $box[0]) / self::SCALE;
    }

    private function text(string $text, string $font, float $size, float $x, float $baseline, string|int $color, bool $center = false, float $bold = 0): void
    {
        if ($center) {
            $x -= $this->measure($text, $font, $size) / 2;
        }

        // Faux bold: stamp the text around two small rings, fine enough that the edges stay smooth
        $offsets = [[0, 0]];
        if ($bold > 0) {
            foreach ([$bold, $bold / 2] as $radius) {
                for ($i = 0; $i < 16; $i++) {
                    $offsets[] = [cos($i * M_PI / 8) * $radius, sin($i * M_PI / 8) * $radius];
                }
            }
        }

        foreach ($offsets as [$dx, $dy]) {
            imagettftext($this->image, $size * self::SCALE, 0, (int) round(($x + $dx) * self::SCALE), (int) round(($baseline + $dy) * self::SCALE), $color = is_int($color) ? $color : $this->color($color), $font, $text);
        }
    }

    private function circle(float $cx, float $cy, float $r, string $color): void
    {
        $d = (int) round($r * 2 * self::SCALE);
        imagefilledellipse($this->image, (int) round($cx * self::SCALE), (int) round($cy * self::SCALE), $d, $d, $this->color($color));
    }

    private function roundedRect(float $x1, float $y1, float $x2, float $y2, float $r, string $color): void
    {
        $r = min($r, ($x2 - $x1) / 2, ($y2 - $y1) / 2);
        $s = self::SCALE;
        $c = $this->color($color);

        imagefilledrectangle($this->image, (int) round(($x1 + $r) * $s), (int) round($y1 * $s), (int) round(($x2 - $r) * $s), (int) round($y2 * $s), $c);
        imagefilledrectangle($this->image, (int) round($x1 * $s), (int) round(($y1 + $r) * $s), (int) round($x2 * $s), (int) round(($y2 - $r) * $s), $c);

        foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as [$cx, $cy]) {
            $this->circle($cx, $cy, $r, $color);
        }
    }

    private function color(string $hex): int
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return imagecolorallocate($this->image, $r, $g, $b);
    }

    private function font(string $name): string
    {
        return resource_path("fonts/opengraph/{$name}.ttf");
    }

    private function ostrich(): string
    {
        return public_path('fonts/ostrich-sans-rounded.ttf');
    }
}
