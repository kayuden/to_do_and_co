<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testUpgradePassword(): void
    {
        $user = new User();
        $user->setUsername('test_user');
        $user->setEmail('test@exemple.com');
        $user->setPassword('old_password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $newHashedPassword = 'new_hashed_password';
        $this->userRepository->upgradePassword($user, $newHashedPassword);
        $this->entityManager->refresh($user);

        $this->assertEquals($newHashedPassword, $user->getPassword());
    }

    public function testUpgradePasswordThrowsException(): void
    {
        $nonUser = new InMemoryUser('test', 'password');

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Instances of "Symfony\Component\Security\Core\User\InMemoryUser" are not supported.');

        $this->userRepository->upgradePassword($nonUser, 'new_password');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }

        $this->userRepository = null;
    }
}