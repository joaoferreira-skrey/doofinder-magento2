<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\ApiKeyValidation
 */
class ApiKeyValidationTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private $resource;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\HashIdValidation
     */
    private $model;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->storeConfig = $this->getMock(
            \Doofinder\Feed\Helper\StoreConfig::class,
            [],
            [],
            '',
            false
        );

        $this->search = $this->getMock(
            \Doofinder\Feed\Helper\Search::class,
            [],
            [],
            '',
            false
        );

        $this->resource = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\ApiKeyValidation::class,
            [
                'storeConfig' => $this->storeConfig,
                'search' => $this->search,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * Test save() method with empty value
     *
     * @return void
     */
    public function testSaveEmpty()
    {
        $this->resource->expects($this->once())->method('save');
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(false);

        $this->model->setValue(null);
        $this->model->save();
    }

    /**
     * Test save() method with empty value with engine enabled
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testSaveEmptyEngineEnabled()
    {
        $this->resource->expects($this->never())->method('save');
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(true);

        $this->model->setValue(null);
        $this->model->save();
    }

    /**
     * Test save() method valid format validation
     *
     * @param  string $value
     * @return void
     * @dataProvider providerTestSaveValidFormat
     */
    public function testSaveValidFormat($value)
    {
        $this->resource->expects($this->once())->method('save');

        $this->model->setValue($value);
        $this->model->save();
    }

    /**
     * Data provider for 'testSaveValidFormat'
     *
     * @return array
     */
    public function providerTestSaveValidFormat()
    {
        return [
            ['eu1-abcdef0123456789abcdef0123456789abcdef01'],
            ['eu1-0123456789abcdef0123456789abcdef01234567'],
            ['us1-abcdef0123456789abcdef0123456789abcdef01'],
            ['us1-0123456789abcdef0123456789abcdef01234567'],
        ];
    }

    /**
     * Test save() method invalid format validation
     *
     * @param  string $value
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider providerTestSaveInvalidFormat
     */
    public function testSaveInvalidFormat($value)
    {
        $this->resource->expects($this->never())->method('save');

        $this->model->setValue($value);
        $this->model->save();
    }

    /**
     * Data provider for 'testSaveInvalidFormat'
     *
     * @return array
     */
    public function providerTestSaveInvalidFormat()
    {
        return [
            ['foo'],
            ['foo-bar'],
            ['eu1-foo'],
            ['eu1-abcdef0123456789abcdef-0123456789abcdef0'],
            ['eu1-abcdefg0123456789abcdef0123456789abcdef0'],
            ['eu1-abcdef0123456789abcdef0123456789abcdef0'],
        ];
    }

    /**
     * Test save() method with invalid api key
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testSaveInvalidApiKey()
    {
        $this->resource->expects($this->never())->method('save');
        $this->search->method('getDoofinderSearchEngines')->will(
            $this->throwException(new \Doofinder\Api\Management\Errors\NotAllowed())
        );

        $this->model->setValue('eu1-0000000000000000000000000000000000000000');
        $this->model->save();
    }
}
