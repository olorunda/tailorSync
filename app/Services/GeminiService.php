<?php

namespace App\Services;

use App\Services\Interfaces\ImageGeneratorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService implements ImageGeneratorInterface
{
    protected $apiKey;
    protected $model;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    protected $currentOccasion = '';

    /**
     * Generate an image based on the provided parameters
     * Implementation of the ImageGeneratorInterface
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
        return $this->generateStyleImage($colors, $styleName, $description, $designElements, $occasion);
    }

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key','AIzaSyBVpemolFRYQqCfKDiA-xciHJG-JIP78XY');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');

    }

    /**
     * Generate style suggestions based on occasion
     *
     * @param string $occasion The occasion for which style suggestions are needed
     * @param array $additionalContext Additional context like gender, season, etc.
     * @return array|null The style suggestions or null if an error occurred
     */
    public function getStyleSuggestions(string $occasion, array $additionalContext = []): ?array
    {
        try {

            // Store the occasion for later use in image generation
            $this->currentOccasion = $occasion;

            // Build the prompt with the occasion and any additional context
            $prompt = $this->buildPrompt($occasion, $additionalContext);
            // Make the API request to Gemini'
               $response = Http::post(
                $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 800,
                    ]
                ]
            );
            if ($response->successful()) {
                $data = $response->json();

                // Extract the text from the response
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $data['candidates'][0]['content']['parts'][0]['text'];

                    // Parse the text into structured suggestions
                    return $this->parseStyleSuggestions($text);
                }
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error generating style suggestions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Build the prompt for the Gemini API
     *
     * @param string $occasion The occasion
     * @param array $additionalContext Additional context
     * @return string The formatted prompt
     */
    protected function buildPrompt(string $occasion, array $additionalContext): string
    {
        $contextStr = '';

        if (!empty($additionalContext)) {
            foreach ($additionalContext as $key => $value) {
                $contextStr .= ucfirst($key) . ': ' . $value . "\n";
            }
        }

        return "You are a professional fashion stylist. Please suggest clothing styles and design ideas for the following occasion: {$occasion}.\n\n" .
               "Additional context:\n{$contextStr}\n" .
               "Please provide your suggestions in the following format:\n" .
               "1. Style name: [Style name - be concise and catchy]\n" .
               "   Description: [Brief description of the overall style and why it works for this occasion]\n" .
               "   Materials: [List materials separated by commas and each should not be more than 50 characters- be specific and limit to 3-5 key materials]\n" .
               "   Colors: [List colors separated by commas and each should not be more than 50 characters - be specific and limit to 3-5 key colors]\n" .
               "   Design elements: [List key design elements separated by commas and each should not be more than 50 characters - include specific cuts, patterns, or features]\n\n" .
               "2. Style name: [Style name]\n" .
               "   ...\n\n" .
               "Please provide 2-3 distinct style suggestions that would be appropriate for this occasion. Make sure each section is clearly formatted and concise for easy reading.";
    }

    /**
     * Parse the raw text response into structured style suggestions
     *
     * @param string $text The raw text from Gemini API
     * @return array The structured style suggestions
     */
    protected function parseStyleSuggestions(string $text): array
    {
        $suggestions = [];

        // Split the text by numbered items (1., 2., etc.)
        preg_match_all('/\d+\.\s+Style name:.*?(?=\d+\.\s+Style name:|$)/s', $text, $matches);

        if (empty($matches[0])) {
            // If the format doesn't match, return the raw text as a single suggestion
            return [['raw_text' => $text]];
        }

        foreach ($matches[0] as $match) {
            $suggestion = [];

            // Extract style name
            preg_match('/Style name:\s*(.+?)(?=\n|$)/i', $match, $nameMatch);
            $suggestion['name'] = $this->cleanText($nameMatch[1] ?? '');

            // Extract description
            preg_match('/Description:\s*(.+?)(?=\n\s*[A-Za-z]+:|$)/is', $match, $descMatch);
            $suggestion['description'] = $this->cleanText($descMatch[1] ?? '');

            // Extract materials
            preg_match('/Materials:\s*(.+?)(?=\n\s*[A-Za-z]+:|$)/is', $match, $materialsMatch);
            $suggestion['materials'] = $this->cleanText($materialsMatch[1] ?? '');

            // Extract colors
            preg_match('/Colors:\s*(.+?)(?=\n\s*[A-Za-z]+:|$)/is', $match, $colorsMatch);
            $suggestion['colors'] = $this->cleanText($colorsMatch[1] ?? '');

            // Extract design elements
            preg_match('/Design elements:\s*(.+?)(?=\n\s*\d+\.|$)/is', $match, $elementsMatch);
            $suggestion['design_elements'] = $this->cleanText($elementsMatch[1] ?? '');

            // Format design elements as a list if they contain commas or bullet points
            $suggestion['design_elements'] = $this->formatListItems($suggestion['design_elements']);

            // Ensure materials and colors are properly comma-separated for the UI
            $suggestion['materials'] = $this->ensureCommaSeparated($suggestion['materials']);
            $suggestion['colors'] = $this->ensureCommaSeparated($suggestion['colors']);

            // Generate a sample image based on the style information
            if (!empty($suggestion['colors'])) {
                $colorArray = array_map('trim', explode(',', $suggestion['colors']));
                $description = $suggestion['description'] ?? '';
                $designElements = $suggestion['design_elements'] ?? '';
                $occasion = isset($this->currentOccasion) ? $this->currentOccasion : '';
                // Use the ImageGeneratorFactory to get the configured image generator
                $imageGenerator = \App\Services\Factories\ImageGeneratorFactory::create();
                $suggestion['sample_image'] = $imageGenerator->generateImage(
                    $colorArray,
                    $suggestion['name'],
                    $description,
                    $designElements,
                    $occasion
                );
            }

            $suggestions[] = $suggestion;
        }

        return $suggestions;
    }

    /**
     * Clean and normalize text from AI response
     *
     * @param string $text The text to clean
     * @return string The cleaned text
     */
    protected function cleanText(string $text): string
    {
        // Remove any markdown formatting that might be present
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);

        // Remove any extra whitespace
        $text = trim($text);

        // Remove any bullet points at the beginning of lines
        $text = preg_replace('/^[\s-]*•\s*/m', '', $text);
        $text = preg_replace('/^[\s-]*-\s*/m', '', $text);

        return $text;
    }

    /**
     * Format text as list items if it contains commas or bullet points
     *
     * @param string $text The text to format
     * @return string The formatted text
     */
    protected function formatListItems(string $text): string
    {
        // If the text already contains bullet points or numbered items, preserve it
        if (preg_match('/^[\s-]*[•\-\d]+\s+/m', $text)) {
            return $text;
        }

        // If the text contains commas, assume it's a list
        if (strpos($text, ',') !== false) {
            // No need to modify further as the view will handle comma-separated lists
            return $text;
        }

        return $text;
    }

    /**
     * Ensure text is properly comma-separated for UI display
     *
     * @param string $text The text to format
     * @return string The comma-separated text
     */
    protected function ensureCommaSeparated(string $text): string
    {
        // Replace semicolons with commas
        $text = str_replace(';', ',', $text);

        // Replace newlines with commas
        $text = preg_replace('/\n+/', ', ', $text);

        // Replace multiple commas with a single comma
        $text = preg_replace('/,\s*,/', ',', $text);

        // Replace "and" with commas where appropriate
        $text = preg_replace('/,?\s+and\s+/', ', ', $text);

        // Ensure there's a space after each comma
        $text = preg_replace('/,(?!\s)/', ', ', $text);

        // Remove any trailing comma
        $text = rtrim($text, ', ');

        return $text;
    }

    /**
     * Generate an image using Gemini's image generation API
     *
     * @param array $colors Array of color names
     * @param string $styleName The name of the style
     * @param string $description The style description
     * @param string $designElements The design elements
     * @param string $occasion The occasion
     * @return string|null The path to the generated image or null if generation failed
     */
    protected function generateImageWithGemini(array $colors, string $styleName, string $description, string $designElements, string $occasion): ?string
    {
        try {
            // Create a unique filename with a hash to ensure uniqueness
            $hash = substr(md5($styleName . implode(',', $colors) . time()), 0, 8);
            $filename = 'gemini-style-' . $hash . '.png';
            $path = 'design-images/' . $filename;

            // Check if we already have this image
            if (Storage::disk('public')->exists($path)) {
                return $path;
            }

            // Build a prompt for the image generation
            $prompt = $this->buildImageGenerationPrompt($colors, $styleName, $description, $designElements, $occasion).' and briefly describe it.
                                    IMPORTANT :  Image generate should be a resolution  of 842px by 1024px
                                ';

            // Use the correct model for image generation
            $imageGenerationModel = 'gemini-2.0-flash-preview-image-generation';
            $imageGenerationModel = 'gemini-2.0-flash-exp';

            // Make the API request to Gemini for image generation

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-Response-Format' => 'FULL',
            ])->post(
               // 'https://generativelanguage.googleapis.com/v1beta/models/' . $imageGenerationModel . ':generateContent',
                "https://generativelanguage.googleapis.com/v1beta/models/$imageGenerationModel:generateContent",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                'responseModalities' => ['TEXT','IMAGE']
            ]
                ],
            );

            if ($response->successful()) {
                $data = $response->json();

                // Extract the image data from the response
                if (isset($data['candidates'][0]['content']['parts'])) {
                    foreach ($data['candidates'][0]['content']['parts'] as $part) {
                        if (isset($part['inlineData']['data'])) {
                            // This is the base64-encoded image data
                            $imageData = base64_decode($part['inlineData']['data']);

                            // Store the image
                            if (Storage::disk('public')->put($path, $imageData)) {
                                return $path;
                            }
                        }
                    }
                }
            }

            // Log the error if the request failed
            Log::error('Gemini API error for image generation', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error generating image with Gemini', [
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

        $prompt .= " Create a high-quality, professional fashion illustration or design mockup that showcases this style.";
        $prompt .= " The image should be visually appealing and suitable for a fashion catalog or website.";

        return $prompt;
    }

    /**
     * Get a sample image URL based on the style description, colors, and occasion
     * Uses Gemini's image generation API if available, falls back to predefined images
     *
     * @param array $colors Array of color names
     * @param string $styleName The name of the style
     * @param string $description The style description (optional)
     * @param string $designElements The design elements (optional)
     * @param string $occasion The occasion (optional)
     * @return string|null The URL to a sample image or null if no suitable image found
     */
    protected function generateStyleImage(array $colors, string $styleName, string $description = '', string $designElements = '', string $occasion = ''): ?string
    {
        try {
            // First try to generate an image using Gemini's image generation API
            $imageFromGemini = $this->generateImageWithGemini($colors, $styleName, $description, $designElements, $occasion);
            if ($imageFromGemini) {
                return $imageFromGemini;
            }

            // If Gemini image generation fails, fall back to predefined images
            // Map of basic colors to image categories with weighted scores
            $colorCategories = [
                // Formal colors
                'black' => ['formal' => 5, 'business' => 3, 'elegant' => 2],
                'navy' => ['formal' => 4, 'business' => 5, 'elegant' => 3],
                'burgundy' => ['formal' => 5, 'elegant' => 4, 'winter' => 2],
                'maroon' => ['formal' => 5, 'elegant' => 3, 'winter' => 2],
                'charcoal' => ['formal' => 5, 'business' => 4],
                'silver' => ['formal' => 4, 'elegant' => 5, 'wedding' => 3],
                'gold' => ['formal' => 3, 'elegant' => 5, 'wedding' => 4],

                // Business colors
                'gray' => ['business' => 5, 'formal' => 3, 'casual' => 1],
                'grey' => ['business' => 5, 'formal' => 3, 'casual' => 1],
                'blue' => ['business' => 4, 'casual' => 2, 'summer' => 2],
                'dark blue' => ['business' => 5, 'formal' => 3],
                'light blue' => ['business' => 3, 'casual' => 4, 'summer' => 3],

                // Casual colors
                'green' => ['casual' => 4, 'summer' => 3, 'outdoor' => 5],
                'olive' => ['casual' => 5, 'outdoor' => 4, 'autumn' => 3],
                'khaki' => ['casual' => 5, 'outdoor' => 3, 'safari' => 4],
                'brown' => ['casual' => 4, 'autumn' => 5, 'outdoor' => 3],
                'beige' => ['casual' => 5, 'summer' => 3, 'safari' => 4],
                'tan' => ['casual' => 5, 'summer' => 3, 'safari' => 4],
                'teal' => ['casual' => 4, 'summer' => 3],

                // Seasonal colors
                'white' => ['summer' => 4, 'wedding' => 5, 'casual' => 3],
                'cream' => ['wedding' => 4, 'formal' => 3, 'summer' => 3],
                'ivory' => ['wedding' => 5, 'formal' => 3],
                'yellow' => ['summer' => 5, 'casual' => 4, 'spring' => 5],
                'orange' => ['summer' => 4, 'casual' => 5, 'autumn' => 5],
                'red' => ['formal' => 3, 'party' => 4, 'romantic' => 5],
                'pink' => ['romantic' => 5, 'spring' => 4, 'casual' => 3],
                'light pink' => ['romantic' => 5, 'wedding' => 3, 'spring' => 4],
                'purple' => ['elegant' => 4, 'formal' => 3, 'creative' => 5],
                'lavender' => ['spring' => 5, 'romantic' => 4, 'casual' => 3],
                'turquoise' => ['summer' => 5, 'beach' => 5, 'tropical' => 5],
                'coral' => ['summer' => 5, 'beach' => 4, 'tropical' => 4],
                'mint' => ['spring' => 5, 'casual' => 4, 'summer' => 3]
            ];

            // Style keywords to image categories mapping with weighted scores
            $styleKeywords = [
                // Formal occasions
                'wedding' => ['wedding' => 10],
                'bridal' => ['wedding' => 10],
                'groom' => ['wedding' => 10, 'formal' => 5],
                'formal' => ['formal' => 10],
                'ceremony' => ['formal' => 8, 'wedding' => 5],
                'gala' => ['formal' => 9, 'elegant' => 7],
                'black tie' => ['formal' => 10, 'elegant' => 8],
                'tuxedo' => ['formal' => 10],
                'suit' => ['formal' => 7, 'business' => 8],

                // Business settings
                'business' => ['business' => 10],
                'office' => ['business' => 10],
                'professional' => ['business' => 9, 'formal' => 5],
                'corporate' => ['business' => 10],
                'interview' => ['business' => 9, 'formal' => 6],
                'meeting' => ['business' => 8],
                'work' => ['business' => 7, 'casual' => 3],

                // Casual settings
                'casual' => ['casual' => 10],
                'everyday' => ['casual' => 9],
                'relaxed' => ['casual' => 8, 'summer' => 4],
                'comfortable' => ['casual' => 7],
                'streetwear' => ['casual' => 9, 'urban' => 8],
                'athleisure' => ['casual' => 8, 'sport' => 9],
                'jeans' => ['casual' => 9],
                't-shirt' => ['casual' => 9],

                // Seasonal/Weather
                'summer' => ['summer' => 10],
                'spring' => ['spring' => 10, 'summer' => 5],
                'winter' => ['winter' => 10],
                'autumn' => ['autumn' => 10, 'winter' => 5],
                'fall' => ['autumn' => 10, 'winter' => 5],
                'beach' => ['beach' => 10, 'summer' => 8],
                'tropical' => ['beach' => 9, 'summer' => 8],
                'resort' => ['beach' => 8, 'summer' => 7, 'elegant' => 5],
                'vacation' => ['beach' => 7, 'summer' => 8, 'casual' => 6],

                // Special occasions
                'party' => ['party' => 10],
                'cocktail' => ['party' => 8, 'elegant' => 7],
                'evening' => ['elegant' => 8, 'formal' => 6, 'party' => 5],
                'prom' => ['formal' => 8, 'elegant' => 7],
                'graduation' => ['formal' => 7, 'elegant' => 5],
                'birthday' => ['party' => 8, 'casual' => 6],
                'celebration' => ['party' => 7, 'elegant' => 5],

                // Romantic
                'romantic' => ['romantic' => 10],
                'date' => ['romantic' => 9, 'elegant' => 6],
                'anniversary' => ['romantic' => 8, 'elegant' => 7],
                'valentine' => ['romantic' => 10],

                // Active/Sports
                'sport' => ['sport' => 10, 'casual' => 5],
                'athletic' => ['sport' => 10, 'casual' => 5],
                'workout' => ['sport' => 10],
                'gym' => ['sport' => 10],
                'fitness' => ['sport' => 10],
                'outdoor' => ['outdoor' => 10, 'casual' => 6],
                'hiking' => ['outdoor' => 10],
                'adventure' => ['outdoor' => 9, 'casual' => 5],

                // Cultural/Traditional
                'traditional' => ['traditional' => 10],
                'ethnic' => ['traditional' => 9, 'cultural' => 10],
                'cultural' => ['cultural' => 10, 'traditional' => 8],
                'heritage' => ['traditional' => 9, 'cultural' => 8],
                'indigenous' => ['traditional' => 10, 'cultural' => 9],

                // Style eras
                'vintage' => ['vintage' => 10],
                'retro' => ['vintage' => 9, 'retro' => 10],
                '50s' => ['vintage' => 9, 'retro' => 8],
                '60s' => ['vintage' => 9, 'retro' => 8],
                '70s' => ['vintage' => 9, 'retro' => 8],
                '80s' => ['vintage' => 8, 'retro' => 9],
                '90s' => ['vintage' => 7, 'retro' => 10],

                // Other styles
                'bohemian' => ['bohemian' => 10, 'casual' => 5],
                'boho' => ['bohemian' => 10, 'casual' => 5],
                'chic' => ['elegant' => 8, 'formal' => 5],
                'minimalist' => ['minimalist' => 10, 'modern' => 8],
                'preppy' => ['preppy' => 10, 'casual' => 6],
                'punk' => ['alternative' => 10, 'urban' => 7],
                'gothic' => ['alternative' => 9, 'dark' => 10],
                'hipster' => ['urban' => 9, 'casual' => 7],
                'luxury' => ['elegant' => 10, 'formal' => 7],
                'streetstyle' => ['urban' => 10, 'casual' => 8]
            ];

            // Occasion to category mapping
            $occasionCategories = [
                'Wedding' => ['wedding' => 10, 'formal' => 7, 'elegant' => 6],
                'Formal Dinner' => ['formal' => 10, 'elegant' => 8],
                'Business Meeting' => ['business' => 10, 'formal' => 5],
                'Casual Outing' => ['casual' => 10],
                'Beach Party' => ['beach' => 10, 'summer' => 8, 'party' => 6],
                'Graduation' => ['formal' => 8, 'elegant' => 6],
                'Birthday Party' => ['party' => 10, 'casual' => 7],
                'Anniversary' => ['romantic' => 9, 'elegant' => 8],
                'Religious Ceremony' => ['formal' => 9, 'traditional' => 8],
                'Festival' => ['casual' => 8, 'bohemian' => 7, 'summer' => 6],
                'Sports Event' => ['casual' => 9, 'sport' => 8],
                'Date Night' => ['romantic' => 10, 'elegant' => 7]
            ];

            // Initialize category scores
            $categoryScores = [
                'formal' => 0,
                'business' => 0,
                'casual' => 0,
                'summer' => 0,
                'winter' => 0,
                'spring' => 0,
                'autumn' => 0,
                'elegant' => 0,
                'party' => 0,
                'romantic' => 0,
                'wedding' => 0,
                'beach' => 0,
                'outdoor' => 0,
                'sport' => 0,
                'traditional' => 0,
                'cultural' => 0,
                'vintage' => 0,
                'retro' => 0,
                'bohemian' => 0,
                'urban' => 0,
                'minimalist' => 0,
                'preppy' => 0,
                'alternative' => 0,
                'dark' => 0,
                'safari' => 0,
                'tropical' => 0,
                'modern' => 0
            ];

            // Score from occasion (highest priority)
            if (!empty($occasion) && isset($occasionCategories[$occasion])) {
                foreach ($occasionCategories[$occasion] as $cat => $score) {
                    $categoryScores[$cat] += $score * 2; // Double weight for occasion
                }
            }

            // Score from style name
            $lowerStyleName = strtolower($styleName);
            foreach ($styleKeywords as $keyword => $categories) {
                if (strpos($lowerStyleName, $keyword) !== false) {
                    foreach ($categories as $cat => $score) {
                        $categoryScores[$cat] += $score;
                    }
                }
            }

            // Score from description
            $lowerDescription = strtolower($description);
            foreach ($styleKeywords as $keyword => $categories) {
                if (strpos($lowerDescription, $keyword) !== false) {
                    foreach ($categories as $cat => $score) {
                        $categoryScores[$cat] += $score * 0.7; // 70% weight for description
                    }
                }
            }

            // Score from design elements
            $lowerElements = strtolower($designElements);
            foreach ($styleKeywords as $keyword => $categories) {
                if (strpos($lowerElements, $keyword) !== false) {
                    foreach ($categories as $cat => $score) {
                        $categoryScores[$cat] += $score * 0.5; // 50% weight for design elements
                    }
                }
            }

            // Score from colors
            foreach ($colors as $color) {
                $color = strtolower(trim($color));

                // Check for exact color match
                if (isset($colorCategories[$color])) {
                    foreach ($colorCategories[$color] as $cat => $score) {
                        $categoryScores[$cat] += $score * 0.8; // 80% weight for colors
                    }
                    continue;
                }

                // Check for compound colors like "light blue", "dark green"
                foreach ($colorCategories as $baseColor => $categories) {
                    if (strpos($color, $baseColor) !== false) {
                        foreach ($categories as $cat => $score) {
                            $categoryScores[$cat] += $score * 0.8; // 80% weight for colors
                        }
                        break;
                    }
                }
            }

            // Find the category with the highest score
            $maxScore = 0;
            $category = 'casual'; // Default

            foreach ($categoryScores as $cat => $score) {
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $category = $cat;
                }
            }

            // Create a unique filename with a hash to ensure uniqueness
            $hash = substr(md5($styleName . implode(',', $colors)), 0, 8);
            $filename = 'style-' . $category . '-' . $hash . '.jpg';

            // Check if we already have this image
            $path = 'design-images/' . $filename;
            if (Storage::disk('public')->exists($path)) {
                return $path;
            }

            // First try to generate an image with Gemini
            try {
                // Create a safe prompt for Gemini that avoids policy violations
                $colorsList = implode(', ', array_slice($colors, 0, 3)); // Limit to 3 colors to avoid overwhelming
                $safePrompt = "Create a simple, abstract fashion design image for a {$category} style using these colors: {$colorsList}. The image should be a clean, professional representation suitable for a fashion catalog.";
                $imageGenerationModel = 'gemini-2.0-flash-preview-image-generation';
                // Make the API request to Gemini for image generation

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $this->apiKey,
                    'X-Goog-Response-Format' => 'FULL',
                ])->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/' . $imageGenerationModel . ':generateContent',
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $safePrompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseModalities' => ['TEXT','IMAGE']
                        ]
                    ],
                );
                if ($response->successful()) {
                    $data = $response->json();

                    // Check if we have an image in the response
                    if (isset($data['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
                        $imageData = base64_decode($data['candidates'][0]['content']['parts'][0]['inlineData']['data']);

                        // Store the image
                        Storage::disk('public')->put($path, $imageData);
                        return $path;
                    }
                } else {
                    // Log the error but continue to fallback method
                    Log::error('Gemini API error for image generation', [
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                }
            } catch (\Exception $e) {
                // Log the error but continue to fallback method
                Log::error('Error with Gemini image generation', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Fallback to Unsplash images if Gemini fails
            $sampleImagesMap = [
                'formal' => [
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Man in suit
                    'https://images.unsplash.com/photo-1555069519-127aadedf1ee?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Formal dress
                    'https://images.unsplash.com/photo-1553808373-2c3a0e1ea0bf?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Formal attire
                ],
                'business' => [
                    'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Business attire
                    'https://images.unsplash.com/photo-1580913428023-02c695666d61?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Professional woman
                    'https://images.unsplash.com/photo-1600486913747-55e5470d6f40?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Business man
                ],
                'casual' => [
                    'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Casual outfit
                    'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Street style
                    'https://images.unsplash.com/photo-1551232864-3f0890e580d9?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Casual look
                ],
                'summer' => [
                    'https://images.unsplash.com/photo-1469334031218-e382a71b716b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Summer dress
                    'https://images.unsplash.com/photo-1517104052305-a8e5d5e3f2b3?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Summer outfit
                    'https://images.unsplash.com/photo-1590999659195-e64a988eaf6f?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Summer style
                ],
                'winter' => [
                    'https://images.unsplash.com/photo-1520975916090-3105956dac38?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Winter coat
                    'https://images.unsplash.com/photo-1548883354-94bcfe321cbb?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Winter outfit
                    'https://images.unsplash.com/photo-1576871337622-98d48d1cf531?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Winter style
                ],
                'elegant' => [
                    'https://images.unsplash.com/photo-1490707967831-1fd9b48e40e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Elegant dress
                    'https://images.unsplash.com/photo-1566174053879-31528523f8ae?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Elegant outfit
                    'https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Elegant style
                ],
                'party' => [
                    'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Party dress
                    'https://images.unsplash.com/photo-1533659828870-95ee305cee3e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Party outfit
                    'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Party style
                ],
                'romantic' => [
                    'https://images.unsplash.com/photo-1502727135886-df285cc8379f?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Romantic dress
                    'https://images.unsplash.com/photo-1596815064285-45ed8a9c0463?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Romantic outfit
                    'https://images.unsplash.com/photo-1504439904031-93ded9f93e4e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Romantic style
                ],
                'wedding' => [
                    'https://images.unsplash.com/photo-1494955870715-e3e68b86d16c?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Wedding dress
                    'https://images.unsplash.com/photo-1550005809-91ad75fb315f?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Wedding attire
                    'https://images.unsplash.com/photo-1595407753234-0882f1e77954?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Wedding style
                ],
                'beach' => [
                    'https://images.unsplash.com/photo-1570976447640-ac859a223c39?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Beach outfit
                    'https://images.unsplash.com/photo-1570976447440-5e7e8a5b3c8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Beach style
                    'https://images.unsplash.com/photo-1544961371-516024f8e267?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Beach wear
                ],
                'outdoor' => [
                    'https://images.unsplash.com/photo-1551632811-561732d1e306?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Outdoor gear
                    'https://images.unsplash.com/photo-1548345680-f5475ea5df84?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Hiking outfit
                    'https://images.unsplash.com/photo-1551632436-cbf6d9df0852?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Adventure wear
                ],
                'sport' => [
                    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Athletic wear
                    'https://images.unsplash.com/photo-1518459031867-a89b944bffe4?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Sports outfit
                    'https://images.unsplash.com/photo-1571731956672-f2b94d7dd0cb?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Fitness attire
                ],
                'traditional' => [
                    'https://images.unsplash.com/photo-1511108690759-009324a90311?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Traditional dress
                    'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Cultural attire
                    'https://images.unsplash.com/photo-1596033389700-35e8ce3fa5dc?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Traditional outfit
                ],
                'vintage' => [
                    'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Vintage style
                    'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Retro outfit
                    'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Vintage look
                ],
                'bohemian' => [
                    'https://images.unsplash.com/photo-1537226972765-9b5c0f3df5fe?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Boho style
                    'https://images.unsplash.com/photo-1509087859087-a384654eca4d?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Bohemian outfit
                    'https://images.unsplash.com/photo-1558301211-0d8c8ddee6ec?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Boho look
                ],
                'spring' => [
                    'https://images.unsplash.com/photo-1556139943-4bdca53adf1e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Spring outfit
                    'https://images.unsplash.com/photo-1557409518-691ebcd96038?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Spring style
                    'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Spring look
                ],
                'autumn' => [
                    'https://images.unsplash.com/photo-1537261131436-2ad5fb82f522?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Autumn outfit
                    'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Fall style
                    'https://images.unsplash.com/photo-1511401139252-f158d3209c17?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Autumn look
                ],
                'urban' => [
                    'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Urban style
                    'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Street fashion
                    'https://images.unsplash.com/photo-1523398002811-999ca8dec234?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Urban look
                ],
                'minimalist' => [
                    'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Minimalist style
                    'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Clean look
                    'https://images.unsplash.com/photo-1604176424472-9d7e10f2f17d?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Simple outfit
                ],
                'preppy' => [
                    'https://images.unsplash.com/photo-1552374196-c4e7ffc6e126?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Preppy style
                    'https://images.unsplash.com/photo-1589992896844-9b720813d1cb?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Classic preppy
                    'https://images.unsplash.com/photo-1600091166971-7f9fadd2bc07?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Preppy look
                ],
                'alternative' => [
                    'https://images.unsplash.com/photo-1536766768598-e09213fdcf22?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Alternative style
                    'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Edgy look
                    'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Punk style
                ],
                'dark' => [
                    'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Dark style
                    'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Gothic look
                    'https://images.unsplash.com/photo-1550928431-ee0ec6db30d3?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Dark fashion
                ],
                'safari' => [
                    'https://images.unsplash.com/photo-1578996953841-b187dbe4bc8a?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Safari style
                    'https://images.unsplash.com/photo-1581497396202-5645e76a3a8e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Safari outfit
                    'https://images.unsplash.com/photo-1551651767-d5ffbdd04088?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Safari look
                ],
                'tropical' => [
                    'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Tropical style
                    'https://images.unsplash.com/photo-1539533113208-f6df8cc8b543?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Tropical outfit
                    'https://images.unsplash.com/photo-1517686748843-bb360cfc62b3?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Tropical look
                ],
                'modern' => [
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Modern style
                    'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80', // Contemporary look
                    'https://images.unsplash.com/photo-1554412933-514a83d2f3c8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'  // Modern fashion
                ]
            ];

            // Get the image URLs for the category
            $imageUrls = $sampleImagesMap[$category] ?? $sampleImagesMap['casual'];

            // Select a random image from the category
            $imageUrl = $imageUrls[array_rand($imageUrls)];

            // Download and store the image
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent) {
                Storage::disk('public')->put($path, $imageContent);
                return $path;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error generating style image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
}
