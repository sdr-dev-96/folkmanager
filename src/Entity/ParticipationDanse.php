<?php

namespace App\Entity;

use App\Repository\ParticipationDanseRepository;
use Doctrine\ORM\Mapping as ORM;

enum StatutParticipation: string
{
    case CONFIRME = 'confirme';
    case ABSENT = 'absent';
    case REMPLACANT = 'remplacant';
}

/** Qui participe effectivement à cette danse, pour cet événement précis. */
#[ORM\Entity(repositoryClass: ParticipationDanseRepository::class)]
#[ORM\Table(name: 'participation_danse')]
#[ORM\UniqueConstraint(name: 'uniq_danse_evenement_membre', columns: ['danse_evenement_id', 'membre_id'])]
class ParticipationDanse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    private DanseEvenement $danseEvenement;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Membre $membre;

    #[ORM\Column(length: 15, enumType: StatutParticipation::class)]
    private StatutParticipation $statut = StatutParticipation::CONFIRME;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $instrumentJoue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDanseEvenement(): DanseEvenement
    {
        return $this->danseEvenement;
    }

    public function setDanseEvenement(DanseEvenement $danseEvenement): static
    {
        $this->danseEvenement = $danseEvenement;
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

    public function getStatut(): StatutParticipation
    {
        return $this->statut;
    }

    public function setStatut(StatutParticipation $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getInstrumentJoue(): ?string
    {
        return $this->instrumentJoue;
    }

    public function setInstrumentJoue(?string $instrumentJoue): static
    {
        $this->instrumentJoue = $instrumentJoue;
        return $this;
    }
}
