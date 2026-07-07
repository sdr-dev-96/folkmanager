<?php

namespace App\Entity;

use App\Repository\GroupeTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comportements possibles : détermine quels champs supplémentaires
 * afficher dans le formulaire d'affectation d'un membre à ce groupe.
 */
enum ComportementGroupe: string
{
    case SIMPLE = 'simple';
    case DUO = 'duo';               // gère un partenaire/binôme (ex. danseurs)
    case INSTRUMENT = 'instrument'; // gère une liste d'instruments + back up
    case CHOEUR_LEAD = 'choeur_lead'; // gère un indicateur "voix principale"
}

#[ORM\Entity(repositoryClass: GroupeTypeRepository::class)]
#[ORM\Table(name: 'groupe_type')]
class GroupeType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'groupeTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private Association $association;

    #[ORM\Column(length: 100)]
    private string $nom;

    #[ORM\Column(length: 7)]
    private string $couleur;

    #[ORM\Column(length: 20, enumType: ComportementGroupe::class)]
    private ComportementGroupe $comportement;

    #[ORM\Column]
    private int $ordreAffichage = 0;

    #[ORM\OneToMany(mappedBy: 'groupeType', targetEntity: MembreGroupe::class, orphanRemoval: true)]
    private Collection $membreGroupes;

    public function __construct()
    {
        $this->membreGroupes = new ArrayCollection();
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

    public function getCouleur(): string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;
        return $this;
    }

    public function getComportement(): ComportementGroupe
    {
        return $this->comportement;
    }

    public function setComportement(ComportementGroupe $comportement): static
    {
        $this->comportement = $comportement;
        return $this;
    }

    public function getOrdreAffichage(): int
    {
        return $this->ordreAffichage;
    }

    public function setOrdreAffichage(int $ordreAffichage): static
    {
        $this->ordreAffichage = $ordreAffichage;
        return $this;
    }

    /** @return Collection<int, MembreGroupe> */
    public function getMembreGroupes(): Collection
    {
        return $this->membreGroupes;
    }
}
