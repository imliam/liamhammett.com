<?php

use Illuminate\Support\Arr;

function random_rotation(bool $small = false): string
{
    if ($small) {
        return Arr::random(['-rotate-1', 'rotate-none', 'rotate-1']);
    }

    return Arr::random(['-rotate-3', '-rotate-2', '-rotate-1', 'rotate-1', 'rotate-2', 'rotate-3']);
}

function svg(string $path): string
{
    $path = str_replace('.', '/', $path);
    $path = str_replace('.svg', '', $path);

    return file_get_contents(base_path("resources/icons/{$path}.svg"));
}
