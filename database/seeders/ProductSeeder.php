<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        foreach ($categories as $category) {
            // Published products, roughly half with price tiers.
            Product::factory()
                ->count(6)
                ->recycle($suppliers)
                ->for($category)
                ->create()
                ->each(function (Product $product, int $i) {
                    if ($i % 2 === 0) {
                        $this->addTiers($product);
                    }
                    $this->addPlaceholderImage($product);
                });

            $draft = Product::factory()->draft()->recycle($suppliers)->for($category)->create();
            $this->addPlaceholderImage($draft);
        }

        // A few low stock products so the dashboard widget has data.
        Product::query()->where('status', ProductStatus::Published)->inRandomOrder()->limit(4)
            ->update(['stock_quantity' => 5, 'low_stock_threshold' => 20]);
    }

    private function addTiers(Product $product): void
    {
        $base = (float) $product->base_price;
        $moq = $product->moq;

        PriceTier::create(['product_id' => $product->id, 'min_quantity' => $moq, 'max_quantity' => $moq * 5 - 1, 'unit_price' => $base]);
        PriceTier::create(['product_id' => $product->id, 'min_quantity' => $moq * 5, 'max_quantity' => $moq * 10 - 1, 'unit_price' => round($base * 0.93, -1)]);
        PriceTier::create(['product_id' => $product->id, 'min_quantity' => $moq * 10, 'max_quantity' => null, 'unit_price' => round($base * 0.86, -1)]);
    }

    /** Generates a plain placeholder JPEG with GD so thumbnails and WebP conversions have something to render. */
    private function addPlaceholderImage(Product $product): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $dir = storage_path('app/seed-images');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $path = $dir.'/'.Str::slug($product->sku).'.jpg';
        $img = imagecreatetruecolor(800, 800);
        $hue = crc32($product->sku) % 360;
        [$r, $g, $b] = $this->hsl($hue, 0.35, 0.78);
        imagefill($img, 0, 0, imagecolorallocate($img, $r, $g, $b));
        $text = imagecolorallocate($img, 60, 50, 45);
        imagestring($img, 5, 300, 390, $product->sku, $text);
        imagejpeg($img, $path, 85);
        imagedestroy($img);

        $product->addMedia($path)->preservingOriginal()->toMediaCollection('images');
    }

    private function hsl(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0], $h < 120 => [$x, $c, 0], $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c], $h < 300 => [$x, 0, $c], default => [$c, 0, $x],
        };

        return [(int) (($r + $m) * 255), (int) (($g + $m) * 255), (int) (($b + $m) * 255)];
    }
}
