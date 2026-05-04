<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaceVerificationService
{
    /**
     * Default cosine similarity threshold for face verification.
     */
    protected float $threshold;

    /**
     * URL of the Python face service.
     */
    protected string $pythonServiceUrl;

    public function __construct()
    {
        $this->threshold = (float) config('app.face_verification_threshold', 0.75);
        $this->pythonServiceUrl = config('services.face_extraction.url', 'http://localhost:8001');
    }

    /**
     * Verify a face by sending the image to the Python service for
     * server-side embedding extraction + comparison.
     *
     * This ensures both enrollment and verification use the exact same
     * preprocessing pipeline (OpenCV detection → tight crop → MobileFaceNet).
     *
     * @param string $imageBytes  Raw JPEG image bytes
     * @param array  $storedEmbedding  192D enrollment embedding from DB
     * @return array{verified: bool, score: float, embedding: array|null}
     */
    public function verifyWithImage(string $imageBytes, array $storedEmbedding): array
    {
        try {
            $response = Http::timeout(30)
                ->attach('file', $imageBytes, 'face.jpg')
                ->post($this->pythonServiceUrl . '/verify', [
                    'stored_embedding' => json_encode($storedEmbedding),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Face verification via Python service', [
                    'verified' => $data['verified'] ?? false,
                    'score' => $data['score'] ?? 0,
                ]);
                return [
                    'verified' => $data['verified'] ?? false,
                    'score' => round($data['score'] ?? 0, 4),
                    'embedding' => $data['embedding'] ?? null,
                ];
            }

            Log::warning('Python face service returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'verified' => false,
                'score' => 0.0,
                'embedding' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Python face service connection failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'verified' => false,
                'score' => 0.0,
                'embedding' => null,
            ];
        }
    }

    /**
     * Verify a face embedding against the stored enrollment embedding.
     * (Legacy method — used when embeddings are already extracted)
     *
     * @param array $inputEmbedding  192-dimensional vector from the device
     * @param array $storedEmbedding 192-dimensional vector from enrollment
     * @return array{verified: bool, score: float}
     */
    public function verify(array $inputEmbedding, array $storedEmbedding): array
    {
        $score = $this->cosineSimilarity($inputEmbedding, $storedEmbedding);

        return [
            'verified' => $score >= $this->threshold,
            'score' => round($score, 4),
        ];
    }

    /**
     * Compute cosine similarity between two vectors.
     * Returns a value between -1 and 1, where 1 = identical.
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new \InvalidArgumentException(
                'Embeddings must have the same dimension. Got ' . count($a) . ' and ' . count($b)
            );
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += (float) $a[$i] * (float) $b[$i];
            $normA += (float) $a[$i] * (float) $a[$i];
            $normB += (float) $b[$i] * (float) $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator == 0) {
            return 0.0;
        }

        return $dotProduct / $denominator;
    }

    /**
     * Get the current verification threshold.
     */
    public function getThreshold(): float
    {
        return $this->threshold;
    }
}
