<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Entity\Programmer;
use AppBundle\Test\ApiTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ProgrammerControllerTest extends ApiTestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->createUser('weaverryan');
    }

    public function testPost()
    {

        $nickname = 'ObjectOrienter' . rand(0, 999);

        $data = array(
            'nickname' => $nickname,
            'avatarNumber' => 5,
            'tagLine' => 'a test dev'
        );

        $response = $this->client->post(
            '/api/programmers',
            [
                'body' => json_encode($data)
            ]
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertStringEndsWith('/api/programmers/' . $nickname, $response->getHeader('Location'));
        $finishedData = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('nickname', $finishedData);
        $this->assertEquals($nickname, $finishedData['nickname']);
    }

    public function testGETProgrammer()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'UnitTester',
                'avatarNumber' => 3,
            )
        );

        $response = $this->client->get('/api/programmers/UnitTester');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist(
            $response,
            array(
                'nickname',
                'avatarNumber',
                'powerLevel',
                'tagLine'
            )
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'UnitTester');
        //$this->debugResponse($response);
    }

    public function testGETProgrammersCollection()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'UnitTester',
                'avatarNumber' => 3,
            )
        );
        $this->createProgrammer(
            array(
                'nickname' => 'CowboyCoder',
                'avatarNumber' => 5,
            )
        );

        $response = $this->client->get('/api/programmers');
        //$this->printLastRequestUrl();
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'programmers');
        //$this->asserter()->assertResponsePropertyCount($response, 'programmers', 7);
        $this->asserter()->assertResponsePropertyEquals($response, 'programmers[1].nickname', 'CowboyCoder');
    }

    protected function createProgrammer(array $data)
    {
        $data = array_merge(
            array(
                'powerLevel' => rand(0, 10),
                'user' => $this->getEntityManager()->getRepository('AppBundle:User')->findAny()
            ),
            $data
        );

        // Use PropertyAccess component instead of iterating and calling each setter.
        // Call $accessor->setValue(), pass in $programmer, $key (property name) and $value.
        $accessor = PropertyAccess::createPropertyAccessor();

        $programmer = new Programmer();

        foreach ($data as $key => $value) {
            $accessor->setValue($programmer, $key, $value);
        }

        $this->getEntityManager()->persist($programmer);
        $this->getEntityManager()->flush();

        return $programmer;
    }

    public function testPUTProgrammerController()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'CowboyCoder',
                'avatarNumber' => 5,
                'tagLine' => 'foo',
            )
        );

        $data = array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 2,
            'tagLine' => 'foo',
        );

        $response = $this->client->put(
            '/api/programmers/CowboyCoder',
            [
                'body' => json_encode($data)
            ]
        );

        $this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 2);
    }

    public function testDELETEProgrammer()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'UnitTester',
                'avatarNumber' => 3,
            )
        );

        $response = $this->client->delete('/api/programmers/UnitTester');
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testPATCHProgrammer()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'CowboyCoder',
                'avatarNumber' => 5,
                'tagLine' => 'foo',
            )
        );

        $data = array(
            'tagLine' => 'bar',
        );

        $response = $this->client->patch(
            '/api/programmers/CowboyCoder',
            [
                'body' => json_encode($data)
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 5);
        $this->asserter()->assertResponsePropertyEquals($response, 'tagLine', 'bar');
    }

    public function testValidationErros()
    {

        $data = array(
            'avatarNumber' => 5,
            'tagLine' => 'a test dev'
        );

        $response = $this->client->post(
            '/api/programmers',
            [
                'body' => json_encode($data)
            ]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.nickname[0]', 'Please enter a clever nickname');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.avatarNumber');
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type'));
        $this->debugResponse($response);
    }

    public function testInvalidJson()
    {
        $invalidBody = <<<EOF
{
    "nickname": "JohnnyRobot",
    "avatarNumber" : "2
    "tagLine": "I'm from a test!"
}
EOF;
        $response = $this->client->post('/api/programmers', [
            'body' => $invalidBody
        ]);

        $this->debugResponse($response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'invalid_body_format');
    }

    public function test404Exception()
    {
        $response = $this->client->get('/api/programmers/fake');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type'));
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->debugResponse($response);
    }

}