<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\Country]
    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\Column]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updated_at = null;

    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: Account::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Account $account = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    /**
     * @var Collection<int, PaymentSource>
     */
    #[ORM\OneToMany(targetEntity: PaymentSource::class, mappedBy: 'user')]
    private Collection $paymentSources;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'user')]
    private Collection $categories;

    /**
     * @var Collection<int, MoneyDiary>
     */
    #[ORM\OneToMany(targetEntity: MoneyDiary::class, mappedBy: 'user')]
    private Collection $moneyDiaries;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
        $this->tasks = new ArrayCollection();
        $this->paymentSources = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->moneyDiaries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (! $this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Collection<int, PaymentSource>
     */
    public function getPaymentSources(): Collection
    {
        return $this->paymentSources;
    }

    public function addPaymentSource(PaymentSource $paymentSource): static
    {
        if (!$this->paymentSources->contains($paymentSource)) {
            $this->paymentSources->add($paymentSource);
            $paymentSource->setUser($this);
        }

        return $this;
    }

    public function removePaymentSource(PaymentSource $paymentSource): static
    {
        if ($this->paymentSources->removeElement($paymentSource)) {
            // set the owning side to null (unless already changed)
            if ($paymentSource->getUser() === $this) {
                $paymentSource->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getUser() === $this) {
                $category->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MoneyDiary>
     */
    public function getMoneyDiaries(): Collection
    {
        return $this->moneyDiaries;
    }

    public function addMoneyDiary(MoneyDiary $moneyDiary): static
    {
        if (!$this->moneyDiaries->contains($moneyDiary)) {
            $this->moneyDiaries->add($moneyDiary);
            $moneyDiary->setUser($this);
        }

        return $this;
    }

    public function removeMoneyDiary(MoneyDiary $moneyDiary): static
    {
        if ($this->moneyDiaries->removeElement($moneyDiary)) {
            // set the owning side to null (unless already changed)
            if ($moneyDiary->getUser() === $this) {
                $moneyDiary->setUser(null);
            }
        }

        return $this;
    }
}
