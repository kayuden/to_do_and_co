<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testConstructor(): void
    {
        $this->assertNull($this->user->getId());
        $this->assertNull($this->user->getUsername());
        $this->assertNull($this->user->getEmail());
        $this->assertNull($this->user->getPassword());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
        $this->assertCount(0, $this->user->getTasks());
    }

    public function testGetterAndSetter(): void
    {
        $this->user->setUsername('testuser');
        $this->assertEquals('testuser', $this->user->getUsername());
        
        $this->user->setEmail('test@exemple.com');
        $this->assertEquals('test@exemple.com', $this->user->getEmail());
        
        $this->user->setPassword('hashedpassword');
        $this->assertEquals('hashedpassword', $this->user->getPassword());
        
        $this->user->setPlainPassword('plainpass');
        $this->assertEquals('plainpass', $this->user->getPlainPassword());
    }

    public function testRolesManagement(): void
    {
        // test défaut ROLE_USER
        $this->assertContains('ROLE_USER', $this->user->getRoles());
        
        // test avec ROLE_ADMIN
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(2, array_unique($roles));
    }

    public function testTasksManagement(): void
    {
        $task = new Task();
        
        $this->user->addTask($task);
        $this->assertCount(1, $this->user->getTasks());
        $this->assertSame($this->user, $task->getAuthor());
        
        $this->user->removeTask($task);
        $this->assertCount(0, $this->user->getTasks());
        $this->assertNull($task->getAuthor());
    }

    public function testUserIdentifier(): void
    {
        $this->user->setUsername('testuser');
        $this->assertEquals('testuser', $this->user->getUserIdentifier());
        
        $emptyUser = new User(); // On a besoin d'une nouvelle instance ici
        $this->expectException(\LogicException::class);
        $emptyUser->getUserIdentifier();
    }

    public function testEraseCredentials(): void
    {
        $this->user->setPlainPassword('password123');
        $this->assertEquals('password123', $this->user->getPlainPassword());
        
        $this->user->eraseCredentials();
        $this->assertNull($this->user->getPlainPassword());
    }

    public function testIsAnonymous(): void
    {
        $this->assertFalse($this->user->isAnonymous());
        
        //test auteur anonyme
        $this->user->setUsername(User::ANONYMOUS_USER);
        $this->assertTrue($this->user->isAnonymous());
        
        //test auteur non anonyme
        $this->user->setUsername('regular_user');
        $this->assertFalse($this->user->isAnonymous());
    }
}
