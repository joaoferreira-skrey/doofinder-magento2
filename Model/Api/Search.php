<?php

namespace Doofinder\Feed\Model\Api;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Search\ClientFactory;
use Doofinder\Search\Client;
use Doofinder\Search\Results;

/**
 * Class Search
 * The class responsible for communicating between Magento and Doofinder API Library
 */
class Search
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var null|Results
     */
    private $results;

    /**
     * Search constructor.
     * @param ClientFactory $clientFactory
     * @param StoreConfig $storeConfig
     */
    public function __construct(
        ClientFactory $clientFactory,
        StoreConfig $storeConfig
    ) {
        $this->clientFactory = $clientFactory;
        $this->storeConfig = $storeConfig;
    }

    /**
     * Execute Doofinder search
     * @param array $params
     * @return Results
     */
    public function execute(array $params)
    {
        $params['hashid'] = $this->storeConfig->getHashId();

        $this->results = $this->getClient()->search($params);
        return $this->results;
    }

    /**
     * @return mixed
     */
    public function getBannerData()
    {
        return $this->results->getProperty('banner');
    }

    /**
     * @param string|integer $bannerId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function registerBannerClick($bannerId)
    {
        $hashId = $this->storeConfig->getHashId();
        $this->getClient()->registerImageClick('', $hashId, $bannerId);
    }

    /**
     * @param string|integer $productId
     * @param string|null $query
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function registerResultClick($productId, $query = null)
    {
        $hashId = $this->storeConfig->getHashId();

        $options = [
            'datatype' => 'product'
        ];
        if ($query) {
            $options['query'] = $query;
        }
        $this->getClient()->registerClick('', $hashId, $productId, $options);
    }

    /**
     * Init Doofinder Search from Doofinder API Library
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->clientFactory->create([
                'server' => $this->storeConfig->getSearchServer(),
                'token' => $this->storeConfig->getApiKey()
            ]);
        }
        return $this->client;
    }
}
