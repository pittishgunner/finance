<?php

namespace App\Entity;

use App\Repository\MissingRecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MissingRecordRepository::class)]
class MissingRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_microseconds', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $parsedRecord = null;

    /**
     * @var Collection<int, Record>
     */
    #[ORM\ManyToMany(targetEntity: Record::class)]
    private Collection $matchedRecords;

    #[ORM\Column(type: 'datetime_microseconds', nullable: true)]
    private $updatedAt = null;

    #[ORM\Column]
    private ?bool $solved = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hash = null;

    public function __construct()
    {
        $this->matchedRecords = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getParsedRecord(): ?string
    {
        return $this->parsedRecord;
    }

    public function setParsedRecord(string $parsedRecord): static
    {
        $this->parsedRecord = $parsedRecord;

        return $this;
    }

    /**
     * @return Collection<int, Record>
     */
    public function getMatchedRecords(): Collection
    {
        return $this->matchedRecords;
    }

    public function addMatchedRecord(Record $matchedRecord): static
    {
        if (!$this->matchedRecords->contains($matchedRecord)) {
            $this->matchedRecords->add($matchedRecord);
        }

        return $this;
    }

    public function removeMatchedRecord(Record $matchedRecord): static
    {
        $this->matchedRecords->removeElement($matchedRecord);

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isSolved(): ?bool
    {
        return $this->solved;
    }

    public function setSolved(bool $solved): static
    {
        $this->solved = $solved;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }
}
