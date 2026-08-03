<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WgerService
{
    private const BASE_URL = 'https://wger.de/api/v2';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function getExercices(int $limit = 20, int $offset = 0): array
    {
        // On essaie d'abord en français
        $response = $this->httpClient->request('GET', self::BASE_URL . '/exerciseinfo/', [
            'query' => [
                'format'   => 'json',
                'language' => 4,
                'limit'    => $limit,
                'offset'   => $offset,
            ]
        ]);

        $data = $response->toArray();
        $results = $data['results'] ?? [];

        // Si pas assez de résultats en français, fallback anglais
        if (count($results) < $limit) {
            $responseEn = $this->httpClient->request('GET', self::BASE_URL . '/exerciseinfo/', [
                'query' => [
                    'format'   => 'json',
                    'language' => 2,
                    'limit'    => $limit,
                    'offset'   => $offset,
                ]
            ]);
            $dataEn = $responseEn->toArray();
            $results = $dataEn['results'] ?? [];
        }

        return $results;
    }

    public function getExercice(int $wgerId): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . '/exerciseinfo/' . $wgerId . '/', [
            'query' => ['format' => 'json']
        ]);

        return $response->toArray();
    }

    public function getCategories(): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . '/exercisecategory/', [
            'query' => ['format' => 'json']
        ]);

        $data = $response->toArray();
        return $data['results'] ?? [];
    }

    public function getMuscles(): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . '/muscle/', [
            'query' => ['format' => 'json']
        ]);

        $data = $response->toArray();
        return $data['results'] ?? [];
    }
}