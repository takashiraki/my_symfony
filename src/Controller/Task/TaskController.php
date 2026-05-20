<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Message\SendTaskNotificationMessage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TaskController extends AbstractController
{
    #[Route('/task/create', name: 'app_task_create')]
    public function index(): Response
    {
        return $this->render('task/task/index.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }

    #[Route('/task/{id}/created', name: 'app_task_created', methods: ['GET'])]
    public function created(Task $task): Response
    {
        return $this->render('task/task/created.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/store', name: 'app_task_store', methods: ['POST'])]
    #[IsCsrfTokenValid('task_create', tokenKey: '_csrf_token')]
    public function create(
        Request $http_request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
    ): Response {
        $value = $http_request->request->get('value');
        $description = $http_request->request->get('description');
        $due_date = $http_request->request->get('due_date');
        $status = $http_request->request->get('status');

        $task = new Task();
        $task->setValue($value);
        $task->setDescription($description);
        $task->setDueDate(new DateTime($due_date));
        $task->setStatus(TaskStatus::tryFrom($status));

        $errors = $validator->validate($task);

        if (count($errors) > 0) {
            return $this->render('task/task/index.html.twig', [
                'controller_name' => 'TaskController',
                'errors' => $errors,
            ]);
        }

        $entityManager->persist($task);
        $entityManager->flush();

        $messageBus->dispatch(new SendTaskNotificationMessage($task->getId()));

        return $this->redirectToRoute('app_task_created', ['id' => $task->getId()]);
    }
}
