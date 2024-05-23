<?php

namespace App\Controller\Tasks;

use App\Entity\Tasks;
use App\Entity\TasksUsers;
use App\Services\TasksService\Contracts\TasksServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TasksController extends AbstractController
{
    private NormalizerInterface $normalizer;
    private EntityManagerInterface $em;

    public function __construct(
        private readonly TasksServiceInterface $tasksService,
        private readonly ValidatorInterface $validator,
        ManagerRegistry $managerRegistry
    ) {
        $this->normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $this->em = $managerRegistry->getManager('default');
    }

    public function all(): JsonResponse
    {
        $tasks = $this->tasksService->getAll();

        $tasksUsers = $this->tasksService->getTaskUsersByTasks($tasks);
        $tasksUsersIndexed = [];
        foreach ($tasksUsers as $taskUser) {
            $taskId = $taskUser->getTaskId();
            if (!array_key_exists($taskId, $tasksUsersIndexed)) {
                $tasksUsersIndexed[$taskId] = [];
            }
            $tasksUsersIndexed[$taskId][] = $taskUser;
        }

        $normalized = [];
        foreach ($tasks as $task) {
            $userIds = array_map(static function (TasksUsers $tasksUser) {
                return $tasksUser->getUserId();
            }, $tasksUsersIndexed[$task->getId()] ?? []);

            $normalized[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'users' => $userIds,
            ];
        }

        return new JsonResponse($normalized);
    }

    public function byId(int $taskId): JsonResponse
    {
        $task = $this->tasksService->getById($taskId);

        if (is_null($task)) {
            return new JsonResponse(['message' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $userTasks = $this->tasksService->getTaskUsersByTasks([$task]);

        $userIds = array_map(static function (TasksUsers $tasksUser) {
            return $tasksUser->getUserId();
        }, $userTasks);

        $normalized = [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'users' => $userIds,
        ];

        return new JsonResponse($normalized);
    }

    public function create(Request $request): JsonResponse
    {
        $parameters = $request->toArray();

        $task = new Tasks();

        $task->setId($parameters['id'] ?? null);
        $task->setTitle($parameters['title'] ?? null);
        $task->setDescription($parameters['description'] ?? null);

        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $userIds = $parameters['users'] ?? [];
        if (empty($userIds) || !is_array($userIds)) {
            return new JsonResponse(['error' => 'bad request'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->beginTransaction();

        if (!$this->tasksService->create($task) || !$this->tasksService->updateTaskUsers($task, $userIds)) {
            $this->em->rollback();
            return new JsonResponse(['message' => 'Task not created'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->commit();

        return new JsonResponse([
            'message' => 'Task created'
        ]);
    }

    public function update(int $taskId, Request $request): JsonResponse
    {
        $parameters = $request->toArray();

        $task = $this->tasksService->getById($taskId);

        if (is_null($task)) {
            return new JsonResponse(['message' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        if (array_key_exists('id', $parameters)) {
            $task->setId($parameters['id']);
        }

        if (array_key_exists('title', $parameters)) {
            $task->setTitle($parameters['title']);
        }

        if (array_key_exists('description', $parameters)) {
            $task->setDescription($parameters['description']);
        }

        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->beginTransaction();

        if (!$this->tasksService->update($task)) {
            $this->em->rollback();

            return new JsonResponse(['message' => 'Task not updated'], Response::HTTP_BAD_REQUEST);
        }

        $userIds = $parameters['users'] ?? null;
        if (!empty($userIds) && is_array($userIds) && !$this->tasksService->updateTaskUsers($task, $userIds)) {
            $this->em->rollback();

            return new JsonResponse(['message' => 'Task not updated'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->commit();

        return new JsonResponse([
            'message' => 'Task updated'
        ]);
    }
}
