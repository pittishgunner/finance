<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use eduMedia\TagBundle\Entity\TaggableInterface;
use eduMedia\TagBundle\Entity\TaggableTrait;

#[ORM\Entity(repositoryClass: RecordRepository::class)]
#[ORM\Index(columns: ['date'], name: 'date_idx')]
#[ORM\Index(columns: ['debit'], name: 'debit_idx')]
#[ORM\Index(columns: ['credit'], name: 'credit_idx')]
#[ORM\Index(columns: ['balance'], name: 'balance_idx')]
#[ORM\Index(columns: ['hash'], name: 'hash_idx')]
#[ORM\Index(columns: ['notified_at'], name: 'notified_idx')]
#[ORM\Index(columns: ['created_at'], name: 'created_idx')]
#[ORM\Index(columns: ['reconciled'], name: 'reconciled_idx')]
class Record implements TaggableInterface
{
    use TaggableTrait;

    public function __construct()
    {
        $this->setCreatedAt(new DateTimeImmutable());
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\Column(type: 'datetime_microseconds')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'date')]
    private DateTime $date;

    #[ORM\Column]
    private ?float $debit = null;

    #[ORM\Column]
    private ?float $credit = null;

    #[ORM\Column]
    private ?float $balance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $hash = null;

    #[ORM\Column(type: 'datetime_microseconds', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'records')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'records')]
    private ?SubCategory $subCategory = null;

    #[ORM\Column(type: 'datetime_microseconds', nullable: true)]
    private ?DateTimeImmutable $notifiedAt = null;

    #[ORM\Column]
    private bool $reconciled = false;

    #[ORM\Column]
    private bool $ignored = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDebit(): ?float
    {
        return $this->debit;
    }

    public function setDebit(float $debit): static
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(float $credit): static
    {
        $this->credit = $credit;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

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

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

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

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getNotifiedAt(): ?DateTimeImmutable
    {
        return $this->notifiedAt;
    }

    public function setNotifiedAt(?DateTimeImmutable $notifiedAt): static
    {
        $this->notifiedAt = $notifiedAt;

        return $this;
    }

    public function isReconciled(): bool
    {
        return $this->reconciled;
    }

    public function setReconciled(bool $reconciled): static
    {
        $this->reconciled = $reconciled;

        return $this;
    }

    public function isIgnored(): bool
    {
        return $this->ignored;
    }

    public function setIgnored(bool $ignored): static
    {
        $this->ignored = $ignored;

        return $this;
    }
}
