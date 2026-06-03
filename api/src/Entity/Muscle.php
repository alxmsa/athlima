<?php

namespace App\Entity;

use App\Repository\MuscleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MuscleRepository::class)]
class Muscle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $nom = null;

    #[ORM\Column(length: 80)]
    private ?string $groupeMusculaire = null;

    #[ORM\Column]
    private ?bool $estPrincipal = null;

    #[ORM\ManyToMany(targetEntity: Exercice::class, mappedBy: 'muscles')]
    private Collection $exercices;

    public function __construct()
    {
        $this->exercices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGroupeMusculaire(): ?string
    {
        return $this->groupeMusculaire;
    }

    public function setGroupeMusculaire(string $groupeMusculaire): static
    {
        $this->groupeMusculaire = $groupeMusculaire;
        return $this;
    }

    public function isEstPrincipal(): ?bool
    {
        return $this->estPrincipal;
    }

    public function setEstPrincipal(bool $estPrincipal): static
    {
        $this->estPrincipal = $estPrincipal;
        return $this;
    }

    public function getExercices(): Collection
    {
        return $this->exercices;
    }
}