<?php

namespace App\Entity;

use App\Repository\DanseEvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** Le programme : quelles danses, dans quel ordre, pour un événement donné. */
#[ORM\Entity(repositoryClass: DanseEvenementRepository::class)]
#[ORM\Table(name: 'danse_evenement')]
class DanseEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'programme')]
    #[ORM\JoinColumn(nullable: false)]
    private Evenement $evenement;

    #[ORM\ManyToOne(inversedBy: 'programmations')]
    #[ORM\JoinColumn(nullable: false)]
    private Danse $danse;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $heurePrevue = null;

    #[ORM\OneToMany(mappedBy: 'danseEvenement', targetEntity: ParticipationDanse::class, orphanRemoval: true)]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvenement(): Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(Evenement $evenement): static
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getDanse(): Danse
    {
        return $this->danse;
    }

    public function setDanse(Danse $danse): static
    {
        $this->danse = $danse;
        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getHeurePrevue(): ?\DateTimeImmutable
    {
        return $this->heurePrevue;
    }

    public function setHeurePrevue(?\DateTimeImmutable $heurePrevue): static
    {
        $this->heurePrevue = $heurePrevue;
        return $this;
    }

    /** @return Collection<int, ParticipationDanse> */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }
}
