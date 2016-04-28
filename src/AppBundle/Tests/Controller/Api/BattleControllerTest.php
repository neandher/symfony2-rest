<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;

class BattleControllerTest extends ApiTestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->createUser('weaverryan');
        $this->createUser('someone_else');
    }

    public function testPOSTCreateBattle()
    {
        $project = $this->createProject('my_project');

        $programmer = $this->createProgrammer(
            [
                'nickname'     => 'Fred',
                'tagLine'      => '',
                'avatarNumber' => 5,
            ]
        );

        $data = [
            'projectId'    => $project->getId(),
            'programmerId' => $programmer->getId(),
        ];

        $response = $this->client->post(
            '/api/battles',
            [
                'body'    => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan')
            ]
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'didProgrammerWin');
        $this->asserter()->assertResponsePropertyEquals($response, 'project', $project->getId());
        $this->asserter()->assertResponsePropertyEquals($response, 'programmer', 'Fred');
        $this->debugResponse($response);
    }

    public function testPostBattleValidationErrors()
    {
        $programmer = $this->createProgrammer(
            [
                'nickname'     => 'Fred',
                'tagLine'      => '',
                'avatarNumber' => 5,
                'user'         => 'someone_else'
            ]
        );

        $data = [
            'projectId'    => null,
            'programmerId' => $programmer->getId(),
        ];

        $response = $this->client->post(
            '/api/battles',
            [
                'body'    => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan')
            ]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'errors.projectId');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'errors.projectId[0]',
            'This value should not be blank.'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.programmerId[0]', 'some dummy message');
        $this->debugResponse($response);
    }
}