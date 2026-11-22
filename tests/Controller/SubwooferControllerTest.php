<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SubwooferControllerTest extends WebTestCase
{
    public function testShowRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/subwoofer/1');

        self::assertResponseRedirects('/login');
    }
}
