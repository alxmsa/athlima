<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(type: 'string', length: 80)]
    #[Assert\NotBlank]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 80)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $poidsKg = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 1, nullable: true)]
    private ?string $tailleCm = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $niveau = 'debutant';

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Seance::class, cascade: ['persist', 'remove'])]
    private Collection $seances;

    #[ORM\OneToOne(mappedBy: 'utilisateur', targetEntity: Objectif::class, cascade: ['persist', 'remove'])]
    private ?Objectif $objectif = null;

    public function __construct()
    {
        $this->seances   = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    // ── UserInterface ──────────────────────────────────────
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles   = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function eraseCredentials(): void {}

    // ── Getters / Setters ──────────────────────────────────
    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface { return $this->dateNaissance; }
    public function setDateNaissance(?\DateTimeInterface $d): static
    {
        $this->dateNaissance = $d;
        return $this;
    }

    public function getPoidsKg(): ?float { return $this->poidsKg; }
    public function setPoidsKg(?float $p): static
    {
        $this->poidsKg = $p;
        return $this;
    }

    public function getTailleCm(): ?float { return $this->tailleCm; }
    public function setTailleCm(?float $t): static
    {
        $this->tailleCm = $t;
        return $this;
    }

    public function getNiveau(): string { return $this->niveau; }
    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function getSeances(): Collection { return $this->seances; }

    public function getObjectif(): ?Objectif { return $this->objectif; }
    public function setObjectif(?Objectif $o): static
    {
        $this->objectif = $o;
        return $this;
    }
}