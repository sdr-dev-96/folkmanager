<?php

namespace App\Entity;

use App\Repository\AssociationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssociationRepository::class)]
#[ORM\Table(name: 'association')]
class Association
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(length: 100, unique: true)]
    private string $slug;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $couleurPrincipale = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $couleurAccent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $villeCreation = null;

    #[ORM\Column(nullable: true)]
    private ?int $anneeCreation = null;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: Membre::class, orphanRemoval: true)]
    private Collection $membres;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: GroupeType::class, orphanRemoval: true)]
    private Collection $groupeTypes;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: Evenement::class, orphanRemoval: true)]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: Danse::class, orphanRemoval: true)]
    private Collection $danses;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: PlanningConfig::class, orphanRemoval: true)]
    private Collection $planningConfigs;

    #[ORM\OneToMany(mappedBy: 'association', targetEntity: ReseauSocialCompte::class, orphanRemoval: true)]
    private Collection $reseauxSociaux;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->groupeTypes = new ArrayCollection();
        $this->evenements = new ArrayCollection();
        $this->danses = new ArrayCollection();
        $this->planningConfigs = new ArrayCollection();
        $this->reseauxSociaux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCouleurPrincipale(): ?string
    {
        return $this->couleurPrincipale;
    }

    public function setCouleurPrincipale(?string $couleurPrincipale): static
    {
        $this->couleurPrincipale = $couleurPrincipale;
        return $this;
    }

    public function getCouleurAccent(): ?string
    {
        return $this->couleurAccent;
    }

    public function setCouleurAccent(?string $couleurAccent): static
    {
        $this->couleurAccent = $couleurAccent;
        return $this;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(?string $logoPath): static
    {
        $this->logoPath = $logoPath;
        return $this;
    }

    public function getVilleCreation(): ?string
    {
        return $this->villeCreation;
    }

    public function setVilleCreation(?string $villeCreation): static
    {
        $this->villeCreation = $villeCreation;
        return $this;
    }

    public function getAnneeCreation(): ?int
    {
        return $this->anneeCreation;
    }

    public function setAnneeCreation(?int $anneeCreation): static
    {
        $this->anneeCreation = $anneeCreation;
        return $this;
    }

    /** @return Collection<int, Membre> */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    /** @return Collection<int, GroupeType> */
    public function getGroupeTypes(): Collection
    {
        return $this->groupeTypes;
    }

    /** @return Collection<int, Evenement> */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    /** @return Collection<int, Danse> */
    public function getDanses(): Collection
    {
        return $this->danses;
    }

    /** @return Collection<int, PlanningConfig> */
    public function getPlanningConfigs(): Collection
    {
        return $this->planningConfigs;
    }

    /** @return Collection<int, ReseauSocialCompte> */
    public function getReseauxSociaux(): Collection
    {
        return $this->reseauxSociaux;
    }
}
