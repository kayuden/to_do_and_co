<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $tasks,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/{filter}', name: 'task_list', methods: ['GET'], requirements: ['filter' => 'all|done|todo'], defaults: ['filter' => 'all'])]
    public function list(TaskRepository $taskRepository, string $filter): Response
    {
        switch ($filter) {
            case 'done':
                $tasks = $taskRepository->findBy(['isDone' => true]);
                break;
            case 'todo':
                $tasks = $taskRepository->findBy(['isDone' => false]);
                break;
            default:
                $tasks = $taskRepository->findAll();
                break;
        }

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'filter' => $filter,
        ]);
    }

    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof \App\Entity\User) {
                $this->addFlash('error', 'Votre session a expiré. Veuillez vous reconnecter.');
                return $this->redirectToRoute('app_login');
            }

            $task->setAuthor($user);

            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(#[MapEntity(expr: 'repository.find(id)')] Task $task, Request $request): Response
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/{id<\d+>}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggle(#[MapEntity(expr: 'repository.find(id)')] Task $task, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('toggle' . (int) $task->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('task_list');
        }

        $task->toggle(!$task->isDone());
        $this->em->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route('/{id<\d+>}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(#[MapEntity(expr: 'repository.find(id)')] Task $task, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete'. (int) $task->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('task_list');
        }

        $this->em->remove($task);
        $this->em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
