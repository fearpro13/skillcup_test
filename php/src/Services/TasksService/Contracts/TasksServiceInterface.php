<?php

namespace App\Services\TasksService\Contracts;

use App\Entity\Tasks;
use App\Entity\TasksUsers;

interface TasksServiceInterface
{
    /**
     * @return Tasks[]
     */
    public function getAll():array;

    /**
     * @param int $id
     * @return Tasks|null
     */
    public function getById(int $id):?Tasks;

    /**
     * @param Tasks $task
     * @return bool
     */
    public function create(Tasks $task):bool;

    /**
     * @param Tasks $task
     * @return bool
     */
    public function update(Tasks $task):bool;

    /////////////////////////////////////////////

    /**
     * @param Tasks[] $tasks
     * @return TasksUsers[]
     */
    public function getTaskUsersByTasks(array $tasks):array;
    public function updateTaskUsers(Tasks $task,array $userIds):bool;

    /////////////////////////////////////////////

    public function getUserTask(Tasks $task,int $userId):?TasksUsers;

    public function completeTask(TasksUsers $tasksUsers):bool;
}