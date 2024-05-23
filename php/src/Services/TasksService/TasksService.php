<?php

namespace App\Services\TasksService;

use App\Entity\Tasks;
use App\Entity\TasksUsers;
use App\Repository\TasksRepository;
use App\Repository\TasksUsersRepository;
use App\Services\TasksService\Contracts\TasksServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

readonly class TasksService implements TasksServiceInterface
{
    private EntityManagerInterface $em;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private TasksRepository $tasksRepository,
        private TasksUsersRepository $tasksUsersRepository
    ) {
        $this->em = $managerRegistry->getManager('default');
    }

    public function getAll(): array
    {
        return $this->tasksRepository->findAll();
    }

    public function getById(int $id): ?Tasks
    {
        return $this->tasksRepository->find($id);
    }

    public function create(Tasks $task): true
    {
        $this->em->persist($task);

        $this->em->flush();

        return true;
    }

    public function update(Tasks $task): true
    {
        $this->em->flush();

        return true;
    }

    public function getTaskUsersByTasks(array $tasks): array
    {
        return $this
            ->em
            ->getRepository(TasksUsers::class)
            ->getTaskUsersByTasks($tasks);
    }

    public function updateTaskUsers(Tasks $task, array $userIds): bool
    {
        $userIds = array_values(array_filter(array_unique(array_values($userIds), SORT_REGULAR)));

        $values = [];
        foreach ($userIds as $userId) {
            if (!is_numeric($userId) || $userId <= 0) {
                return false;
            }

            $values[] = $task->getId();
            $values[] = (int)$userId;
            $values[] = false;
        }

        $insertTemplate = implode(",", array_fill(0, count($userIds), '(?, ?, ?)'));

        $this->em->beginTransaction();

        $insertQuery = $this->em->createNativeQuery(
            "insert into tasks_users (task_id, user_id, completed) values $insertTemplate",
            new ResultSetMapping()
        );
        $insertQuery->setParameters($values);

        $deleteTemplate = sprintf("(%s)", implode(",", array_fill(0, count($userIds), '?')));
        $deleteQuery = $this->em->createNativeQuery(
            "delete from tasks_users where task_id = ? and user_id NOT IN $deleteTemplate",
            new ResultSetMapping()
        );
        $deleteQuery->setParameters([$task->getId(), ...$userIds]);

        try {
            $insertQuery->execute();

            $deleteQuery->execute();
        } catch (Throwable $exception) {
            error_log($exception);
            $this->em->rollback();

            return false;
        }

        $this->em->commit();

        return true;
    }

    public function getUserTask(Tasks $task, int $userId): ?TasksUsers
    {
        return $this->tasksUsersRepository->findOneBy([
            'taskId' => $task->getId(),
            'userId' => $userId
        ]);
    }

    public function completeTask(TasksUsers $tasksUsers): bool
    {
        if ($tasksUsers->isCompleted()) {
            return false;
        }

        $tasksUsers->setCompleted(true);

        $this->em->flush();

        return true;
    }
}