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

        $geminiKey = (string) config('services.gemini.api_key');
        $openAiKey = (string) config('services.openai.api_key');
        if ($geminiKey === '' && $openAiKey === '') {
            return response()->json([
                'message' => 'AI is not configured yet. Set GEMINI_API_KEY or OPENAI_API_KEY in .env.',
            ], 422);
        }

        $errors = [];
        if ($geminiKey !== '') {
            try {
                return response()->json([
                    'image_data_url' => $this->generateWithGemini($finalPrompt),
                ]);
            } catch (\Throwable $e) {
                $errors[] = 'Gemini: ' . $e->getMessage();
            }
        }

        if ($openAiKey !== '') {
            try {
                return response()->json([
                    'image_data_url' => $this->generateWithOpenAi($finalPrompt),
                ]);
            } catch (\Throwable $e) {
                $errors[] = 'OpenAI: ' . $e->getMessage();
            }
        }

        return response()->json([
            'message' => $errors[0] ?? 'AI returned no image.',
        ], 502);
    }

    private function generateWithGemini(string $prompt): string
    {
        $apiKey = (string) config('services.gemini.api_key');
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = (string) config('services.gemini.image_model', 'gemini-2.5-flash-image');

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
        ])
            ->acceptJson()
            ->timeout(120)
            ->post("{$baseUrl}/models/{$model}:generateContent", [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE', 'TEXT'],
                    'imageConfig' => [
                        'aspectRatio' => '1:1',
                    ],
                ],
            ]);

        if (!$response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', 'Gemini image generation failed.');
            throw new \RuntimeException($message);
        }

        $payload = $response->json();
        $parts = data_get($payload, 'candidates.0.content.parts', []);
        if (!is_array($parts)) {
            $parts = [];
        }

        foreach ($parts as $part) {
            if (!is_array($part)) {
                continue;
            }

            $b64 = data_get($part, 'inlineData.data') ?? data_get($part, 'inline_data.data');
            $mime = data_get($part, 'inlineData.mimeType')
                ?? data_get($part, 'inline_data.mime_type')
                ?? 'image/png';

            if (is_string($b64) && $b64 !== '') {
                return 'data:' . $mime . ';base64,' . $b64;
            }
        }

        throw new \RuntimeException('Gemini returned no image bytes.');
    }

    private function generateWithOpenAi(string $prompt): string
    {
        $apiKey = (string) config('services.openai.api_key');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = (string) config('services.openai.image_model', 'gpt-image-1');

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(120)
            ->post("{$baseUrl}/images/generations", [
                'model' => $model,
                'prompt' => $prompt,
                'size' => '1024x1024',
                'n' => 1,
            ]);

        if (!$response->successful()) {
            $errorMessage = (string) data_get($response->json(), 'error.message', 'OpenAI image generation failed.');
            throw new \RuntimeException($errorMessage);
        }

        $payload = $response->json();
        $b64 = data_get($payload, 'data.0.b64_json')
            ?? data_get($payload, 'data.0.image_base64')
            ?? data_get($payload, 'data.0.base64');
        $imageUrl = data_get($payload, 'data.0.url');

        if (is_string($b64) && $b64 !== '') {
            return 'data:image/png;base64,' . $b64;
        }

        if (is_string($imageUrl) && $imageUrl !== '') {
            $imageResponse = Http::timeout(60)->get($imageUrl);

            if ($imageResponse->successful()) {
                $mime = (string) $imageResponse->header('Content-Type', 'image/png');
                $binary = $imageResponse->body();

                return 'data:' . $mime . ';base64,' . base64_encode($binary);
            }
        }

        throw new \RuntimeException('OpenAI returned no image bytes.');
    }
}
