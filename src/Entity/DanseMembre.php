<?php

namespace App\Entity;

use App\Repository\DanseMembreRepository;
use Doctrine\ORM\Mapping as ORM;

/** Qui sait exécuter cette danse (répertoire de compétences, indépendant d'un événement précis). */
#[ORM\Entity(repositoryClass: DanseMembreRepository::class)]
#[ORM\Table(name: 'danse_membre')]
#[ORM\UniqueConstraint(name: 'uniq_danse_membre', columns: ['danse_id', 'membre_id'])]
class DanseMembre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'membresHabilites')]
    #[ORM\JoinColumn(nullable: false)]
    private Danse $danse;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Membre $membre;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMembre(): Membre
    {
        return $this->membre;
    }

    public function setMembre(Membre $membre): static
    {
        $this->membre = $membre;
        return $this;
    }
}
