<?php

namespace App\Tests\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessSubscriberTest extends WebTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = \App\EventSubscriber\AdminAccessSubscriber::getSubscribedEvents();

        $this->assertIsArray($events);
        $this->assertArrayHasKey(\Symfony\Component\HttpKernel\KernelEvents::REQUEST, $events);
        $this->assertEquals(['onKernelRequest', -10], $events[\Symfony\Component\HttpKernel\KernelEvents::REQUEST]);
    }
    
    public function testAnonymousUserIsRedirectedFromUsersPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/users');
        $response = $client->getResponse();

        // test redirection vers login
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    public function testAdminCanAccessUsersPage(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(\App\Repository\UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'admin@exemple.com']);

        $client->loginUser($adminUser);
        $client->request('GET', '/users');

        // test ok
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNonAdminUserIsRedirectedFromUsersPage(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(\App\Repository\UserRepository::class);
        $regularUser = $userRepository->findOneBy(['email' => 'anonyme@exemple.com']);

        $client->loginUser($regularUser);
        $client->request('GET', '/users');

        // test redirection page autorisée 
        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertResponseRedirects('/tasks');
    }
}
