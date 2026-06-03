<?php

namespace App\Entity;

use App\Repository\SerieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SerieRepository::class)]
class Serie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'series')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExerciceSeance $exerciceSeance = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numSerie = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $nbRepsRealisees = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $poidsKg = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $dureeSec = null;

    #[ORM\Column]
    private ?bool $estPr = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExerciceSeance(): ?ExerciceSeance
    {
        return $this->exerciceSeance;
    }

    public function setExerciceSeance(?ExerciceSeance $exerciceSeance): static
    {
        $this->exerciceSeance = $exerciceSeance;

        return $this;
    }

    public function getNumSerie(): ?int
    {
        return $this->numSerie;
    }

    public function setNumSerie(int $numSerie): static
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    public function getNbRepsRealisees(): ?int
    {
        return $this->nbRepsRealisees;
    }

    public function setNbRepsRealisees(int $nbRepsRealisees): static
    {
        $this->nbRepsRealisees = $nbRepsRealisees;

        return $this;
    }

    public function getPoidsKg(): ?string
    {
        return $this->poidsKg;
    }

    public function setPoidsKg(string $poidsKg): static
    {
        $this->poidsKg = $poidsKg;

        return $this;
    }

    public function getDureeSec(): ?int
    {
        return $this->dureeSec;
    }

    public function setDureeSec(?int $dureeSec): static
    {
        $this->dureeSec = $dureeSec;

        return $this;
    }

    public function isEstPr(): ?bool
    {
        return $this->estPr;
    }

    public function setEstPr(bool $estPr): static
    {
        $this->estPr = $estPr;

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
    public function calculer1RM(): float
    {
        if ($this->nbRepsRealisees === 1) return (float) $this->poidsKg;
        return round((float) $this->poidsKg * (1 + $this->nbRepsRealisees / 30), 1);
    }

}
