<?php

namespace App\Entity;

use App\Repository\TasksUsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TasksUsersRepository::class)]
#[ORM\Table(name: 'tasks_users')]
class TasksUsers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT, nullable: false,name: 'task_id')]
    private ?int $taskId = null;

    #[ORM\Column(type: Types::BIGINT, nullable: false, name: 'user_id')]
    private ?int $userId = null;

    #[ORM\Column(nullable: false)]
    private ?bool $completed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(?int $taskId): static
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(?bool $completed): static
    {
        $this->completed = $completed;

        return $this;
    }
}
