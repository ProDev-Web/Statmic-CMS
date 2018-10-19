<?php

namespace Statamic\Extend;

use GuzzleHttp\Client;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Statamic\API\Addon as AddonAPI;

class Marketplace
{
    /**
     * @var string
     */
    const API_PREFIX = 'api/v1/marketplace';

    /**
     * @var int
     */
    const CACHE_FOR_MINUTES = 60;

    /**
     * @var string
     */
    protected $domain = 'https://statamic.com';

    /**
     * @var bool
     */
    protected $verifySsl = true;

    /**
     * @var string
     */
    protected $filter;

    /**
     * Instantiate marketplace API wrapper.
     */
    public function __construct()
    {
        if ($domain = env('STATAMIC_DOMAIN')) {
            $this->domain = $domain;
            $this->verifySsl = false;
        }
    }

    /**
     * Set filter.
     *
     * @param mixed $filter
     * @return $this
     */
    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get addons.
     *
     * @param boolean $addLocalData
     * @return mixed
     */
    public function get($addLocalData = true)
    {
        $this->payload = Cache::remember('marketplace-addons', static::CACHE_FOR_MINUTES, function () {
            return $this->apiRequest('addons');
        });

        if ($addLocalData) {
            $this->addLocalMetaToPayload();
            $this->addLocalDevelopmentAddonsToPayload();
        }

        if ($this->filter) {
            $this->filterPayload();
        }

        return $this->payload;
    }

    /**
     * Get paginated addons.
     *
     * We need to paginate after processing local filters (ie. installed, not installed, etc.),
     * so we take the whole cached payload and paginate here in the app.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage)
    {
        $data = collect($this->get()['data']);

        $currentPage = request()->input('page', 1);
        $items = $data->forPage($currentPage, $perPage)->values();
        $total = $data->count();
        $options = ['path' => collect(explode('?', request()->getUri()))->first()];

        return Resource::collection(
            new LengthAwarePaginator($items, $total, $perPage, $currentPage, $options)
        );
    }

    /**
     * Find addon by github repo.
     *
     * @param string $githubRepo
     * @param boolean $addLocalData
     * @return mixed
     */
    public function findByGithubRepo($githubRepo, $addLocalData = true)
    {
        return collect($this->get($addLocalData)['data'])->first(function ($addon) use ($githubRepo) {
            return data_get($addon, 'variants.0.githubRepo') === $githubRepo;
        });
    }

    /**
     * Send API request.
     *
     * @param string $endpoint
     * @param string $method
     * @return mixed
     */
    protected function apiRequest($endpoint, $method = 'GET')
    {
        $client = new Client;

        $response = $client->request($method, $this->buildEndpoint($endpoint), [
            'verify' => $this->verifySsl,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Build api endpoint.
     *
     * @param string $uri
     * @return string
     */
    protected function buildEndpoint($endpoint)
    {
        return collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');
    }

    /**
     * Add local meta to whole payload.
     */
    protected function addLocalMetaToPayload()
    {
        $this->payload['data'] = collect($this->payload['data'])->map(function ($addon) {
            return $this->addLocalMetaToAddon($addon);
        });
    }

    /**
     * Add local meta to addon paylod.
     *
     * @param array $addon
     * @return array
     */
    protected function addLocalMetaToAddon($addon)
    {
        return array_merge($addon, [
            'installed' => AddonAPI::all()->keys()->contains($addon['variants'][0]['githubRepo']),
        ]);
    }

    /**
     * Add local development addons to payload.
     *
     */
    protected function addLocalDevelopmentAddonsToPayload()
    {
        AddonAPI::all()->reject->marketplaceProductId()->each(function ($addon) {
            $this->payload['data'][] = $this->buildAddonPayloadFromLocalData($addon);
        });
    }

    /**
     * Build addon payload from local data.
     *
     * @param Addon $addon
     * @return array
     */
    protected function buildAddonPayloadFromLocalData(Addon $addon)
    {
        return [
            'id' => $addon->id(),
            'name' => $addon->name(),
            'variants' => [
                [
                    'id' => $addon->id() . '-variant',
                    'number' => 1,
                    'description' => 'N/A',
                    'assets' => [],
                ]
            ],
            'seller' => [
                'id' => $addon->id() . '-seller',
                'name' => 'NA',
                'website' => null,
                'avatar' => null,
            ],
            'installed' => true,
        ];
    }

    /**
     * Filter payload.
     */
    protected function filterPayload()
    {
        if ($this->filter === 'installable') {
            $this->payload['data'] = collect($this->payload['data'])->reject->installed->values()->all();
        } elseif ($this->filter === 'installed') {
            $this->payload['data'] = collect($this->payload['data'])->filter->installed->values()->all();
        }
    }
}
