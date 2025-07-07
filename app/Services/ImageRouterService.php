<?php

namespace App\Services;

use App\Services\Interfaces\ImageGeneratorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageRouterService implements ImageGeneratorInterface
{
    protected $apiKey;
    protected $baseUrl = 'https://api.imagerouter.io/v1/openai/images/generations';
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.imagerouter.api_key', 'f03aacb31e77283a4e26915311145f548c6c7e9c9da931259e17ae5432aa3146');
        $this->model = config('services.imagerouter.model', 'google/gemini-2.0-flash-exp:free');
    }

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
    public function generateImage(array $colors, string $styleName, string $description = '', string $designElements = '', string $occasion = ''): ?string
    {

        try {
            // Create a unique filename with a hash to ensure uniqueness
            $hash = substr(md5($styleName . implode(',', $colors) . time()), 0, 8);
            $filename = 'imagerouter-style-' . $hash . '.png';
            $path = 'design-images/' . $filename;

            // Check if we already have this image
            if (Storage::disk('public')->exists($path)) {
                return $path;
            }

            // Build a prompt for the image generation
            $prompt = $this->buildImageGenerationPrompt($colors, $styleName, $description, $designElements, $occasion);

            // Make the API request to ImageRouter.io
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'prompt' => $prompt,
                'model' => $this->model,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Extract the image URL from the response
                if (isset($data['data'][0]['url'])) {
                    $imageUrl = $data['data'][0]['url'];

                    // Download the image from the URL
                    $imageResponse = Http::get($imageUrl);
                    if ($imageResponse->successful()) {
                        $imageData = $imageResponse->body();

                        // Store the image
                        if (Storage::disk('public')->put($path, $imageData)) {
                            return $path;
                        }
                    }
                }
            }

            // Log the error if the request failed
            Log::error('ImageRouter.io API error for image generation', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error generating image with ImageRouter.io', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Build a prompt for image generation
     *
     * @param array $colors Array of color names
     * @param string $styleName The name of the style
     * @param string $description The style description
     * @param string $designElements The design elements
     * @param string $occasion The occasion
     * @return string The prompt for image generation
     */
    protected function buildImageGenerationPrompt(array $colors, string $styleName, string $description, string $designElements, string $occasion): string
    {
        $colorStr = implode(', ', $colors);

        $prompt = "Generate a fashion design image for a style called \"$styleName\".";

        if (!empty($occasion)) {
            $prompt .= " This style is appropriate for a $occasion.";
        }

        if (!empty($description)) {
            $prompt .= " Style description: $description.";
        }

        if (!empty($colorStr)) {
            $prompt .= " The color palette includes: $colorStr.";
        }

        if (!empty($designElements)) {
            $prompt .= " Key design elements: $designElements.";
        }

        $prompt .= " IMPORTANT: Image should be a resolution of 842px by 1024px.";

        return $prompt;
    }
}
