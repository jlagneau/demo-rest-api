<?php

namespace Tests\BlogBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\BlogBundle\CustomWebTestCase as WebTestCase;

class ArticleControllerTest extends WebTestCase
{
    private $parameters;

    /**
     * Set up the database and fixtures for tests.
     */
    public function setUp()
    {
        self::bootKernel();
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');
        self::runCommand('doctrine:fixtures:load --purge-with-truncate --no-interaction');

        $this->client = static::createClient();
        $this->fixtures = $this->loadFixtures([
            'BlogBundle\DataFixtures\ORM\LoadUserData',
            'BlogBundle\DataFixtures\ORM\LoadArticleData',
        ])->getReferenceRepository();

        $this->parameters['user_name'] = static::$kernel->getContainer()->getParameter('user_name');
        $this->parameters['user_pass'] = static::$kernel->getContainer()->getParameter('user_pass');
    }

    /**
     * Test GET HTTP method.
     */
    public function testGet()
    {
        $route = $this->getUrl('api_get_articles', ['_format' => 'json', 'limit' => 2]);
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_OK);
        $content = $response->getContent();
        $decoded = json_decode($content, true);
        $this->assertEquals(count($decoded), 2);
        foreach ($decoded as $article) {
            $this->assertTrue(isset($article['id']));
            $this->assertTrue(isset($article['title']));
            $this->assertTrue(isset($article['content']));
        }

        $route = $this->getUrl('api_get_article', ['id' => 1, '_format' => 'json']);
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_OK);
        $content = $response->getContent();
        $decoded = json_decode($content, true);
        $this->assertTrue(isset($decoded['id']));
        $this->assertTrue(isset($decoded['title']));
        $this->assertTrue(isset($decoded['content']));
    }

    /**
     * Test HEAD HTTP method.
     */
    public function testHead()
    {
        $this->client->request(
            'HEAD',
            sprintf('/articles/%d.json', 1),
            ['ACCEPT' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200, false);
        $this->assertEquals($response->getContent(), null);

        $this->client->request(
            'HEAD',
            '/articles.json',
            ['ACCEPT' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, 200, false);
        $this->assertEquals($response->getContent(), null);
    }

    /**
     * Test POST HTTP method.
     */
    public function testJsonPost()
    {
        $this->createAuthenticatedClient();
        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"title":"foo","content":"bar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_CREATED, false);
    }

    /**
     * Test POST HTTP method with bad credentials.
     */
    public function testJsonPostBadCredentials()
    {
        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"title":"foo","content":"bar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_UNAUTHORIZED, false);
    }

    /**
     * Test POST HTTP method with bad parameters.
     */
    public function testJsonPostBadParameters()
    {
        $this->createAuthenticatedClient();
        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"foo":"bar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test PUT HTTP method when modifying.
     */
    public function testJsonPutModify()
    {
        $route = $this->getUrl('api_put_article', ['id' => 1, '_format' => 'json']);

        $this->client->request(
            'GET',
            $route,
            ['ACCEPT' => 'application/json']
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );

        $this->createAuthenticatedClient();
        $this->client->request(
            'PUT',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"title":"foobar","content":"foobar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_SEE_OTHER, false);
        $this->assertTrue(
            $response->headers->has('Location')
        );
    }

    /**
     * Test PUT HTTP method when creating.
     */
    public function testJsonPutCreate()
    {
        $route = $this->getUrl('api_put_article', ['id' => 0, '_format' => 'json']);

        $this->client->request(
            'GET',
            $route,
            ['ACCEPT' => 'application/json']
        );

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse()->getStatusCode(),
            $this->client->getResponse()->getContent()
        );

        $this->createAuthenticatedClient();
        $this->client->request(
            'PUT',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"title":"barfoo","content":"barfoo"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_CREATED, false);
    }

    /**
     * Test PUT HTTP method with bad parameters.
     */
    public function testJsonPutBadParameters()
    {
        $route = $this->getUrl('api_put_article', ['id' => 0, '_format' => 'json']);

        $this->createAuthenticatedClient();
        $this->client->request(
            'PUT',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"bar":"foo"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test PATCH HTTP method.
     */
    public function testJsonPatch()
    {
        $route = $this->getUrl('api_patch_article', ['id' => 1, '_format' => 'json']);

        $this->createAuthenticatedClient();
        $this->client->request(
            'PATCH',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'ACCEPT' => 'application/json',
            ],
            '{"content":"def"}'
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * Test PATCH HTTP method with bad parameters.
     */
    public function testJsonPatchBadParameters()
    {
        $route = $this->getUrl('api_patch_article', ['id' => 1, '_format' => 'json']);

        $this->createAuthenticatedClient();
        $this->client->request(
            'PATCH',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            '{"foobar":"foobar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test DELETE HTTP method.
     */
    public function testDelete()
    {
        $route = $this->getUrl('api_get_article', ['id' => 1, '_format' => 'json']);

        $this->createAuthenticatedClient();
        $this->client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * Test DELETE HTTP method when not found.
     */
    public function testDeleteNotFound()
    {
        $id = 0;
        $route = $this->getUrl('api_get_article', ['id' => $id, '_format' => 'json']);

        $this->createAuthenticatedClient();
        $this->client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * Create a client with a default Authorization header.
     */
    protected function createAuthenticatedClient()
    {
        $this->client->request(
            'POST',
            '/login_check',
            [
                'username' => $this->parameters['user_name'],
                'password' => $this->parameters['user_pass'],
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        if (array_key_exists('token', $data)) {
            $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
        }
    }
}
