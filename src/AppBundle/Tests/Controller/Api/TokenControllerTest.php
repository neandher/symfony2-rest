<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;

class TokenControllerTest extends ApiTestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->createUser('weaverryan','I<3Pizza');
    }

    public function testPOSTCreateToken()
    {
        $response = $this->client->post('/api/tokens', [
            'auth' => ['weaverryan', 'I<3Pizza']
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'token');

        $this->debugResponse($response);

    }

    public function testPOSTTokenInvalidCredentials()
    {
        $response = $this->client->post('/api/tokens', [
            'auth' => ['weaverryan', 'IH8Pizza']
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'token');

        $this->debugResponse($response);

    }

}