<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAccessSubscriber extends AbstractController implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9], // Priority just before firewall (10)
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Vérifie si la route commence par /users
        if (str_starts_with($request->getPathInfo(), '/users')) {
            // Si l'utilisateur n'est pas connecté ou n'est pas admin
            if (!$this->security->isGranted('ROLE_ADMIN')) {
                $this->addFlash('danger', 'Accès réservé aux administrateurs.');
                $event->setResponse(
                    new RedirectResponse($this->urlGenerator->generate('task_list'))
                );
            }
        }
    }
}