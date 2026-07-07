<?php

namespace App\Entity;

use App\Repository\InstrumentJoueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstrumentJoueRepository::class)]
#[ORM\Table(name: 'instrument_joue')]
class InstrumentJoue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'instruments')]
    #[ORM\JoinColumn(nullable: false)]
    private MembreGroupe $membreGroupe;

    #[ORM\Column(length: 100)]
    private string $nomInstrument;

    #[ORM\Column]
    private bool $estBackup = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMembreGroupe(): MembreGroupe
    {
        return $this->membreGroupe;
    }

    public function setMembreGroupe(MembreGroupe $membreGroupe): static
    {
        $this->membreGroupe = $membreGroupe;
        return $this;
    }

    public function getNomInstrument(): string
    {
        return $this->nomInstrument;
    }

    public function setNomInstrument(string $nomInstrument): static
    {
        $this->nomInstrument = $nomInstrument;
        return $this;
    }

    public function isEstBackup(): bool
    {
        return $this->estBackup;
    }

    public function setEstBackup(bool $estBackup): static
    {
        $this->estBackup = $estBackup;
        return $this;
    }
}
