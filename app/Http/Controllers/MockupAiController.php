<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MockupAiController extends Controller
{
    public function generate(Request $request)
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'min:3', 'max:800'],
            'style_tab' => ['nullable', 'string', 'max:40'],
        ]);

        $apiKey = (string) config('services.openai.api_key');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = (string) config('services.openai.image_model', 'gpt-image-1');

        if ($apiKey === '') {
            return response()->json([
                'message' => 'AI is not configured yet. Set OPENAI_API_KEY in your .env file.',
            ], 422);
        }

        $prompt = trim((string) ($data['prompt'] ?? ''));
        $styleTab = strtolower(trim((string) ($data['style_tab'] ?? 'templates')));
        $styleHint = $styleTab === 'styles'
            ? 'Use a distinctive visual style with bold composition.'
            : 'Make it a clean, reusable design template.';

        $finalPrompt = implode(' ', array_filter([
            'Create a high-quality print-ready apparel graphic.',
            $styleHint,
            'Important: output only artwork on a transparent background, with no t-shirt mockup, no model, no scene, no text outside the design.',
            $prompt,
        ]));

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(120)
            ->post("{$baseUrl}/images/generations", [
                'model' => $model,
                'prompt' => $finalPrompt,
                'size' => '1024x1024',
                'n' => 1,
            ]);

        if (!$response->successful()) {
            $errorMessage = (string) data_get($response->json(), 'error.message', 'Failed to generate image.');

            return response()->json([
                'message' => $errorMessage,
            ], 502);
        }

        $payload = $response->json();
        $b64 = data_get($payload, 'data.0.b64_json')
            ?? data_get($payload, 'data.0.image_base64')
            ?? data_get($payload, 'data.0.base64');
        $imageUrl = data_get($payload, 'data.0.url');

        if (is_string($b64) && $b64 !== '') {
            return response()->json([
                'image_data_url' => 'data:image/png;base64,' . $b64,
            ]);
        }

        if (is_string($imageUrl) && $imageUrl !== '') {
            $imageResponse = Http::timeout(60)->get($imageUrl);

            if ($imageResponse->successful()) {
                $mime = (string) $imageResponse->header('Content-Type', 'image/png');
                $binary = $imageResponse->body();

                return response()->json([
                    'image_data_url' => 'data:' . $mime . ';base64,' . base64_encode($binary),
                ]);
            }
        }

        return response()->json([
            'message' => 'AI returned no image.',
        ], 502);
    }
}

