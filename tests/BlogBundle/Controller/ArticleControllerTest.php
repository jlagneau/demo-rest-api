<?php

namespace Tests\BlogBundle\Tests\Controller;

use Tests\BlogBundle\CustomWebTestCase as WebTestCase;
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

        $this->client = static::createClient();

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
        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
            ],
            '{"title":"foo","content":"bar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_CREATED, false);
    }

    /**
     * Test POST HTTP method with bad parameters.
     */
    public function testJsonPostBadParameters()
    {
        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
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
        $article = $this->getArticle();
        $route = $this->getUrl('api_put_article', ['id' => $article->getId(), '_format' => 'json']);

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

        $this->client->request(
            'PUT',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
            ],
            '{"title":"foobar","content":"foobar"}'
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($this->client->getResponse(), Response::HTTP_SEE_OTHER, false);
        $this->assertTrue(
            $response->headers->has('Location')
        );
    }

    /**
     * Test PUT HTTP method when creating.
     */
    public function testJsonPutCreate()
    {
        $id = 0;
        $route = $this->getUrl('api_put_article', ['id' => $id, '_format' => 'json']);

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

        $this->client->request(
            'PUT',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
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
        $id = 0;
        $route = $this->getUrl('api_put_article', ['id' => $id, '_format' => 'json']);

        $this->client->request(
            'PUT',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
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
        $article = $this->getArticle();
        $route = $this->getUrl('api_patch_article', ['id' => $article->getId(), '_format' => 'json']);

        $this->client->request(
            'PATCH',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'ACCEPT' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
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
        $article = $this->getArticle();
        $route = $this->getUrl('api_patch_article', ['id' => $article->getId(), '_format' => 'json']);

        $this->client->request(
            'PATCH',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
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
        $article = $this->getArticle();
        $route = $this->getUrl('api_get_article', ['id' => $article->getId(), '_format' => 'json']);

        $this->client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
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

        $this->client->request(
            'DELETE',
            $route,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'PHP_AUTH_USER' => 'test',
                'PHP_AUTH_PW' => 'test',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Response::HTTP_NOT_FOUND);
    }

    protected function getUser()
    {
        return $this->fixtures->getReference('user');
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
