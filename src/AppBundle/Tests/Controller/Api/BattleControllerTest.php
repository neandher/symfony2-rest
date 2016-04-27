<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;

class BattleControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->createUser('weaverryan');
    }

    public function testPostCreateBattle()
    {
        $project = $this->createProject('my_project');

        $programmer = $this->createProgrammer(
            [
                'nickname' => 'Fred',
                'tagLine' => '',
                'avatarNumber' => 5,
            ]
        );

        $data = [
            'project' => $project->getId(),
            'programmer' => $programmer->getId(),
        ];

        $response = $this->client->post('/api/battles',
            [
                'body' => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan')
            ]
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'didProgrammerWin');
        $this->debugResponse($response);
    }
}