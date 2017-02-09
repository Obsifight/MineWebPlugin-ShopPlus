<?php

namespace Paymill\Tests\Integration;

use Paymill\Models as Models;

/**
 * Client
 */
class Client extends IntegrationBase
{
    /**
     * @var \Paymill\Models\Request\Client
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_model = new Models\Request\Client();
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
    public function createClient()
    {
        $this->_model->setEmail('Plugins@Paymill.de')
            ->setDescription('Test');
        $result = $this->_service->create($this->_model);
        $this->assertInstanceOf('Paymill\Models\Response\Client', $result);
        return $result;
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createClient
     */
    public function updateClient($model)
    {
        $this->_model->setId($model->getId())
            ->setDescription('UpdateSuccessful');
        $result = $this->_service->update($this->_model);
        $this->assertInstanceOf('Paymill\Models\Response\Client', $result, var_export($result, true));
        $this->assertEquals('UpdateSuccessful', $result->getDescription());
    }

    /**
     *
     * @test
     * @codeCoverageIgnore
     * @expectedException \Paymill\Services\PaymillException
     * @expectedExceptionMessage Client not found
     */
    public function updateClientWithWrongId()
    {
        $this->_model->setId('YouWillNeverFindMe404')
            ->setDescription('TEST');
        $this->_service->update($this->_model);
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createClient
     */
    public function getOneClient($model)
    {
        $this->_model->setId($model->getId());
        $result = $this->_service->getOne($this->_model);
        $this->assertInstanceOf('Paymill\Models\Response\Client', $result, var_export($result, true));
        $this->assertEquals($model->getId(), $result->getId());
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @depends createClient
     */
    public function getAllClient()
    {
        $result = $this->_service->getAll($this->_model);
        $this->assertInternalType('array', $result, var_export($result, true));
    }

    /**
     * @test
     * @codeCoverageIgnore
     */
    public function getAllClientAsModel()
    {
        $result = $this->_service->getAllAsModel($this->_model);
        $this->assertInternalType('array', $result, var_export($result, true));
        $this->assertInstanceOf('Paymill\Models\Response\Client', array_pop($result));
    }



    /**
     * @test
     * @codeCoverageIgnore
     */
    public function getAllClientWithFilter()
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
     * @depends createClient
     * @depends getOneClient
     * @depends updateClient
     */
    public function deleteClient($model)
    {
        $this->_model->setId($model->getId());
        $this->markTestIncomplete('Client does not return a empty array like the other resources.');
        $result = $this->_service->delete($this->_model);
        $this->assertInternalType('array', $result, var_export($result, true));
    }

    /**
     * @test
     * @codeCoverageIgnore
     * @expectedException \Paymill\Services\PaymillException
     * @expectedExceptionMessage 'PluginsAtPaymillDotde' is not a valid email address.
     */
    public function createClientWithInvalidEmail()
    {
        $this->_model->setEmail('PluginsAtPaymillDotde')
            ->setDescription('Test');
        $result = $this->_service->create($this->_model);
        $this->assertInstanceOf('Paymill\Models\Response\Client', $result);
        return $result;
    }


}
