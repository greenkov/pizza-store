<?php

namespace App\Console\Commands\Traits;

use Illuminate\Console\Concerns\InteractsWithIO;

trait GetArrayOptionTrait
{
    use InteractsWithIO;

    /**
     * @param string $key
     * @param array|null $default
     *
     * @return array|null
     */
    private function getStringArrayOption(string $key, ?array $default = null): ?array
    {
        $value = $this->option($key);
        if ($value === null) {
            return $default;
        }

        return array_values(array_filter(array_map(fn ($item) => trim($item), explode(',', $value))));
    }

    /**
     * @param string $key
     * @param array|null $default
     *
     * @return array|null
     */
    private function getIntArrayOption(string $key, ?array $default = null): ?array
    {
        $value = $this->option($key);
        if ($value === null) {
            return $default;
        }

        $stringArray = array_values(array_filter(array_map(fn ($item) => trim($item), explode(',', $value))));

        return array_map(fn ($item) => (int) $item, $stringArray);
    }
}
