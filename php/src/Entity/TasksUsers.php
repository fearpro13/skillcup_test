<?php

namespace App\Entity;

use App\Repository\TasksUsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TasksUsersRepository::class)]
class TasksUsers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $taskId = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $userId = null;

    #[ORM\Column(nullable: true)]
    private ?bool $completed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function setTaskId(?string $taskId): static
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
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
