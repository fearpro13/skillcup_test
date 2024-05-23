<?php

namespace App\Repository;

use App\Entity\Tasks;
use App\Entity\TasksUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TasksUsers>
 */
class TasksUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TasksUsers::class);
    }

    /**
     * @param Tasks $task
     * @return TasksUsers[]
     */
    public function getTaskUsersByTask(Tasks $task):array
    {
        return $this->findOneBy(
            [
                'taskId' => $task->getId(),
            ]
        );
    }

    /**
     * @param array $tasks
     * @return array
     */
    public function getTaskUsersByTasks(array $tasks):array
    {
        return $this->findBy(
            [
                'taskId' => $tasks
            ]
        );
    }
}
