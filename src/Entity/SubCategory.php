<?php

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubCategoryRepository::class)]
class SubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'subCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, Record>
     */
    #[ORM\OneToMany(mappedBy: 'subCategory', targetEntity: Record::class)]
    private Collection $records;

    /**
     * @var Collection<int, CategoryRule>
     */
    #[ORM\OneToMany(mappedBy: 'subCategory', targetEntity: CategoryRule::class)]
    private Collection $categoryRules;

    public function __construct()
    {
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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
            $record->setSubCategory($this);
        }

        return $this;
    }

    public function removeRecord(Record $record): static
    {
        if ($this->records->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getSubCategory() === $this) {
                $record->setSubCategory(null);
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
            $categoryRule->setSubCategory($this);
        }

        return $this;
    }

    public function removeCategoryRule(CategoryRule $categoryRule): static
    {
        if ($this->categoryRules->removeElement($categoryRule)) {
            // set the owning side to null (unless already changed)
            if ($categoryRule->getSubCategory() === $this) {
                $categoryRule->setSubCategory(null);
            }
        }

        return $this;
    }
}
