<?php

namespace BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase as WebTestCase;
use FOS\RestBundle\Util\Codes;
use BlogBundle\Tests\Fixtures\Entity\LoadPostData;

class PostControllerTest extends WebTestCase
{
    /**
     * Test GET HTTP method.
     */
    public function testGet()
    {
        $this->client = static::createClient();
        $fixtures = array('BlogBundle\Tests\Fixtures\Entity\LoadPostData');
        $this->loadFixtures($fixtures);
        $posts = LoadPostData::$posts;
        $route =  $this->getUrl('api_v1_get_posts', array('_format' => 'json', 'limit' => 2));
        $this->client->request('GET', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_OK);
        $content = $response->getContent();
        $decoded = json_decode($content, true);
        $this->assertEquals(count($decoded), 2);
        foreach ($decoded as $post) {
            $this->assertTrue(isset($post['id']));
            $this->assertTrue(isset($post['title']));
            $this->assertTrue(isset($post['content']));
        }
        $post = array_pop($posts);
        $route =  $this->getUrl('api_v1_get_post', array('id' => $post->getId(), '_format' => 'json'));
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
        $fixtures   = array('BlogBundle\Tests\Fixtures\Entity\LoadPostData');
        $this->loadFixtures($fixtures);
        $posts      = LoadPostData::$posts;
        $post       = array_pop($posts);

        $this->client->request(
            'HEAD',
            sprintf('/v1/posts/%d.json', $post->getId()),
            array('ACCEPT' => 'application/json')
        );
        $response = $this->client->getResponse();

        $this->assertJsonResponse($response, 200, false);
        $this->assertEquals($response->getContent(), null);

        $this->client->request(
            'HEAD',
            '/v1/posts.json',
            array('ACCEPT' => 'application/json')
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

        $this->client->request(
            'POST',
            '/v1/posts.json',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"title":"foo","content":"bar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_CREATED, false);
    }

    /**
    * Test POST HTTP method with bad parameters.
    */
    public function testJsonPostBadParameters()
    {
        $this->client = static::createClient();

        $this->client->request(
            'POST',
            '/v1/posts.json',
            array(),
            array(),
            array('CONTENT_TYPE'  => 'application/json'),
            '{"foo":"bar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    /**
    * Test PUT HTTP method when modifying.
    */
    public function testJsonPutModify()
    {
        $this->client   = static::createClient();
        $fixtures       = array('BlogBundle\Tests\Fixtures\Entity\LoadPostData');
        $this->loadFixtures($fixtures);
        $posts          = LoadPostData::$posts;
        $post           = array_pop($posts);

        $this->client->request(
            'GET',
            sprintf('/v1/posts/%d.json', $post->getId()),
            array('ACCEPT' => 'application/json')
        );

        $this->assertEquals(Codes::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client->request(
            'PUT',
            sprintf('/v1/posts/%d.json', $post->getId()),
            array(),
            array(),
            array('CONTENT_TYPE'  => 'application/json'),
            '{"title":"foobar","content":"foobar"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_SEE_OTHER, false);
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Location',
                sprintf('http://localhost/v1/posts/%d.json', $post->getId())
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
            sprintf('/v1/posts/%d.json', $id),
            array('ACCEPT' => 'application/json')
        );

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client->request(
            'PUT',
            sprintf('/v1/posts/%d.json', $id),
            array(),
            array(),
            array('CONTENT_TYPE'  => 'application/json'),
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
        $this->client = static::createClient();

        $this->client->request(
            'PUT',
            sprintf('/v1/posts/%d.json', $id),
            array(),
            array(),
            array('CONTENT_TYPE'  => 'application/json'),
            '{"bar":"foo"}'
        );

        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    /**
    * Test PATCH HTTP method.
    */
    public function testJsonPatch()
    {
        $this->client = static::createClient();
        $fixtures = array('BlogBundle\Tests\Fixtures\Entity\LoadPostData');
        $this->loadFixtures($fixtures);
        $posts = LoadPostData::$posts;
        $post = array_pop($posts);
        $this->client->request(
            'PATCH',
            sprintf('/v1/posts/%d.json', $post->getId()),
            array(),
            array(),
            array('CONTENT_TYPE'  => 'application/json', 'ACCEPT' => 'application/json'),
            '{"content":"def"}'
        );
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Codes::HTTP_NO_CONTENT);
    }

    /**
    * Test PATCH HTTP method with bad parameters.
    */
    public function testJsonPatchBadParameters()
    {
        $this->client = static::createClient();
        $fixtures = array('BlogBundle\Tests\Fixtures\Entity\LoadPostData');
        $this->loadFixtures($fixtures);
        $posts = LoadPostData::$posts;
        $post = array_pop($posts);
        $this->client->request(
            'PATCH',
            sprintf('/v1/posts/%d.json', $post->getId()),
            array(),
            array(),
            array('CONTENT_TYPE'  => 'application/json'),
            '{"foobar":"foobar"}'
        );
        $this->assertJsonResponse($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    /**
    * Test DELETE HTTP method.
    */
    public function testDelete()
    {
        $this->client = static::createClient();
        $fixtures = array('BlogBundle\Tests\Fixtures\Entity\LoadPostData');
        $this->loadFixtures($fixtures);
        $post = array_pop(LoadPostData::$posts);
        $route =  $this->getUrl('api_v1_get_post', array('id' => $post->getId(), '_format' => 'json'));
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
        $this->client = static::createClient();
        $route =  $this->getUrl('api_v1_get_post', array('id' => $id, '_format' => 'json'));
        $this->client->request('DELETE', $route);
        $response = $this->client->getResponse();
        $this->assertJsonResponse($response, Codes::HTTP_NOT_FOUND);
    }

    /**
    * Assert that response was correctly formatted in JSON.
    */
    protected function assertJsonResponse($response, $statusCode = Codes::HTTP_OK, $checkValidJson =  true, $contentType = 'application/json')
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
}
