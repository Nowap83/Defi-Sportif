<?php
namespace App\Entity;

use App\Repository\DefiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    #[Assert\NotNull(message: "La date du défi est obligatoire")]
    #[Assert\GreaterThan(
        value: "today",
        message: "La date du défi doit être dans le futur"
    )]
    private ?\DateTimeImmutable $dateDefi = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    #[Assert\NotBlank(message: "Le type de défi est obligatoire")]
    #[Assert\Choice(
        choices: ['course', 'randonnee', 'velo', 'natation', 'triathlon', 'autre'],
        message: "Le type de défi choisi n'est pas valide"
    )]
    private ?string $typeDefi = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    #[Assert\NotBlank(message: "La région est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "La région doit contenir au moins {{ limit }} caractères",
        maxMessage: "La région ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    #[Assert\NotBlank(message: "Le pays est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le pays doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le pays ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $pays = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    #[Assert\NotNull(message: "La distance est obligatoire")]
    #[Assert\Positive(message: "La distance doit être un nombre positif")]
    #[Assert\LessThan(
        value: 1000,
        message: "La distance ne peut pas dépasser 1000 km"
    )]
    private ?float $distance = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    #[Assert\NotNull(message: "Le nombre minimum de participants est obligatoire")]
    #[Assert\Positive(message: "Le nombre minimum de participants doit être positif")]
    #[Assert\LessThan(
        propertyPath: "maxParticipant",
        message: "Le nombre minimum de participants doit être inférieur au maximum"
    )]
    private ?int $minParticipant = null;

    #[ORM\Column]
    #[Groups(['defis_list'])]
    #[Assert\NotNull(message: "Le nombre maximum de participants est obligatoire")]
    #[Assert\Positive(message: "Le nombre maximum de participants doit être positif")]
    #[Assert\LessThanOrEqual(
        value: 1000,
        message: "Le nombre maximum de participants ne peut pas dépasser 1000"
    )]
    #[Assert\GreaterThan(
        propertyPath: "minParticipant",
        message: "Le nombre maximum de participants doit être supérieur au minimum"
    )]
    private ?int $maxParticipant = null;

    #[ORM\Column(length: 255)]
    #[Groups(['defis_list'])]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de l'image ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: '/\.(jpg|jpeg|png|gif|webp)$/i',
        message: "L'image doit avoir une extension valide (jpg, jpeg, png, gif, webp)"
    )]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'defisCrees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le créateur du défi est obligatoire")]
    private ?User $createur = null;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'defi', orphanRemoval: true)]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }
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

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setDefi($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getDefi() === $this) {
                $inscription->setDefi(null);
            }
        }

        return $this;
    }
}
