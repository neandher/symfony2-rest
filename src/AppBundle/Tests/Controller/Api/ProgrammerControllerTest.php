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

    public function testPOSTprogrammer()
    {
        //$token = $this->getService('lexik_jwt_authentication.encoder')->encode(['username' => 'weaverryan']);

        $nickname = 'ObjectOrienter' . rand(0, 999);

        $data = array(
            'nickname' => $nickname,
            'avatarNumber' => 5,
            'tagLine' => 'a test dev'
        );

        $response = $this->client->post(
            '/api/programmers',
            [
                'body' => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan'),
                /*'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]*/
            ]
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertStringEndsWith('/api/programmers/' . $nickname, $response->getHeader('Location'));
        $finishedData = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('nickname', $finishedData);
        $this->assertEquals($nickname, $finishedData['nickname']);
        $this->assertEquals('application/vnd.codebattles+json', $response->getHeader('Content-Type'));
        $this->debugResponse($response);
    }

    public function testGetProgrammer()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'UnitTester',
                'avatarNumber' => 3,
            )
        );

        $response = $this->client->get('/api/programmers/UnitTester', ['headers' => $this->AuthorizedHeaders('weaverryan')]);
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
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            '_links.self',
            $this->adjustUri('/api/programmers/UnitTester')
        );
        $this->debugResponse($response);
    }

    public function testGETProgrammerDeep()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'UnitTester',
                'avatarNumber' => 3,
            )
        );

        $response = $this->client->get('/api/programmers/UnitTester?deep=1', ['headers' => $this->AuthorizedHeaders('weaverryan')]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, array('user.username'));

        $this->debugResponse($response);
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

        $response = $this->client->get('/api/programmers', ['headers' => $this->AuthorizedHeaders('weaverryan')]);
        //$this->printLastRequestUrl();
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        //$this->asserter()->assertResponsePropertyCount($response, 'items', 7);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].nickname', 'CowboyCoder');
    }

    public function testGETProgrammersCollectionPaginated()
    {

        for ($i = 0; $i < 25; $i++) {
            $this->createProgrammer(
                array(
                    'nickname' => 'Programmer' . $i,
                    'avatarNumber' => 3,
                )
            );
        }

        // page 1

        $response = $this->client->get('/api/programmers', ['headers' => $this->AuthorizedHeaders('weaverryan')]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer5');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        $this->debugResponse($response);

        // page 2

        $nextLink = $this->asserter()->readResponseProperty($response, '_links.next');
        $response = $this->client->get($nextLink, ['headers' => $this->AuthorizedHeaders('weaverryan')]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer15');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);

        $this->debugResponse($response);

        // last page

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink, ['headers' => $this->AuthorizedHeaders('weaverryan')]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertyEquals($response, 'items[4].nickname', 'Programmer24');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'programmers[5].name');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);

        $this->debugResponse($response);
    }

    public function testGETProgrammersCollectionPagination()
    {
        $this->createProgrammer(
            array(
                'nickname' => 'willnotmatch',
                'avatarNumber' => 5,
            )
        );

        for ($i = 0; $i < 25; $i++) {
            $this->createProgrammer(
                array(
                    'nickname' => 'Programmer' . $i,
                    'avatarNumber' => 3,
                )
            );
        }

        // page 1

        $response = $this->client->get('/api/programmers?filter=programmer', ['headers' => $this->AuthorizedHeaders('weaverryan')]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer5');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        $this->debugResponse($response);

        // page 2

        $nextLink = $this->asserter()->readResponseProperty($response, '_links.next');
        $response = $this->client->get($nextLink, ['headers' => $this->AuthorizedHeaders('weaverryan')]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer15');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);

        $this->debugResponse($response);

        // last page

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink, ['headers' => $this->AuthorizedHeaders('weaverryan')]);

        $this->assertEquals(200, $response->getStatusCode());

        $this->asserter()->assertResponsePropertyEquals($response, 'items[4].nickname', 'Programmer24');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'programmers[5].name');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);

        $this->debugResponse($response);
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
                'body' => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan')
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

        $response = $this->client->delete('/api/programmers/UnitTester', ['headers' => $this->AuthorizedHeaders('weaverryan')]);
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
                'body' => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan')
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
                'body' => json_encode($data),
                'headers' => $this->AuthorizedHeaders('weaverryan')
            ]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist(
            $response,
            array(
                'type',
                'title',
                'errors',
            )
        );
        $this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'errors.nickname[0]',
            'Please enter a clever nickname'
        );
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
        $response = $this->client->post(
            '/api/programmers',
            [
                'body' => $invalidBody,
                'headers' => $this->AuthorizedHeaders('weaverryan')
            ]
        );

        $this->debugResponse($response);
        $this->assertEquals(400, $response->getStatusCode());
        //$this->asserter()->assertResponsePropertyEquals($response, 'type', 'invalid_body_format');
        $this->asserter()->assertResponsePropertyContains($response, 'type', 'invalid_body_format');
    }

    public function test404Exception()
    {
        $response = $this->client->get('/api/programmers/fake', ['headers' => $this->AuthorizedHeaders('weaverryan')]);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type'));
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No programmer with nickname "fake"');
        $this->debugResponse($response);
    }

    public function testRequiresAuthentication()
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
                'body' => json_encode($data),
                //'headers' => $this->AuthorizedHeaders('weaverryan') not send to return 401 code
            ]
        );

        $this->assertEquals(401, $response->getStatusCode());

        $this->debugResponse($response);
    }

    public function testBadToken()
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
                'body' => json_encode($data),
                'headers' => [
                    'Authorization' => 'Bearer WRONG'
                ]
            ]
        );

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type'));

        $this->debugResponse($response);
    }

}