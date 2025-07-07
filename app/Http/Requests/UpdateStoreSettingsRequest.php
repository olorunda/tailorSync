<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller/service
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $businessDetail = $this->user()->businessDetail;

        return [
            'store_enabled' => 'boolean',
            'store_slug' => 'nullable|string|max:100|unique:business_details,store_slug,' . $businessDetail->id,
            'store_theme_color' => 'nullable|string|max:20',
            'store_secondary_color' => 'nullable|string|max:20',
            'store_accent_color' => 'nullable|string|max:20',
            'store_description' => 'nullable|string|max:1000',
            'store_banner_image' => 'nullable|image|max:2048',
            'store_featured_categories' => 'nullable|array',
            'store_featured_categories.*' => 'nullable|string|max:100',
            'store_social_links' => 'nullable|array',
            'store_social_links.*' => 'nullable|url',
            'store_announcement' => 'nullable|string|max:500',
            'store_show_featured_products' => 'boolean',
            'store_show_new_arrivals' => 'boolean',
            'store_show_custom_designs' => 'boolean',
        ];
    }
}
