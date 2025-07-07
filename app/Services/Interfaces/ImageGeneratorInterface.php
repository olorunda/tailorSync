<?php

namespace App\Services\Interfaces;

interface ImageGeneratorInterface
{
    /**
     * Generate an image based on the provided parameters
     *
     * @param array $colors Array of color names
     * @param string $styleName The name of the style
     * @param string $description The style description
     * @param string $designElements The design elements
     * @param string $occasion The occasion
     * @return string|null The path to the generated image or null if generation failed
     */
    public function generateImage(array $colors, string $styleName, string $description = '', string $designElements = '', string $occasion = ''): ?string;
}
