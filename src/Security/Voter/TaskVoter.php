<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const DELETE = 'delete';

    public function __construct(
        private Security $security
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::DELETE && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Task $task */
        $task = $subject;

        return match($attribute) {
            self::DELETE => $this->canDelete($task, $user),
            default => false,
        };
    }

    private function canDelete(Task $task, UserInterface $user): bool
    {
        // Si l'utilisateur est l'auteur de la tâche, il peut la supprimer
        if ($task->getAuthor() === $user) {
            return true;
        }

        // Si la tâche est liée à l'utilisateur "anonyme" et que l'utilisateur a le ROLE_ADMIN, il peut la supprimer
        $author = $task->getAuthor();
        if ($author instanceof User && $author->isAnonymous() && $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
}