<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        
        // connexion utilisateur
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByUsername('john');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/');

        // test page OK
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('Accueil - ToDo & Co', $crawler->filter('title')->text());
        $this->assertStringContainsString(
            'Bienvenue sur ToDo & Co, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !',
            $crawler->filter('h1')->text()
        );
    }
}
