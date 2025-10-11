<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $testUser;

    private function createTestTask(string $title = 'Tâche', string $content = 'Contenu de la tâche'): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setContent($content);
        $task->setAuthor($this->testUser);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return $task;
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        
        $container = $this->client->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        // nettoyage bdd et rechargement des fixtures avant chaque test
        $connection = $this->entityManager->getConnection();
        foreach (['task', 'user'] as $table) {
            $connection->executeQuery('DELETE FROM ' . $table);
            $connection->executeQuery('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
        }
        $loader = $container->get('doctrine.fixtures.loader');
        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor(
            $this->entityManager,
            new \Doctrine\Common\DataFixtures\Purger\ORMPurger($this->entityManager)
        );
        $executor->execute($loader->getFixtures());

        // utilisateur 'john' des fixtures
        $userRepository = $this->entityManager->getRepository(User::class);
        $this->testUser = $userRepository->findOneByUsername('john'); 
    }

    public function testListTasksRequiresAuthentication(): void
    {
        $this->client->followRedirects(false);
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
        $this->client->followRedirects(true);
    }

    public function testCreateTaskRequiresAuthentication(): void
    {
        $this->client->followRedirects(false);
        $this->client->request('GET', '/tasks/create');
        $this->assertResponseRedirects('/login');
        $this->client->followRedirects(true);
    }

    public function testEditTaskRequiresAuthentication(): void
    {
        $task = $this->createTestTask();

        $this->client->followRedirects(false);
        $this->client->request('GET', sprintf('/tasks/%d/edit', $task->getId()));
        $this->assertResponseRedirects('/login');
        $this->client->followRedirects(true);
    }

    public function testToggleTaskRequiresAuthentication(): void
    {
        $task = $this->createTestTask();

        $this->client->followRedirects(false);
        $this->client->request('POST', sprintf('/tasks/%d/toggle', $task->getId()));
        $this->assertResponseRedirects('/login');
        $this->client->followRedirects(true);
    }

    public function testDeleteTaskRequiresAuthentication(): void
    {
        $task = $this->createTestTask();

        $this->client->followRedirects(false);
        $this->client->request('POST', sprintf('/tasks/%d/delete', $task->getId()));
        $this->assertResponseRedirects('/login');
        $this->client->followRedirects(true);
    }

    public function testAuthenticatedUserCanListAllTasks(): void
    {
        $this->client->loginUser($this->testUser);

        // vérification présence des 5 tâches des fixtures
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(5, $crawler->filter('.card')->count(), 'Toutes les tâches devraient être affichées');
        $this->assertStringContainsString('Préparer la réunion de lundi', $crawler->html());
        $this->assertStringContainsString('Sauvegarde hebdomadaire du serveur', $crawler->html());
    }

    public function testAuthenticatedUserCanListDoneTasks(): void
    {
        $this->client->loginUser($this->testUser);

        // test nombre de tâches terminées
        $crawler = $this->client->request('GET', '/tasks/done');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(1, $crawler->filter('.card')->count());

        // test tâche terminée présente
        $this->assertStringContainsString('Sauvegarde hebdomadaire du serveur', $crawler->html());

        // test tâche non terminée absente
        $this->assertStringNotContainsString('Préparer la réunion de lundi', $crawler->html());
    }

    public function testAuthenticatedUserCanListTodoTasks(): void
    {
        $this->client->loginUser($this->testUser);

        // test nombre de tâches non terminées
        $crawler = $this->client->request('GET', '/tasks/todo');
        $this->assertResponseIsSuccessful();
        $this->assertEquals(4, $crawler->filter('.card')->count(), 'Quatre tâches devraient être non terminées');

        // test tâche non terminée présente
        $this->assertStringContainsString('Préparer la réunion de lundi', $crawler->html());

        // test tâche terminée absente
        $this->assertStringNotContainsString('Sauvegarde hebdomadaire du serveur', $crawler->html());
    }

    public function testAuthenticatedUserCanCreateTask(): void
    {
        $this->client->loginUser($this->testUser);

        $taskCount = count($this->entityManager->getRepository(Task::class)->findAll());

        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Nouvelle tâche',
            'task[content]' => 'Nouveau contenu'
        ]);

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $this->entityManager->clear();
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        
        $this->assertCount($taskCount + 1, $tasks);
        $task = end($tasks);
        $this->assertEquals('Nouvelle tâche', $task->getTitle());
        $this->assertEquals('Nouveau contenu', $task->getContent());
        $this->assertEquals($this->testUser->getId(), $task->getAuthor()->getId());
    }

    public function testAuthenticatedUserCanEditTask(): void
    {
        $this->client->loginUser($this->testUser);

        $task = $this->createTestTask('Original Title', 'Original Content');
        
        $taskId = $task->getId();
        $this->entityManager->clear();

        $crawler = $this->client->request('GET', sprintf('/tasks/%d/edit', $taskId));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Titre modifié',
            'task[content]' => 'Contenu modifié'
        ]);

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        $this->assertEquals('Titre modifié', $updatedTask->getTitle());
        $this->assertEquals('Contenu modifié', $updatedTask->getContent());
    }

    public function testAuthenticatedUserCanToggleTask(): void
    {
        $this->client->loginUser($this->testUser);

        $task = $this->createTestTask('Tâche', 'Changer le statut');
        
        $taskId = $task->getId();
        $initialState = $task->isDone();
        $this->entityManager->clear();

        $crawler = $this->client->request('GET', '/tasks');
        $form = $crawler->filter(sprintf('form[action="%s"]', sprintf('/tasks/%d/toggle', $taskId)))->form();
        
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        $this->assertNotEquals($initialState, $updatedTask->isDone());
    }

    public function testAuthenticatedUserCanDeleteOwnTask(): void
    {
        $this->client->loginUser($this->testUser);

        $task = $this->createTestTask('Tâche à supprimer', 'Contenu à supprimer');

        $taskId = $task->getId();

        $crawler = $this->client->request('GET', '/tasks');
        $form = $crawler->filter(sprintf('form[action="%s"]', sprintf('/tasks/%d/delete', $taskId)))->form();
        
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        
        $deletedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        $this->assertNull($deletedTask);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
