<?php

namespace App\Entity;

use App\Repository\CategoryRuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRuleRepository::class)]
class CategoryRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $matches = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $debit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $credit = null;

    #[ORM\ManyToOne]
    private ?Account $account = null;

    #[ORM\ManyToOne(inversedBy: 'categoryRules')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'categoryRules')]
    private ?SubCategory $subCategory = null;

    #[ORM\Column]
    private ?bool $enabled = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private int $position = 999999;

    #[ORM\Column]
    private bool $stop = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatches(): ?array
    {
        return $this->matches;
    }

    public function setMatches(?array $matches): static
    {
        $this->matches = $matches;

        return $this;
    }

    public function getDebit(): ?string
    {
        return $this->debit;
    }

    public function setDebit(?string $debit): static
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }

    public function setCredit(?string $credit): static
    {
        $this->credit = $credit;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): static
    {
        $this->account = $account;

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

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): static
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isStop(): bool
    {
        return $this->stop;
    }

    public function setStop(bool $stop): static
    {
        $this->stop = $stop;

        return $this;
    }
}
