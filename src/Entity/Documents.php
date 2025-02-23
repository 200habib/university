<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\File;
use App\Repository\DocumentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: DocumentsRepository::class)]
class Documents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)] // Augmente la longueur à 255 caractères
    private ?string $file_name = null;

    #[Vich\UploadableField(mapping: 'documents', fileNameProperty: 'file_name')]
    private ?File $file_path = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $download_date = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $selectedUser = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $coach = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'documents')]
    private Collection $steps;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(?string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getFilePath(): ?File
    {
        return $this->file_path;
    }

    public function setFilePath(?File $file_path = null): static
    {
        $this->file_path = $file_path;

        // Met à jour la date pour déclencher l'upload si un fichier est attribué
        if ($file_path) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getDownloadDate(): ?\DateTimeInterface
    {
        return $this->download_date;
    }

    public function setDownloadDate(?\DateTimeInterface $download_date): static
    {
        $this->download_date = $download_date;

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

    public function getSelectedUser(): ?User
    {
        return $this->selectedUser;
    }

    public function setSelectedUser(?User $selectedUser): static
    {
        $this->selectedUser = $selectedUser;

        return $this;
    }

    public function getCoach(): ?User
    {
        return $this->coach;
    }

    public function setCoach(?User $coach): static
    {
        $this->coach = $coach;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): static
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setDocuments($this);
        }

        return $this;
    }

    public function removeStep(Step $step): static
    {
        if ($this->steps->removeElement($step)) {
            if ($step->getDocuments() === $this) {
                $step->setDocuments(null);
            }
        }

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->user ? $this->user->getEmail() : null;
    }
}
