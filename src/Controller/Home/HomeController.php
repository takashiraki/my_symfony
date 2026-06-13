<?php

declare(strict_types=1);

namespace App\Controller\Home;

use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(
        TaskRepository $tasksRepository,
    ): Response {
        /** @var \App\Entity\Account|null $account */
        $account = $this->getUser();
        $user = $account?->getUser();
        $tasks = $user ? $tasksRepository->findByUser($user) : [];
        $total = count($tasks);
        $progress = count(array_filter($tasks, function (Task $task) {
            return $task->getStatus() === TaskStatus::IN_PROGRESS;
        }));
        $done = count(array_filter($tasks, function (Task $task) {
            return $task->getStatus() === TaskStatus::DONE;
        }));

        return $this->render('home/index.html.twig', [
            'controller_name' => 'Home/HomeController',
            'tasks' => $tasks,
            'total' => $total,
            'progress' => $progress,
            'done' => $done,
        ]);
    }
}
