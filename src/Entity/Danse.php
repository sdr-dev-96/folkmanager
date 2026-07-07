<?php

namespace App\Entity;

use App\Repository\DanseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DanseRepository::class)]
#[ORM\Table(name: 'danse')]
class Danse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'danses')]
    #[ORM\JoinColumn(nullable: false)]
    private Association $association;

    #[ORM\Column(length: 150)]
    private string $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $paroles = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreMaxParticipants = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'danse', targetEntity: DanseMembre::class, orphanRemoval: true)]
    private Collection $membresHabilites;

    #[ORM\OneToMany(mappedBy: 'danse', targetEntity: DanseEvenement::class, orphanRemoval: true)]
    private Collection $programmations;

    public function __construct()
    {
        $this->membresHabilites = new ArrayCollection();
        $this->programmations = new ArrayCollection();
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

    public function getParoles(): ?string
    {
        return $this->paroles;
    }

    public function setParoles(?string $paroles): static
    {
        $this->paroles = $paroles;
        return $this;
    }

    public function getNombreMaxParticipants(): ?int
    {
        return $this->nombreMaxParticipants;
    }

    public function setNombreMaxParticipants(?int $nombreMaxParticipants): static
    {
        $this->nombreMaxParticipants = $nombreMaxParticipants;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /** @return Collection<int, DanseMembre> */
    public function getMembresHabilites(): Collection
    {
        return $this->membresHabilites;
    }

    /** @return Collection<int, DanseEvenement> */
    public function getProgrammations(): Collection
    {
        return $this->programmations;
    }
}
