<?php

namespace App\Services\Factories;

use App\Services\GeminiService;
use App\Services\ImageRouterService;
use App\Services\Interfaces\ImageGeneratorInterface;
use Illuminate\Support\Facades\App;

class ImageGeneratorFactory
{
    /**
     * Create an instance of the configured image generator service
     *
     * @return ImageGeneratorInterface
     */
    public static function create(): ImageGeneratorInterface
    {
        $provider = config('services.image_generator.provider', 'imagerouter');

        return match ($provider) {
            'gemini' => App::make(GeminiService::class),
            'imagerouter' => App::make(ImageRouterService::class),
            default => App::make(ImageRouterService::class), // Default to ImageRouter
        };
    }
}
