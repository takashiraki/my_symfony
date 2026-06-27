<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\MoneyDiartType;
use App\Repository\MoneyDiaryRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoneyDiaryRepository::class)]
class MoneyDiary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTime $date = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\ManyToOne(inversedBy: 'moneyDiaries')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'moneyDiaries')]
    private ?PaymentSource $paymentSource = null;

    #[ORM\ManyToOne(inversedBy: 'moneyDiaries')]
    private ?User $user = null;

    #[ORM\Column(enumType: MoneyDiartType::class)]
    private ?MoneyDiartType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPaymentSource(): ?PaymentSource
    {
        return $this->paymentSource;
    }

    public function setPaymentSource(?PaymentSource $paymentSource): static
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): ?MoneyDiartType
    {
        return $this->type;
    }

    public function setType(MoneyDiartType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
