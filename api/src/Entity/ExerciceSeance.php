<?php

namespace App\Entity;

use App\Repository\ExerciceSeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciceSeanceRepository::class)]
class ExerciceSeance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'exerciceSeances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seance $seance = null;

    #[ORM\ManyToOne(inversedBy: 'exerciceSeances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Exercice $exercice = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordre = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $nbRepsCible = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $poidsCibleKg = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tempsReposSec = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    /**
     * @var Collection<int, Serie>
     */
    #[ORM\OneToMany(targetEntity: Serie::class, mappedBy: 'exerciceSeance', orphanRemoval: true)]
    private Collection $series;

    public function __construct()
    {
        $this->series = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): static
    {
        $this->seance = $seance;

        return $this;
    }

    public function getExercice(): ?Exercice
    {
        return $this->exercice;
    }

    public function setExercice(?Exercice $exercice): static
    {
        $this->exercice = $exercice;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getNbRepsCible(): ?int
    {
        return $this->nbRepsCible;
    }

    public function setNbRepsCible(int $nbRepsCible): static
    {
        $this->nbRepsCible = $nbRepsCible;

        return $this;
    }

    public function getPoidsCibleKg(): ?string
    {
        return $this->poidsCibleKg;
    }

    public function setPoidsCibleKg(?string $poidsCibleKg): static
    {
        $this->poidsCibleKg = $poidsCibleKg;

        return $this;
    }

    public function getTempsReposSec(): ?int
    {
        return $this->tempsReposSec;
    }

    public function setTempsReposSec(int $tempsReposSec): static
    {
        $this->tempsReposSec = $tempsReposSec;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return Collection<int, Serie>
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function addSeries(Serie $series): static
    {
        if (!$this->series->contains($series)) {
            $this->series->add($series);
            $series->setExerciceSeance($this);
        }

        return $this;
    }

    public function removeSeries(Serie $series): static
    {
        if ($this->series->removeElement($series)) {
            // set the owning side to null (unless already changed)
            if ($series->getExerciceSeance() === $this) {
                $series->setExerciceSeance(null);
            }
        }

        return $this;
    }
}
