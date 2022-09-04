<?php

declare(strict_types = 1);

namespace App\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigFunctions extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this, 'config']),
            new TwigFunction('is_file', [$this, 'is_file']),
            new TwigFunction('base_url', [$this, 'base_url']),
        ];
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }

    public function base_url(string $path = ''): string
    {
        return base_url($path);
    }

    public function is_file(string $path): bool
    {
        return is_file($path);
    }
}
