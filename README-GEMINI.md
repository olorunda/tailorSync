# Google Gemini Integration for Style Suggestions

This application now includes integration with Google Gemini AI to provide style suggestions based on selected occasions.

## Setup Instructions

1. Obtain a Google Gemini API key from the [Google AI Studio](https://makersuite.google.com/app/apikey)

2. Add the following environment variables to your `.env` file:

```
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-pro
```

## How to Use

1. When creating a new design, select an occasion from the dropdown menu
2. Click the "Get Suggestions" button
3. Gemini will generate style suggestions based on the selected occasion
4. You can apply any of the suggestions to your design by clicking the "Apply This Style" button

## Features

- AI-powered style suggestions based on occasions
- Suggestions include style name, description, materials, colors, and design elements
- One-click application of suggestions to your design
- Automatic addition of materials and tags from suggestions

## Troubleshooting

If you encounter any issues:

1. Make sure your GEMINI_API_KEY is correctly set in the .env file
2. Check the application logs for any error messages
3. Ensure you have an active internet connection

## Privacy Note

When you use the style suggestion feature, your selected occasion is sent to Google's Gemini API. No personal information or client data is shared with Google.
