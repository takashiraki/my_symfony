<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TaskStatus;
use App\Repository\TasksRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TasksRepository::class)]
class Tasks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $due_date = null;

    #[ORM\Column(enumType: TaskStatus::class)]
    private ?TaskStatus $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDueDate(): ?DateTime
    {
        return $this->due_date;
    }

    public function setDueDate(?DateTime $due_date): static
    {
        $this->due_date = $due_date;

        return $this;
    }

    public function getStatus(): ?TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
