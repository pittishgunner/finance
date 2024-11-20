<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, SubCategory>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: SubCategory::class, orphanRemoval: true)]
    private Collection $subCategories;

    /**
     * @var Collection<int, Record>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Record::class)]
    private Collection $records;

    /**
     * @var Collection<int, CategoryRule>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: CategoryRule::class)]
    private Collection $categoryRules;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
        $this->records = new ArrayCollection();
        $this->categoryRules = new ArrayCollection();
    }

    // Used by EntityFilter
    public function __toString(): string
    {
        return $this->getName();
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

    /**
     * @return Collection<int, SubCategory>
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): static
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories->add($subCategory);
            $subCategory->setCategory($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): static
    {
        if ($this->subCategories->removeElement($subCategory)) {
            // set the owning side to null (unless already changed)
            if ($subCategory->getCategory() === $this) {
                $subCategory->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Record>
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(Record $record): static
    {
        if (!$this->records->contains($record)) {
            $this->records->add($record);
            $record->setCategory($this);
        }

        return $this;
    }

    public function removeRecord(Record $record): static
    {
        if ($this->records->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getCategory() === $this) {
                $record->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryRule>
     */
    public function getCategoryRules(): Collection
    {
        return $this->categoryRules;
    }

    public function addCategoryRule(CategoryRule $categoryRule): static
    {
        if (!$this->categoryRules->contains($categoryRule)) {
            $this->categoryRules->add($categoryRule);
            $categoryRule->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryRule(CategoryRule $categoryRule): static
    {
        if ($this->categoryRules->removeElement($categoryRule)) {
            // set the owning side to null (unless already changed)
            if ($categoryRule->getCategory() === $this) {
                $categoryRule->setCategory(null);
            }
        }

        return $this;
    }
}
