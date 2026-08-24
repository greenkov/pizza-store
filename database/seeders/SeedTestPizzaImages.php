<?php

namespace Database\Seeders;

use App\Models\PizzaPreset;
use File;
use Illuminate\Database\Seeder;
use Storage;

class SeedTestPizzaImages extends Seeder
{
    private const string SOURCE_DIR = 'test_images';

    public function run(): void
    {
        $disk = Storage::disk('public');
        $files = File::files(storage_path(self::SOURCE_DIR));

        if ($files === []) {
            $this->command->warn('No images in storage/'.self::SOURCE_DIR);

            return;
        }

        $paths = [];

        foreach ($files as $file) {
            $path = PizzaPreset::IMAGE_PATH.$file->getFilename();

            if (! $disk->exists($path)) {
                $stream = fopen($file->getPathname(), 'r');
                $disk->writeStream($path, $stream);
                fclose($stream);
            }

            $paths[] = $path;
        }

        PizzaPreset::whereNull('image_path')->each(
            fn (PizzaPreset $preset) => $preset->update([
                'image_path' => fake()->randomElement($paths),
            ])
        );
    }
}
