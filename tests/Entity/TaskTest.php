<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskTest extends TestCase //php bin/phpunit --coverage-html public/test-coverage
{
    private ValidatorInterface $validator;
    private Task $task;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        
        $this->task = new Task();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->task->getCreatedAt());
        $this->assertFalse($this->task->isDone());
        $this->assertNull($this->task->getId());
    }

    public function testValidEntity(): void
    {
        $this->task->setTitle('Tâche de test');
        $this->task->setContent('Description de la tâche.');
        $this->task->setIsDone(false);

        $this->assertEquals('Tâche de test', $this->task->getTitle());
        $this->assertEquals('Description de la tâche.', $this->task->getContent());
        $this->assertFalse($this->task->isDone());
    }

    public function testInvalidEntity(): void
    {
        // test titre et contenu vide
        $errors = $this->validator->validate($this->task);
        $this->assertCount(2, $errors);

        // tests des messages d'erreur spécifiques
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }
        
        $this->assertContains('Vous devez saisir un titre.', $messages);
        $this->assertContains('Vous devez saisir du contenu.', $messages);
    }

    public function testToggle(): void
    {
        $this->task->setIsDone(false);
        $this->assertFalse($this->task->isDone());

        // test tâche terminée
        $this->task->toggle(true);
        $this->assertTrue($this->task->isDone());

        // test tâche non terminée
        $this->task->toggle(false);
        $this->assertFalse($this->task->isDone());
    }

    public function testCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2025-10-08');
        $this->task->setCreatedAt($date);
        $this->assertEquals($date, $this->task->getCreatedAt());
    }

    public function testAuthor(): void
    {
        $this->assertNull($this->task->getAuthor());

        $author = $this->createMock(User::class);
        $this->task->setAuthor($author);
        $this->assertSame($author, $this->task->getAuthor());
    }
}
