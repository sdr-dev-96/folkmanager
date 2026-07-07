<?php

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
#[ORM\Table(name: 'membre')]
class Membre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'membres')]
    #[ORM\JoinColumn(nullable: false)]
    private Association $association;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\OneToOne(targetEntity: Utilisateur::class, mappedBy: 'membre', cascade: ['persist', 'remove'])]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'membre', targetEntity: MembreGroupe::class, orphanRemoval: true)]
    private Collection $membreGroupes;

    #[ORM\OneToMany(mappedBy: 'membre', targetEntity: Presence::class, orphanRemoval: true)]
    private Collection $presences;

    public function __construct()
    {
        $this->membreGroupes = new ArrayCollection();
        $this->presences = new ArrayCollection();
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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /** @return Collection<int, MembreGroupe> */
    public function getMembreGroupes(): Collection
    {
        return $this->membreGroupes;
    }

    /** @return Collection<int, Presence> */
    public function getPresences(): Collection
    {
        return $this->presences;
    }
}
