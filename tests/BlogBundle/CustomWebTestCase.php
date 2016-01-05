<?php

namespace tests\BlogBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase as WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomWebTestCase extends WebTestCase
{
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
}
