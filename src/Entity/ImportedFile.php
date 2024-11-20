<?php

namespace App\Entity;

use App\Repository\ImportedFileRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportedFileRepository::class)]
class ImportedFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $folder = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column]
    private DateTimeImmutable $importedAt;

    #[ORM\Column]
    private ?bool $forceReImport = null;

    #[ORM\Column]
    private DateTimeImmutable $fileCreatedAt;

    #[ORM\Column(nullable: true)]
    private ?int $parsedRecords = null;

    #[ORM\ManyToOne(inversedBy: 'importedFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getImportedAt(): DateTimeImmutable
    {
        return $this->importedAt;
    }

    public function setImportedAt(DateTimeImmutable $importedAt): static
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    public function isForceReImport(): ?bool
    {
        return $this->forceReImport;
    }

    public function setForceReImport(bool $forceReImport): static
    {
        $this->forceReImport = $forceReImport;

        return $this;
    }

    public function getFileCreatedAt(): DateTimeImmutable
    {
        return $this->fileCreatedAt;
    }

    public function setFileCreatedAt(DateTimeImmutable $fileCreatedAt): static
    {
        $this->fileCreatedAt = $fileCreatedAt;

        return $this;
    }

    public function getParsedRecords(): ?int
    {
        return $this->parsedRecords;
    }

    public function setParsedRecords(?int $parsedRecords): static
    {
        $this->parsedRecords = $parsedRecords;

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
}
