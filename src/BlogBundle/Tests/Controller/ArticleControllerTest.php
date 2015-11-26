<?php

namespace BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase as WebTestCase;
use FOS\RestBundle\Util\Codes;
use BlogBundle\Tests\Fixtures\Entity\LoadArticleData;
use BlogBundle\Tests\Fixtures\Entity\LoadUserData;

class ArticleControllerTest extends WebTestCase
{
    /**
     * Test GET HTTP method.
     */
    public function testGet()
    {
        $this->client = static::createClient();
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadArticleData'];
        $this->loadFixtures($fixtures);
        $articles = LoadArticleData::$articles;
        $route = $this->getUrl('api_get_articles', ['_format' => 'json', 'limit' => 2]);
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $content = $response->getContent();
        $decoded = json_decode($content, true);
        $this->assertEquals(count($decoded), 2);
        foreach ($decoded as $article) {
            $this->assertTrue(isset($article['id']));
            $this->assertTrue(isset($article['title']));
            $this->assertTrue(isset($article['content']));
        }
        $article = array_pop($articles);
        $route = $this->getUrl('api_get_article', ['id' => $article->getId(), '_format' => 'json']);
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
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
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadArticleData'];
        $this->loadFixtures($fixtures);
        $articles = LoadArticleData::$articles;
        $article = array_pop($articles);

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
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);

        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"foo","content":"bar"}'
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_CREATED, false);
    }

    /**
     * Test POST HTTP method with bad parameters.
     */
    public function testJsonPostBadParameters()
    {
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);

        $this->client->request(
            'POST',
            '/articles.json',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"foo":"bar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    /**
     * Test PUT HTTP method when modifying.
     */
    public function testJsonPutModify()
    {
        $this->client = static::createClient();
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadArticleData'];
        $this->loadFixtures($fixtures);
        $articles = LoadArticleData::$articles;
        $article = array_pop($articles);

        $this->client->request(
            'GET',
            sprintf('/articles/%d.json', $article->getId()),
            ['ACCEPT' => 'application/json']
        );

        $this->assertEquals(Codes::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);
        $this->client->request(
            'PUT',
            sprintf('/articles/%d.json', $article->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"foobar","content":"foobar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_SEE_OTHER, false);
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

        $this->client->request(
            'GET',
            sprintf('/articles/%d.json', $id),
            ['ACCEPT' => 'application/json']
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);
        $this->client->request(
            'PUT',
            sprintf('/articles/%d.json', $id),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"barfoo","content":"barfoo"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_CREATED, false);
    }

    /**
     * Test PUT HTTP method with bad parameters.
     */
    public function testJsonPutBadParameters()
    {
        $id = 0;
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);

        $this->client->request(
            'PUT',
            sprintf('/articles/%d.json', $id),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"bar":"foo"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    /**
     * Test PATCH HTTP method.
     */
    public function testJsonPatch()
    {
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadArticleData'];
        $this->loadFixtures($fixtures);
        $articles = LoadArticleData::$articles;
        $article = array_pop($articles);
        $this->client->request(
            'PATCH',
            sprintf('/articles/%d.json', $article->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'ACCEPT' => 'application/json'],
            '{"content":"def"}'
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Codes::HTTP_NO_CONTENT);
    }

    /**
     * Test PATCH HTTP method with bad parameters.
     */
    public function testJsonPatchBadParameters()
    {
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadArticleData'];
        $this->loadFixtures($fixtures);
        $articles = LoadArticleData::$articles;
        $article = array_pop($articles);
        $this->client->request(
            'PATCH',
            sprintf('/articles/%d.json', $article->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"foobar":"foobar"}'
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    /**
     * Test DELETE HTTP method.
     */
    public function testDelete()
    {
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadArticleData'];
        $this->loadFixtures($fixtures);
        $article = array_pop(LoadArticleData::$articles);
        $route = $this->getUrl('api_get_article', ['id' => $article->getId(), '_format' => 'json']);
        $this->client->request('DELETE', $route);
        $response = $this->client->getResponse();
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Codes::HTTP_NO_CONTENT);
    }

    /**
     * Test DELETE HTTP method when not found.
     */
    public function testDeleteNotFound()
    {
        $id = 0;
        $this->client = static::createClient([], [
            'HTTP_API-Auth' => $this->getApiKey()
        ]);
        $route = $this->getUrl('api_get_article', ['id' => $id, '_format' => 'json']);
        $this->client->request('DELETE', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }

    /**
     * Assert that response was correctly formatted in JSON.
     */
    protected function assertJsonResponse($response, $statusCode = Codes::HTTP_OK, $checkValidJson = true, $contentType = 'application/json')
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
     * Get Api Key
     *
     * @return string
     */
    protected function getApiKey()
    {
        $this->client = static::createClient();
        $fixtures = ['BlogBundle\Tests\Fixtures\Entity\LoadUserData'];
        $this->loadFixtures($fixtures);
        $user = array_pop(LoadUserData::$users);
        return $user->getApiKey();
    }
}
