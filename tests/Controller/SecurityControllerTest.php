<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginSuccess(): void
    {
        // accès page de login
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Se connecter');

        // formulaire de connexion avec identifiants corrects
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'marie';
        $form['_password'] = 'marie123';
        $this->client->submit($form);
        // test redirection après authentification
        $this->assertResponseRedirects();

        // test contenu de la page de login après redirection
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a', 'Se déconnecter');
    }

    public function testLoginFail(): void
    {
        // page de login
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Se connecter');

        // formulaire de connexion avec identifiants incorrects
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'utilisateur';
        $form['_password'] = 'mauvaismdp';
        $this->client->submit($form);
        // test redirection après échec
        $this->assertResponseRedirects('/login');
        
        // test contenu de la page de login après redirection
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-danger', "Nom d'utilisateur ou mot de passe incorrect.");
    }
}