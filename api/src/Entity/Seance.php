<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_TERMINEE = 'terminee';
    const STATUT_ANNULEE  = 'annulee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $dureeMin = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    /**
     * @var Collection<int, ExerciceSeance>
     */
    #[ORM\OneToMany(targetEntity: ExerciceSeance::class, mappedBy: 'seance', orphanRemoval: true)]
    private Collection $exerciceSeances;

    public function __construct()
    {
        $this->exerciceSeances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

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

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getDureeMin(): ?int
    {
        return $this->dureeMin;
    }

    public function setDureeMin(?int $dureeMin): static
    {
        $this->dureeMin = $dureeMin;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

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
            $exerciceSeance->setSeance($this);
        }

        return $this;
    }

    public function removeExerciceSeance(ExerciceSeance $exerciceSeance): static
    {
        if ($this->exerciceSeances->removeElement($exerciceSeance)) {
            // set the owning side to null (unless already changed)
            if ($exerciceSeance->getSeance() === $this) {
                $exerciceSeance->setSeance(null);
            }
        }

        return $this;
    }
    public function terminer(): static
    {
        $this->dateFin = new \DateTime();
        $this->statut  = self::STATUT_TERMINEE;
        if ($this->dateFin && $this->dateDebut) {
            $diff = $this->dateDebut->diff($this->dateFin);
            $this->dureeMin = ($diff->h * 60) + $diff->i;
        }
        return $this;
    }
}
