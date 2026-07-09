<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CategoryType;
use App\Repository\CategoryRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, MoneyDiary>
     */
    #[ORM\OneToMany(targetEntity: MoneyDiary::class, mappedBy: 'category')]
    private Collection $moneyDiaries;

    #[ORM\Column(enumType: CategoryType::class)]
    private ?CategoryType $type = null;

    #[ORM\Column]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->moneyDiaries = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
        if (! $this->moneyDiaries->contains($moneyDiary)) {
            $this->moneyDiaries->add($moneyDiary);
            $moneyDiary->setCategory($this);
        }

        return $this;
    }

    public function removeMoneyDiary(MoneyDiary $moneyDiary): static
    {
        if ($this->moneyDiaries->removeElement($moneyDiary)) {
            // set the owning side to null (unless already changed)
            if ($moneyDiary->getCategory() === $this) {
                $moneyDiary->setCategory(null);
            }
        }

        return $this;
    }

    public function getType(): ?CategoryType
    {
        return $this->type;
    }

    public function setType(CategoryType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
