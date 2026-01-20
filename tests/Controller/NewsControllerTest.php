<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NewsControllerTest extends WebTestCase
{
    public function testGetFeedsReturnsJson(): void
    {
        $client = static::createClient();

        $client->request('GET', '/feeds');

        $this->assertResponseIsSuccessful();

        $this->assertJson($client->getResponse()->getContent());

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
}
