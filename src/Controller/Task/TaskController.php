<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Account;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Form\TaskType;
use App\Message\SendTaskNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class TaskController extends AbstractController
{
    #[Route('/task/{id}/created', name: 'app_task_created', methods: ['GET'])]
    public function created(Task $task): Response
    {
        return $this->render('task/task/created.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/create', name: 'app_task_create')]
    public function create(
        Request $http_request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
    ): Response {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($http_request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Account $account */
            $account = $this->getUser();
            $task->setUser($account->getUser());

            $entityManager->persist($task);
            $entityManager->flush();

            $messageBus->dispatch(new SendTaskNotificationMessage($task->getId()));

            return $this->redirectToRoute('app_task_created', ['id' => $task->getId()]);
        }

        return $this->render('task/task/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/{id}/complete', name: 'app_task_complete', methods: ['POST'])]
    #[IsCsrfTokenValid('task_create', tokenKey: '_csrf_token')]
    public function complete(
        EntityManagerInterface $entityManager,
        string $id,
    ): Response {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (! $task) {
            return $this->redirectToRoute('app_home');
        }

        $task->setStatus(TaskStatus::DONE);

        $entityManager->flush();

        $this->addFlash('success', 'Task completed.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/task/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    #[IsCsrfTokenValid('task_create', tokenKey: '_csrf_token')]
    public function delete(
        string $id,
        EntityManagerInterface $entityManager
    ): Response {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (! $task) {
            return $this->redirectToRoute('app_home');
        }

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'Task deleted.');

        return $this->redirectToRoute('app_home');
    }

    public function edit(
        string $id,
        EntityManagerInterface $entityManager
    ): Response {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (! $task) {
            return $this->redirectToRoute('app_home');
        }
    }
}
