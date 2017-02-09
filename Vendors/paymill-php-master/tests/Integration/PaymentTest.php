<?php

namespace Paymill\Tests\Integration;

use Paymill\Models as Models;

/**
 * PaymentTest
 */
class PaymentTest extends IntegrationBase
{
    /**
     * @var \Paymill\Models\Request\Payment
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_model = new Models\Request\Payment();
        parent::setUp();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->_model = null;
        parent::tearDown();
    }

    /**
     * @test
     * @codeCoverageIgnore
     */
    public function createPayment()
    {
        $this->_model->setToken("098f6bcd4621d373cade4e832627b4f6");
        $result = $this->_service->create($this->_model);
        $this->assertInstanceOf('Paymill\Models\Response\Payment', $result);
        return $result;
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createPayment
     * @expectedException \Paymill\Services\PaymillException
     * @expectedExceptionMessage Method not Found
     */
    public function updatePayment($model)
    {
        $this->_model->setId($model->getId());
        $this->_service->update($this->_model);
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createPayment
     */
    public function getOnePayment($model)
    {
        $this->_model->setId($model->getId());
        $result = $this->_service->getOne($this->_model);
        $this->assertInstanceOf('Paymill\Models\Response\Payment', $result, var_export($result, true));
        $this->assertEquals($model->getId(), $result->getId());
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createPayment
     */
    public function getAllPayment()
    {
        $this->_model;
        $result = $this->_service->getAll($this->_model);
        $this->assertInternalType('array', $result, var_export($result, true));
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createPayment
     */
    public function getAllPaymentAsModel()
    {
        $this->_model;
        $result = $this->_service->getAllAsModel($this->_model);
        $this->assertInternalType('array', $result, var_export($result, true));
		$this->assertInstanceOf('Paymill\Models\Response\Payment', array_pop($result));
    }

    /**
     * @test
     * @codeCoverageIgnore
     */
    public function getAllPaymentWithFilter()
    {
        $this->_model->setFilter(array(
            'count' => 1,
            'offset' => 0
            )
        );
        $result = $this->_service->getAll($this->_model);
        $this->assertEquals(1, count($result), var_export($result, true));
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createPayment
     * @depends getOnePayment
     * @depends updatePayment
     */
    public function deletePayment($model)
    {
        $this->_model->setId($model->getId());
        $result = $this->_service->delete($this->_model);
        $this->assertEquals(null, $result, var_export($result, true));
    }

}
