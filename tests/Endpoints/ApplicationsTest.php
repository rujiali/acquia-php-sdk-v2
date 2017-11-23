<?php

use AcquiaCloudApi\CloudApi\Client;

class ApplicationsTest extends CloudApiTestCase
{

    protected $properties = [
    'uuid',
    'name',
    'hosting',
    'subscription',
    'organization',
    'type',
    'flags',
    'status',
    'links'
    ];

    public function testGetApplications()
    {
        $response = $this->getPsr7JsonResponseForFixture('Endpoints/getApplications.json');
        $client = $this->getMockClient($response);

        /** @var AcquiaCloudApi\CloudApi\Client $client */
        $result = $client->applications();

        $this->assertInstanceOf('\ArrayObject', $result);
        $this->assertInstanceOf('\AcquiaCloudApi\Response\ApplicationsResponse', $result);

        foreach ($result as $record) {
            $this->assertInstanceOf('\AcquiaCloudApi\Response\ApplicationResponse', $record);

            foreach ($this->properties as $property) {
                $this->assertObjectHasAttribute($property, $record);
            }
        }
    }

    public function testGetApplication()
    {
        $response = $this->getPsr7JsonResponseForFixture('Endpoints/getApplication.json');
        $client = $this->getMockClient($response);

        /** @var AcquiaCloudApi\CloudApi\Client $client */
        $result = $client->application('8ff6c046-ec64-4ce4-bea6-27845ec18600');

        $this->assertNotInstanceOf('\AcquiaCloudApi\Response\ApplicationsResponse', $result);
        $this->assertInstanceOf('\AcquiaCloudApi\Response\ApplicationResponse', $result);

        foreach ($this->properties as $property) {
            $this->assertObjectHasAttribute($property, $result);
        }
    }

    public function testRenameApplication()
    {
        $response = $this->getPsr7JsonResponseForFixture('Endpoints/renameApplication.json');
        $client = $this->getMockClient($response);

        /** @var AcquiaCloudApi\CloudApi\Client $client */
        $result = $client->applicationRename('8ff6c046-ec64-4ce4-bea6-27845ec18600', "My application's new name");

        $this->assertInstanceOf('\AcquiaCloudApi\Response\OperationResponse', $result);

        $this->assertEquals('Application renamed.', $result->message);
    }
}
