<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Map;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

class ProductTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product
     */
    private $_model;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $_category;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $_currency;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $_priceCurrency;

    /**
     * @var \Doofinder\Feed\Helper\Product
     */
    private $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_category = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            '',
            false
        );
        $this->_category->method('getName')->will(
            $this->onConsecutiveCalls(
                'Category 1',
                'Category 1.1',
                'Category 2'
            )
        );

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->_currency = $this->getMock(
            '\Magento\Directory\Model\Currency',
            [],
            [],
            '',
            false
        );
        $this->_currency->method('format')->with(10.1234)->willReturn('10.1234');

        $this->_priceCurrency = $this->getMock(
            '\Magento\Framework\Pricing\PriceCurrencyInterface',
            [],
            [],
            '',
            false
        );
        $this->_priceCurrency->method('getCurrency')->willReturn($this->_currency);

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Product',
            [],
            [],
            '',
            false
        );
        $this->_helper->method('getProductUrl')->willReturn('http://example.com/simple-product.html');
        $this->_helper->method('getProductCategoriesWithParents')->willReturn([
            [
                $this->_category,
                $this->_category,
            ],
            [
                $this->_category,
            ]
        ]);
        $this->_helper->method('getProductImageUrl')->willReturn('http://example.com/path/to/image.jpg');
        $this->_helper->method('getProductPrice')->willReturn(10.1234);
        $this->_helper->method('getProductAvailability')->willReturn('in stock');
        $this->_helper->method('getCurrencyCode')->willReturn('USD');
        $this->_helper->method('getQuantityAndStockStatus')->willReturn('5 - in stock');
        $map = [
            [$this->_product, 'title', 'Sample title',],
            [$this->_product, 'description', 'Sample description',],
            [$this->_product, 'color', 'blue'],
            [$this->_product, 'tax_class_id', 'Taxable'],
            [$this->_product, 'manufacturer', 'Company'],
        ];
        $this->_helper->method('getAttributeText')->will($this->returnValueMap($map));

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );
        $this->_item->method('getContext')->willReturn($this->_product);

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Generator\Map\Product',
            [
                'helper' => $this->_helper,
                'item' => $this->_item,
                'priceCurrency' => $this->_priceCurrency,
            ]
        );
        $this->_model->setExportProductPrices(true);
    }

    /**
     * Test get() method
     */
    public function testGet()
    {
        $this->assertEquals('Sample title', $this->_model->get('title'));
        $this->assertEquals('Sample description', $this->_model->get('description'));
        $this->assertEquals('Category 1>Category 1.1%%Category 2', $this->_model->get('category_ids'));
        $this->assertEquals('http://example.com/path/to/image.jpg', $this->_model->get('image'));
        $this->assertEquals('http://example.com/simple-product.html', $this->_model->get('url_key'));
        $this->assertEquals('10.1234', $this->_model->get('price'));
        $this->assertEquals(null, $this->_model->setExportProductPrices(false)->get('price'));
        $this->assertEquals('in stock', $this->_model->get('df_availability'));
        $this->assertEquals('USD', $this->_model->get('df_currency'));
        $this->assertEquals('blue', $this->_model->get('color'));
        $this->assertEquals('Taxable', $this->_model->get('tax_class_id'));
        $this->assertEquals('Company', $this->_model->get('manufacturer'));
        $this->assertEquals('5 - in stock', $this->_model->get('quantity_and_stock_status'));
    }
}