<?php

namespace App\Entity;

use App\Repository\DefiRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;




#[ORM\Entity(repositoryClass: DefiRepository::class)]
class Defi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['defis_list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    private ?\DateTimeImmutable $dateDefi = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    private ?string $typeDefi = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    private ?string $pays = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    private ?float $distance = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    private ?int $minParticipant = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    private ?int $maxParticipant = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'defisCCrees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateDefi(): ?\DateTimeImmutable
    {
        return $this->dateDefi;
    }

    public function setDateDefi(\DateTimeImmutable $dateDefi): static
    {
        $this->dateDefi = $dateDefi;

        return $this;
    }

    public function getTypeDefi(): ?string
    {
        return $this->typeDefi;
    }

    public function setTypeDefi(string $typeDefi): static
    {
        $this->typeDefi = $typeDefi;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): static
    {
        $this->distance = $distance;

        return $this;
    }

    public function getMinParticipant(): ?int
    {
        return $this->minParticipant;
    }

    public function setMinParticipant(int $minParticipant): static
    {
        $this->minParticipant = $minParticipant;

        return $this;
    }

    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
    }

    public function setMaxParticipant(int $maxParticipant): static
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreateur(): ?User
    {
        return $this->createur;
    }

    public function setCreateur(?User $createur): static
    {
        $this->createur = $createur;

        return $this;
    }
}
