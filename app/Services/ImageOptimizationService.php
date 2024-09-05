<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ImageOptimizationService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('TINYPNG_API_KEY');
        $this->client = new Client([
            'auth' => ['api', $this->apiKey],
        ]);
    }

    /**
     * @param $filePath
     * @return mixed
     * @throws GuzzleException
     */
    public function optimize($filePath)
    {
        if (!$this->apiKey) {
            return $filePath;
        }

        try {
            $response = $this->client->post('https://api.tinify.com/shrink', [
                'body' => file_get_contents($filePath),
            ]);

            if ($response->getStatusCode() == 201) {
                $result = json_decode($response->getBody(), true);
                file_put_contents($filePath, file_get_contents($result['output']['url']));
            }
        } catch (Exception $e) {
            Log::error('Error: ' . $e->getMessage());
        }

        return $filePath;
    }
}
