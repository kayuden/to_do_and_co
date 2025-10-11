<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function loginAsAdmin()
    {
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testAdmin = $userRepository->findOneBy(['email' => 'admin@exemple.com']);

        $this->client->loginUser($testAdmin);
    }

    public function testListUsersRequiresAdminRole(): void
    {
        $this->client->request('GET', '/users');

        // test redirection vers login si pas connecté
        $this->assertResponseRedirects('/login');
    }

    public function testListUsersDisplaysUserList(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testCreateUserFormDisplaysCorrectly(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateUserSuccessfully(): void
    {
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->filter('form')->form([
            'user[username]' => 'nouveau',
            'user[email]' => 'nouveau@exemple.com',
            'user[plainPassword][first]' => 'password123',
            'user[plainPassword][second]' => 'password123',
            'user[roles]' => ['ROLE_USER']
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
    }

    public function testEditUserSuccessfully(): void
    {
        $this->loginAsAdmin();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'marie']);

        $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');

        $form = $crawler->filter('form')->form([
            'user[username]' => 'mod_user',
            'user[email]' => 'mod_user@exemple.com'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été modifié.");
    }

    public function testDeleteUserSuccessfully(): void
    {
        $this->loginAsAdmin();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'john@exemple.com']);

        $crawler = $this->client->request('GET', '/users');
        
        $form = $crawler->filter(sprintf('form[action="/users/%d/delete"]', $testUser->getId()))->form();
        $this->client->submit($form);

        // test redirection et message de succès
        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été supprimé");
        // test utilisateur supprimé
        $this->assertNull(
            $userRepository->find($testUser->getId()),
            "L'utilisateur n'a pas été supprimé de la base de données"
        );
    }

    public function testDeleteUserWithInvalidCsrfToken(): void
    {
        $this->loginAsAdmin();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'admin@exemple.com']);

        $this->client->request(
            'POST',
            '/users/' . $testUser->getId() . '/delete',
            ['_token' => 'invalid_token']
        );

        // test utilisateur non supprimé
        $this->assertNotNull(
            $userRepository->find($testUser->getId())
        );

        $this->client->followRedirect();
        // test message d'erreur
        $this->assertSelectorTextContains(
            '.alert-danger',
            "Échec de la suppression : token CSRF invalide."
        );
    }
}
