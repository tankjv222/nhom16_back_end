<?php

namespace App\Services;

use App\Models\Student;
use Aws\Rekognition\RekognitionClient;

class AwsRekognitionService
{
    protected $client;

    public function __construct()
    {
        if (env('AWS_ACCESS_KEY_ID') && env('AWS_SECRET_ACCESS_KEY')) {
            $this->client = new RekognitionClient([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
        }
    }

    public function compareWithStudentImage(Student $student, string $filePath): array
    {
        if (!$this->client || !$student->photo_url) {
            return ['ok' => true, 'confidence' => 95.0, 'message' => 'Mock compare (no AWS configured or no student photo)'];
        }

        try {
            $sourceImage = null;
            $photo = $student->photo_url;

            // s3://bucket/key
            if (str_starts_with($photo, 's3://')) {
                $parts = explode('/', substr($photo, 5), 2);
                $bucket = $parts[0] ?? env('AWS_BUCKET');
                $key = $parts[1] ?? '';
                $sourceImage = ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]];
            } elseif (preg_match('#s3[.-]amazonaws\.com#i', $photo) || preg_match('#https?://[\w\-]+\.s3\.amazonaws\.com#i', $photo)) {
                // Try to parse bucket and key from S3 URL
                $url = parse_url($photo);
                if (!empty($url['host']) && str_contains($url['host'], 's3')) {
                    // host could be bucket.s3.amazonaws.com
                    $hostParts = explode('.', $url['host']);
                    $bucket = $hostParts[0];
                    $key = ltrim($url['path'] ?? '', '/');
                    $sourceImage = ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]];
                }
            }

            if (!$sourceImage) {
                // If the photo is a URL, fetch it
                if (preg_match('#^https?://#i', $photo)) {
                    $contents = @file_get_contents($photo);
                    if ($contents === false) {
                        throw new \RuntimeException('Unable to download student photo from URL');
                    }
                    $sourceBytes = $contents;
                } else {
                    // local path in storage/app/public or absolute path
                    if (file_exists($photo)) {
                        $sourceBytes = file_get_contents($photo);
                    } else {
                        // try storage path
                        $storagePath = storage_path('app/public/'.$photo);
                        if (file_exists($storagePath)) {
                            $sourceBytes = file_get_contents($storagePath);
                        } else {
                            throw new \RuntimeException("Student photo not found at $photo");
                        }
                    }
                }
            }

            // Target image bytes
            if (!file_exists($filePath)) {
                throw new \RuntimeException('Uploaded image not found: '.$filePath);
            }
            $targetBytes = file_get_contents($filePath);

            // Build params: use S3Object for SourceImage when available, otherwise Bytes
            $params = ['TargetImage' => ['Bytes' => $targetBytes], 'SimilarityThreshold' => 70];
            if ($sourceImage) {
                $params['SourceImage'] = $sourceImage;
            } else {
                $params['SourceImage'] = ['Bytes' => $sourceBytes];
            }

            $result = $this->client->compareFaces($params);

            $confidence = null;
            if (!empty($result['FaceMatches'])) {
                $confidence = $result['FaceMatches'][0]['Similarity'] ?? null;
            }

            return ['ok' => true, 'confidence' => $confidence, 'raw' => $result];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
