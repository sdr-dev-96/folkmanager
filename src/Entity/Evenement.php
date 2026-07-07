<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

enum StatutEvenement: string
{
    case BROUILLON = 'brouillon';
    case CONFIRME = 'confirme';
    case ANNULE = 'annule';
}

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    private Association $association;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $date;

    /** 1 = lundi ... 7 = dimanche, dérivé de la date mais stocké pour faciliter les requêtes */
    #[ORM\Column]
    private int $jourSemaine;

    #[ORM\Column(length: 100)]
    private string $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $detail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $affichePath = null;

    #[ORM\Column(length: 20, enumType: StatutEvenement::class)]
    private StatutEvenement $statut = StatutEvenement::BROUILLON;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Presence::class, orphanRemoval: true)]
    private Collection $presences;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: DanseEvenement::class, orphanRemoval: true)]
    private Collection $programme;

    public function __construct()
    {
        $this->presences = new ArrayCollection();
        $this->programme = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssociation(): Association
    {
        return $this->association;
    }

    public function setAssociation(Association $association): static
    {
        $this->association = $association;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        $this->jourSemaine = (int) $date->format('N');
        return $this;
    }

    public function getJourSemaine(): int
    {
        return $this->jourSemaine;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): static
    {
        $this->detail = $detail;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getAffichePath(): ?string
    {
        return $this->affichePath;
    }

    public function setAffichePath(?string $affichePath): static
    {
        $this->affichePath = $affichePath;
        return $this;
    }

    public function getStatut(): StatutEvenement
    {
        return $this->statut;
    }

    public function setStatut(StatutEvenement $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    /** @return Collection<int, Presence> */
    public function getPresences(): Collection
    {
        return $this->presences;
    }

    /** @return Collection<int, DanseEvenement> */
    public function getProgramme(): Collection
    {
        return $this->programme;
    }
}
