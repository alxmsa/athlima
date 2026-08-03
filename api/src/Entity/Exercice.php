<?php

namespace App\Entity;

use App\Repository\ExerciceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciceRepository::class)]
class Exercice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exercices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $typeEffort = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $equipement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column]
    private ?bool $estPublic = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    /**
     * @var Collection<int, ExerciceSeance>
     */
    #[ORM\OneToMany(targetEntity: ExerciceSeance::class, mappedBy: 'exercice')]
    private Collection $exerciceSeances;

    #[ORM\ManyToMany(targetEntity: Muscle::class, inversedBy: 'exercices')]
    #[ORM\JoinTable(name: 'exercice_muscle')]
    private Collection $muscles;

    #[ORM\ManyToOne]
    private ?Utilisateur $createdBy = null;

    public function __construct()
    {
            $this->exerciceSeances = new ArrayCollection();
            $this->muscles         = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getTypeEffort(): ?string
    {
        return $this->typeEffort;
    }

    public function setTypeEffort(string $typeEffort): static
    {
        $this->typeEffort = $typeEffort;

        return $this;
    }

    public function getEquipement(): ?string
    {
        return $this->equipement;
    }

    public function setEquipement(?string $equipement): static
    {
        $this->equipement = $equipement;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function isEstPublic(): ?bool
    {
        return $this->estPublic;
    }

    public function setEstPublic(bool $estPublic): static
    {
        $this->estPublic = $estPublic;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, ExerciceSeance>
     */
    public function getExerciceSeances(): Collection
    {
        return $this->exerciceSeances;
    }

    public function addExerciceSeance(ExerciceSeance $exerciceSeance): static
    {
        if (!$this->exerciceSeances->contains($exerciceSeance)) {
            $this->exerciceSeances->add($exerciceSeance);
            $exerciceSeance->setExercice($this);
        }

        return $this;
    }

    public function removeExerciceSeance(ExerciceSeance $exerciceSeance): static
    {
        if ($this->exerciceSeances->removeElement($exerciceSeance)) {
            // set the owning side to null (unless already changed)
            if ($exerciceSeance->getExercice() === $this) {
                $exerciceSeance->setExercice(null);
            }
        }

        return $this;
    }

    public function getMuscles(): Collection
    {
        return $this->muscles;
    }

    public function addMuscle(Muscle $muscle): static
    {
        if (!$this->muscles->contains($muscle)) {
            $this->muscles->add($muscle);
        }
        return $this;
    }

    public function removeMuscle(Muscle $muscle): static
    {
        $this->muscles->removeElement($muscle);
        return $this;
    }

    public function getCreatedBy(): ?Utilisateur
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Utilisateur $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
    
}
