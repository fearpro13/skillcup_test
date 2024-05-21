<?php

namespace App\Services\TasksService;

use App\Entity\Tasks;
use App\Entity\TasksUsers;
use App\Repository\TasksRepository;
use App\Repository\TasksUsersRepository;
use App\Services\TasksService\Contracts\TasksServiceInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

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

    public function updateTaskUsers(Tasks $task, array $userIds): bool
    {
        $userIds = array_filter(array_unique(array_values($userIds), SORT_REGULAR));

        $values = [];
        foreach ($userIds as $userId) {
            if (!is_numeric($userId) || $userId <= 0) {
                return false;
            }

            $values[] = $task->getId();
            $values[] = $userId;
            $values[] = false;
        }

        $insertTemplate = implode(",", array_fill(0, count($userIds), "(?,?,?)"));

        $deleteValues = implode(",", $userIds);

        $this->em->beginTransaction();

        if (!$this->em->createNativeQuery(
                "insert into tasks values $insertTemplate",
                new ResultSetMapping()
            )->execute($values) ||
            !$this->em->createNativeQuery(
                'delete from tasks_users where task_id = ? and user_id not in (?)',
                new ResultSetMapping()
            )->execute([$task->getId(), $deleteValues])) {
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