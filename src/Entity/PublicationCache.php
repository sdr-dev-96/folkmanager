<?php

namespace App\Entity;

use App\Repository\PublicationCacheRepository;
use Doctrine\ORM\Mapping as ORM;

enum TypePublication: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case CAROUSEL = 'carousel';
}

/** Publications récupérées périodiquement via l'API du réseau social, affichées en carrousel. */
#[ORM\Entity(repositoryClass: PublicationCacheRepository::class)]
#[ORM\Table(name: 'publication_cache')]
class PublicationCache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false)]
    private ReseauSocialCompte $compte;

    #[ORM\Column(length: 255)]
    private string $urlMedia;

    #[ORM\Column(length: 15, enumType: TypePublication::class)]
    private TypePublication $type;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $legende = null;

    #[ORM\Column(length: 255)]
    private string $urlPermalien;

    #[ORM\Column]
    private \DateTimeImmutable $publieeLe;

    #[ORM\Column]
    private \DateTimeImmutable $recupereeLe;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompte(): ReseauSocialCompte
    {
        return $this->compte;
    }

    public function setCompte(ReseauSocialCompte $compte): static
    {
        $this->compte = $compte;
        return $this;
    }

    public function getUrlMedia(): string
    {
        return $this->urlMedia;
    }

    public function setUrlMedia(string $urlMedia): static
    {
        $this->urlMedia = $urlMedia;
        return $this;
    }

    public function getType(): TypePublication
    {
        return $this->type;
    }

    public function setType(TypePublication $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function setLegende(?string $legende): static
    {
        $this->legende = $legende;
        return $this;
    }

    public function getUrlPermalien(): string
    {
        return $this->urlPermalien;
    }

    public function setUrlPermalien(string $urlPermalien): static
    {
        $this->urlPermalien = $urlPermalien;
        return $this;
    }

    public function getPublieeLe(): \DateTimeImmutable
    {
        return $this->publieeLe;
    }

    public function setPublieeLe(\DateTimeImmutable $publieeLe): static
    {
        $this->publieeLe = $publieeLe;
        return $this;
    }

    public function getRecupereeLe(): \DateTimeImmutable
    {
        return $this->recupereeLe;
    }

    public function setRecupereeLe(\DateTimeImmutable $recupereeLe): static
    {
        $this->recupereeLe = $recupereeLe;
        return $this;
    }
}
