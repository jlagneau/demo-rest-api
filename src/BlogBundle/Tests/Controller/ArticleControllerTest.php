<?php

namespace BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase as WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    /**
     * Set up the database and fixtures for tests.
     */
    public function setUp()
    {
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');
        self::runCommand('doctrine:fixtures:load --purge-with-truncate --no-interaction');

        $this->fixtures = $this->loadFixtures([
            'BlogBundle\DataFixtures\ORM\LoadUserData',
            'BlogBundle\DataFixtures\ORM\LoadArticleData',
        ])->getReferenceRepository();
    }

    /**
     * Test GET HTTP method.
     */
    public function testGet()
    {
        $this->client = static::createClient();
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

        $article = $this->getArticle();
        $route = $this->getUrl('api_get_article', ['id' => $article->getId(), '_format' => 'json']);
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
        $this->client = static::createClient();
        $article = $this->getArticle();

        $this->client->request(
            'HEAD',
            sprintf('/articles/%d.json', $article->getId()),
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
        $this->client = static::createClient();
        $token = $this->getApiToken();

        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"title":"foo","content":"bar"}'
        );
        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_CREATED, false);
    }

    /**
     * Test POST HTTP method with bad parameters.
     */
    public function testJsonPostBadParameters()
    {
        $this->client = static::createClient();
        $token = $this->getApiToken();

        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"foo":"bar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test PUT HTTP method when modifying.
     */
    public function testJsonPutModify()
    {
        $this->client = static::createClient();
        $token = $this->getApiToken();
        $article = $this->getArticle();

        $this->client->request(
            'GET',
            sprintf('/articles/%d.json', $article->getId()),
            ['ACCEPT' => 'application/json']
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client->request(
            'PUT',
            sprintf('/articles/%d.json', $article->getId()),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"title":"foobar","content":"foobar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_SEE_OTHER, false);
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Location',
                sprintf('http://localhost/articles/%d.json', $article->getId())
            ),
            $this->client->getResponse()->headers
        );
    }

    /**
     * Test PUT HTTP method when creating.
     */
    public function testJsonPutCreate()
    {
        $id = 0;
        $this->client = static::createClient();
        $token = $this->getApiToken();

        $this->client->request(
            'GET',
            sprintf('/articles/%d.json', $id),
            ['ACCEPT' => 'application/json']
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client->request(
            'PUT',
            sprintf('/articles/%d.json', $id),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"title":"barfoo","content":"barfoo"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_CREATED, false);
    }

    /**
     * Test PUT HTTP method with bad parameters.
     */
    public function testJsonPutBadParameters()
    {
        $id = 0;
        $this->client = static::createClient();
        $token = $this->getApiToken();

        $this->client->request(
            'PUT',
            sprintf('/articles/%d.json', $id),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"bar":"foo"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test PATCH HTTP method.
     */
    public function testJsonPatch()
    {
        $this->client = static::createClient();
        $token = $this->getApiToken();
        $article = $this->getArticle();
        $this->client->request(
            'PATCH',
            sprintf('/articles/%d.json', $article->getId()),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'ACCEPT' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"content":"def"}'
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * Test PATCH HTTP method with bad parameters.
     */
    public function testJsonPatchBadParameters()
    {
        $this->client = static::createClient();
        $token = $this->getApiToken();
        $article = $this->getArticle();
        $this->client->request(
            'PATCH',
            sprintf('/articles/%d.json', $article->getId()),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ],
            '{"foobar":"foobar"}'
        );
        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Test DELETE HTTP method.
     */
    public function testDelete()
    {
        $this->client = static::createClient();
        $token = $this->getApiToken();
        $article = $this->getArticle();
        $route = $this->getUrl('api_get_article', ['id' => $article->getId(), '_format' => 'json']);
        $this->client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ]
        );
        $response = $this->client->getResponse();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * Test DELETE HTTP method when not found.
     */
    public function testDeleteNotFound()
    {
        $id = 0;
        $this->client = static::createClient();
        $token = $this->getApiToken();
        $route = $this->getUrl('api_get_article', ['id' => $id, '_format' => 'json']);
        $this->client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Auth-Token' => $token,
            ]
        );
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * Assert that response was correctly formatted in JSON.
     */
    protected function assertJsonResponse($response, $statusCode = Response::HTTP_OK, $checkValidJson = true, $contentType = 'application/json')
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(),
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', $contentType),
            $response->headers
        );
        if ($checkValidJson) {
            $decode = json_decode($response->getContent());
            $this->assertTrue(($decode != null && $decode != false),
                'is response valid json: ['.$response->getContent().']'
            );
        }
    }

    /**
     * Get Api Key.
     *
     * @return string
     */
    protected function getApiToken()
    {
        return $this->fixtures->getReference('user')->getApiKey();
    }

    /**
     * Get Articles.
     *
     * @return ArticleInterface
     */
    protected function getArticle()
    {
        return $this->fixtures->getReference('article');
    }
}
