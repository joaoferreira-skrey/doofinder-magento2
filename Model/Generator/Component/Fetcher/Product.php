<?php

namespace Doofinder\Feed\Model\Generator\Component\Fetcher;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Fetcher;

class Product extends Component implements Fetcher
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory = null;

    /**
     * @var \Doofinder\Feed\Model\Generator\ItemFactory
     */
    protected $_generatorItemFactory = null;

    /**
     * @var boolean
     */
    protected $_isStarted = null;

    /**
     * @var boolean
     */
    protected $_isDone = null;

    /**
     * @var int
     */
    protected $_lastProcessedEntityId = null;

    /**
     * Amount of all products left in current fetch
     *
     * @var int
     */
    protected $_itemsLeftCount = null;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Doofinder\Feed\Model\Generator\ItemFactory $generatorItemFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_generatorItemFactory = $generatorItemFactory;
        parent::__construct($logger, $data);
    }

    /**
     * Fetch products using collection
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    protected function fetchProducts()
    {
        $collection = $this->getProductCollection($this->getLimit(), $this->getOffset());
        $collection->load();

        // Check if fetcher is started
        $this->_isStarted = !$this->getOffset();

        // Check if fetcher is done
        if ($collection->getSize() > 0) {
            $this->_isDone = !$this->getLimit() || $this->getLimit() >= $collection->getSize();
        } else {
            // Done if not items fetched but fetcher is started
            $this->_isDone = $this->_isStarted;
        }


        // Set the last processed entity id
        $this->_lastProcessedEntityId = $this->getOffset();
        if ($collection->getSize()) {
            $this->_lastProcessedEntityId = $collection->getLastItem()->getEntityId();
        }

        // Set fetched size
        $this->_itemsLeftCount = $this->getLimit() ? max(0, $collection->getSize() - $this->getLimit()) : 0;

        return $collection->getItems();
    }

    /**
     * Fetch products
     *
     * @return Item[]
     */
    public function fetch()
    {
        $products = $this->fetchProducts();

        $items = array();
        foreach ($products as $product) {
            $item = $this->createItem($product);
            $items[] = $item;

            // Add all item associates inline with other products
            if ($item->hasAssociates()) {
                foreach ($item->getAssociates() as $associate) {
                    $items[] = $associate;
                }
            }
        }

        return $items;
    }

    /**
     * Fetch product associates
     *
     * @param \Magento\Catalog\Model\Product
     * @return \Doofinder\Feed\Model\Generator\Item[]
     */
    protected function fetchProductAssociates(\Magento\Catalog\Model\Product $product)
    {
        $associates = [];

        foreach ($product->getTypeInstance()->getUsedProducts($product) as $subproduct) {
            $associates[] = $this->createItem($subproduct);
        }

        return $associates;
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection($limit = null, $offset = null)
    {
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStoreFilter()
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH
            ])
            ->addAttributeToSort('id', 'asc');

        if ($limit) {
            $collection->setPageSize($limit);
        }

        if ($offset) {
            $collection->addAttributeToFilter('entity_id', ['gt' => $offset]);
        }

        return $collection;
    }

    /**
     * Create item from product
     *
     * @param \Magento\Catalog\Model\Product
     */
    protected function createItem(\Magento\Catalog\Model\Product $product)
    {
        $item = $this->_generatorItemFactory->create();
        $item->setContext($product);

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $item->setAssociates($this->fetchProductAssociates($product));
        }

        return $item;
    }

    /**
     * Check if the first item has been fetched
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->_isStarted;
    }

    /**
     * Check if the last item has been fetched
     *
     * @return boolean
     */
    public function isDone()
    {
        return $this->_isDone;
    }

    /**
     * Get last processed entity id
     *
     * @return int
     */
    public function getLastProcessedEntityId()
    {
        return $this->_lastProcessedEntityId;
    }

    /**
     * Get progress
     *
     * @return float
     */
    public function getProgress()
    {
        $collection = $this->getProductCollection();
        $total = $collection->getSize();

        return $total ? (1 - round(1.0 * $this->_itemsLeftCount / $total, 2)) : 1.0;
    }
}
