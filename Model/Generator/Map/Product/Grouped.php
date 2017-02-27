<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use \Doofinder\Feed\Model\Generator\Map\Product;

/**
 * Class Grouped
 *
 * @package Doofinder\Feed\Model\Generator\Map\Product
 */
class Grouped extends Product
{
    /**
     * Get bundle product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $field
     * @param boolean $minimal = false
     * @return float
     */
    protected function getProductPrice(\Magento\Catalog\Model\Product $product, $field, $minimal = false)
    {
        if ($field == 'final_price') {
            $minimal = true;
        }

        return parent::getProductPrice($product, $field, $minimal);
    }
}
