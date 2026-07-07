<?php

namespace App\Entity;

use App\Repository\PlanningConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningConfigRepository::class)]
#[ORM\Table(name: 'planning_config')]
class PlanningConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'planningConfigs')]
    #[ORM\JoinColumn(nullable: false)]
    private Association $association;

    /** 1 = lundi ... 7 = dimanche (ISO-8601) */
    #[ORM\Column]
    private int $jourSemaine;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $typesDisponibles = [];

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

    public function getJourSemaine(): int
    {
        return $this->jourSemaine;
    }

    public function setJourSemaine(int $jourSemaine): static
    {
        $this->jourSemaine = $jourSemaine;
        return $this;
    }

    public function getTypesDisponibles(): array
    {
        return $this->typesDisponibles;
    }

    public function setTypesDisponibles(array $typesDisponibles): static
    {
        $this->typesDisponibles = $typesDisponibles;
        return $this;
    }
}
