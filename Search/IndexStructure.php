<?php

namespace Doofinder\Feed\Search;

class IndexStructure extends \Magento\CatalogSearch\Model\Indexer\IndexStructure
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_search;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver,
        \Doofinder\Feed\Helper\Search $search,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($resource, $indexScopeResolver);
        $this->_search = $search;
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
        $this->action('deleteDoofinderIndex', $dimensions);

        parent::delete($index, $dimensions);
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $this->action('createDoofinderIndex', $dimensions);

        parent::create($index, $fields, $dimensions);
    }

    /**
     * Action helper
     *
     * @param string $method
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     */
    private function action($method, array $dimensions)
    {
        $originalStoreCode = $this->_storeConfig->getStoreCode();
        $storeId = $this->_search->getStoreIdFromDimensions($dimensions);
        $this->_storeManager->setCurrentStore($storeId);

        $this->_search->{$method}();

        $this->_storeManager->setCurrentStore($originalStoreCode);
    }
}