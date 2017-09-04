<?php

namespace Doofinder\Feed\Helper;

/**
 * Class FeedConfig
 * @package Doofinder\Feed\Helper
 */
class FeedConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array Feed attribute config
     */
    private $_feedConfig;

    /**
     * @var array Config for given store code
     */
    private $_config;

    /**
     * @var array Config parameters
     */
    private $_params;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Zend\Serializer\Adapter\PhpSerialize
     */
    private $_phpSerialize;

    /**
     * FeedConfig constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Zend\Serializer\Adapter\PhpSerialize $phpSerialize
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_phpSerialize = $phpSerialize;
        parent::__construct($context);
    }

    /**
     * Get feed minimal config.
     *
     * @param string $storeCode
     * @return array
     */
    public function getLeanFeedConfig($storeCode = null)
    {
        $this->_config = $this->_storeConfig->getStoreConfig($storeCode);

        return [
            'data' => [
                'config' => [
                    'fetchers' => [],
                    'processors' => $this->getProcessors(),
                ],
            ],
        ];
    }

    /**
     * Get feed attribute config.
     *
     * @param string $storeCode
     * @param array $params = []
     * @return array
     */
    public function getFeedConfig($storeCode = null, array $params = [])
    {
        if (!isset($this->_feedConfig[$storeCode])) {
            $this->_params = $params;
            $this->setFeedConfig($storeCode);
        }

        return $this->_feedConfig[$storeCode];
    }

    /**
     * Set feed config
     *
     * @param string $storeCode
     */
    private function setFeedConfig($storeCode)
    {
        $config = $this->getLeanFeedConfig($storeCode);

        // Add basic product fetcher
        $config['data']['config']['fetchers'] = $this->getFetchers();

        // Add basic xml processor
        $config['data']['config']['processors']['Xml'] = [];

        $this->_feedConfig[$storeCode] = $config;
    }

    /**
     * Setup fetchers.
     *
     * @return array
     */
    private function getFetchers()
    {
        return [
            'Product' => [
                'offset' => $this->getParam('offset'),
                'limit' => $this->getParam('limit'),
            ],
        ];
    }

    /**
     * Setup processors.
     *
     * @return array
     */
    private function getProcessors()
    {
        return [
            'Mapper' => $this->getMapper(),
            'Cleaner' => [],
        ];
    }

    /**
     * Setup feed mapper.
     *
     * @return array
     */
    private function getMapper()
    {
        return [
            'image_size' => $this->_config['image_size'],
            'split_configurable_products' => $this->_config['split_configurable_products'],
            'export_product_prices' => $this->_config['export_product_prices'],
            'price_tax_mode' => $this->_config['price_tax_mode'],
            'categories_in_navigation' => $this->_config['categories_in_navigation'],
            'map' => $this->getFeedAttributes()
        ];
    }

    /**
     * Get feed attributes from config.
     *
     * @return array
     */
    private function getFeedAttributes()
    {
        $attributes = $this->_config['attributes'];

        if (array_key_exists('additional_attributes', $attributes)) {
            $additionalKeys = $this->_phpSerialize->unserialize($attributes['additional_attributes']);
            unset($attributes['additional_attributes']);

            $additionalAttributes = [];
            foreach ($additionalKeys as $key) {
                $additionalAttributes[$key['field']] = $key['additional_attribute'];
            }

            return array_merge($attributes, $additionalAttributes);
        }

        return $attributes;
    }

    /**
     * Get param
     *
     * @param string $key
     * @return mixed
     */
    private function getParam($key)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : null;
    }

    /**
     * Get feed password
     *
     * @param string $storeCode
     * @return mixed
     */
    public function getFeedPassword($storeCode = null)
    {
        $storeConfig = $this->_storeConfig->getStoreConfig($storeCode);
        return isset($storeConfig['password']) ? $storeConfig['password'] : null;
    }
}