<?php

namespace App\Entity;

use App\Repository\MembreGroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Table pivot Membre <-> GroupeType, avec les attributs spécifiques
 * activés selon le ComportementGroupe du GroupeType concerné :
 *  - DUO           -> genre, partenaire, estEnfant
 *  - CHOEUR_LEAD   -> estLead
 *  - INSTRUMENT    -> collection d'InstrumentJoue
 */
#[ORM\Entity(repositoryClass: MembreGroupeRepository::class)]
#[ORM\Table(name: 'membre_groupe')]
#[ORM\UniqueConstraint(name: 'uniq_membre_groupe', columns: ['membre_id', 'groupe_type_id'])]
class MembreGroupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'membreGroupes')]
    #[ORM\JoinColumn(nullable: false)]
    private Membre $membre;

    #[ORM\ManyToOne(inversedBy: 'membreGroupes')]
    #[ORM\JoinColumn(nullable: false)]
    private GroupeType $groupeType;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $genre = null; // 'homme' / 'femme' — utilisé si comportement = DUO

    #[ORM\ManyToOne(targetEntity: MembreGroupe::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?MembreGroupe $partenaire = null; // auto-référence, utilisé si comportement = DUO

    #[ORM\Column(nullable: true)]
    private ?bool $estEnfant = false;

    #[ORM\Column(nullable: true)]
    private ?bool $estLead = false; // utilisé si comportement = CHOEUR_LEAD

    #[ORM\OneToMany(mappedBy: 'membreGroupe', targetEntity: InstrumentJoue::class, orphanRemoval: true)]
    private Collection $instruments; // utilisé si comportement = INSTRUMENT

    public function __construct()
    {
        $this->instruments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMembre(): Membre
    {
        return $this->membre;
    }

    public function setMembre(Membre $membre): static
    {
        $this->membre = $membre;
        return $this;
    }

    public function getGroupeType(): GroupeType
    {
        return $this->groupeType;
    }

    public function setGroupeType(GroupeType $groupeType): static
    {
        $this->groupeType = $groupeType;
        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): static
    {
        $this->genre = $genre;
        return $this;
    }

    public function getPartenaire(): ?MembreGroupe
    {
        return $this->partenaire;
    }

    public function setPartenaire(?MembreGroupe $partenaire): static
    {
        $this->partenaire = $partenaire;
        return $this;
    }

    public function isEstEnfant(): ?bool
    {
        return $this->estEnfant;
    }

    public function setEstEnfant(?bool $estEnfant): static
    {
        $this->estEnfant = $estEnfant;
        return $this;
    }

    public function isEstLead(): ?bool
    {
        return $this->estLead;
    }

    public function setEstLead(?bool $estLead): static
    {
        $this->estLead = $estLead;
        return $this;
    }

    /** @return Collection<int, InstrumentJoue> */
    public function getInstruments(): Collection
    {
        return $this->instruments;
    }
}
