<?php


namespace App\Services;

use App\DTO\DomainRankData;
use App\Models\Domain;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class PageRankService
{
    public function __construct(
        protected ClientInterface $client,
    ){
    }

    public function getApiKey(): string
    {
        return config('services.opRank.key');
    }

    private function getTopSitesUrl(): string
    {
        return config('services.opRank.topSitesUrl');
    }

    private function getApiUrl(): string
    {
        return config('services.opRank.apiUrl');
    }

    /**
     * @throws GuzzleException
     */
    public function getDomains(): Collection
    {
        $response = $this->fetchTopSitesJson();

        return $this->getRootDomains($response);
    }

    /**
     * @throws GuzzleException
     */
    protected function fetchTopSitesJson(): ResponseInterface
    {
        return $this->client->request('GET', $this->getTopSitesUrl());
    }

    protected function getRootDomains(ResponseInterface $response): Collection
    {
        return $this->decodeJson($response)
            ->pluck('rootDomain')
            ->unique()
            ->values();
    }

    public function getPageRanks(Collection $domains): Collection
    {
        return $domains
            ->chunk(100)
            ->flatMap(fn(Collection $chunk) => $this->requestChunkRanks($chunk));
    }

    protected function requestChunkRanks(Collection $chunk): Collection
    {
        try {
            $response = $this->client->request('GET', $this->getApiUrl(), [
                'headers' => ['API-OPR' => $this->getApiKey()],
                'query'   => ['domains' => $chunk->values()->all()],
            ]);

            $responseData = $this->decodeJson($response)['response'] ?? [];

            return $this->parsePageRankResponse($responseData);
        } catch (GuzzleException $e) {
            Log::error('Failed to fetch page ranks for domain chunk.', [
                'domains' => $chunk->all(),
                'error'   => $e->getMessage(),
            ]);

            return collect();
        }
    }

    protected function parsePageRankResponse(array $data): Collection
    {
        return collect($data)->map(fn($item) => new DomainRankData(
            domain: $item['domain'],
            rank: (int) ($item['page_rank_integer']),
        ));
    }

    protected function decodeJson(ResponseInterface $response): Collection
    {
        $body = $response->getBody()->getContents();

        return collect(json_decode($body, true));
    }

    public function saveRanks(Collection $ranks): void
    {
        $payload = $ranks->map(fn(DomainRankData $dto) => [
            'domain'     => $dto->domain,
            'rank'       => $dto->rank,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        Domain::upsert($payload, ['domain'], ['rank', 'updated_at']);
    }
}
