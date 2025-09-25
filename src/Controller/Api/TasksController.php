<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/tasks', name: 'app_tasks')]
final class TasksController extends AbstractController
{

    private const LIMIT = 10;

    #[Route(name: 'app_tasks_index', methods: ['GET'])]
    public function index(Request $request, TaskRepository $taskRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);

        $paginator = $taskRepository->findPaginatedTasks($page, self::LIMIT);

        $data = [];
        /* @var $task Task */
        foreach ($paginator as $task) {
            $data[] = $task->getValues();
        }

        return $this->json([
            'data' => $data,
            'pages' => [
                'totalItems' => $paginator->count(),
                'currentPage' => $page,
                'itemsPerPage' => self::LIMIT,
                'totalPages' => ceil($paginator->count() / self::LIMIT),
            ],
        ]);
    }

    #[Route(name: 'app_tasks_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->json([
                'task' => $task->getValues(),
                'status' => 'success',
            ], Response::HTTP_CREATED);
        }

        $errors = [];
        foreach ($form->getErrors(true, true) as $formError) {
            $errors[] = $formError->getMessage();
        }

        $logger->error('Errors on creating task', $errors);

        return $this->json([
            'errors' => $errors,
        ]);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/{id}', name: 'app_tasks_show', methods: ['GET'], format: 'json')]
    public function show(int $id, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        $task = $entityManager->find(Task::class, $id);

        if (!$task) {
            $logger->warning('Task not found');
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'task' => $task->getValues(),
        ]);
    }

    #[Route('/{id}', name: 'app_tasks_edit', methods: ['PUT'])]
    public function edit(Request $request,int $id, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        $task = $entityManager->find(Task::class, $id);

        if (!$task) {
            $logger->warning('Task not found');
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(TaskType::class, $task);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json([
                'task' => $task->getValues(),
                'status' => 'success',
            ], Response::HTTP_ACCEPTED);
        }

        $errors = [];
        foreach ($form->getErrors(true, true) as $formError) {
            $errors[] = $formError->getMessage();
        }

        $logger->error('Errors on updating task', $errors);

        return $this->json([
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'app_tasks_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $task = $entityManager->find(Task::class, $id);

        if (!$task) {
            $logger->warning('Task not found');
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($task);
        $entityManager->flush();
        return $this->json(
            ['status' => 'success'],
            Response::HTTP_ACCEPTED
        );
    }
}
