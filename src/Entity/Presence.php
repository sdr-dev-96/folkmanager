<?php

namespace App\Entity;

use App\Repository\PresenceRepository;
use Doctrine\ORM\Mapping as ORM;

enum StatutPresence: string
{
    case PRESENT = 'present';
    case ABSENT = 'absent';
}

#[ORM\Entity(repositoryClass: PresenceRepository::class)]
#[ORM\Table(name: 'presence')]
#[ORM\UniqueConstraint(name: 'uniq_evenement_membre', columns: ['evenement_id', 'membre_id'])]
class Presence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    #[ORM\JoinColumn(nullable: false)]
    private Evenement $evenement;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    #[ORM\JoinColumn(nullable: false)]
    private Membre $membre;

    #[ORM\Column(length: 10, enumType: StatutPresence::class)]
    private StatutPresence $statut;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getMembre(): Membre
    {
        return $this->membre;
    }

    public function setMembre(Membre $membre): static
    {
        $this->membre = $membre;
        return $this;
    }

    public function getStatut(): StatutPresence
    {
        return $this->statut;
    }

    public function setStatut(StatutPresence $statut): static
    {
        $this->statut = $statut;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
