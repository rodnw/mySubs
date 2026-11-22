<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SoundSystemControllerTest extends WebTestCase
{
    public function testIndexRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sound-system');

        self::assertResponseRedirects('/login');
    }
}
