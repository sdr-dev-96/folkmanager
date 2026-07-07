<?php

namespace App\Entity;

use App\Repository\ReseauSocialCompteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

enum Plateforme: string
{
    case INSTAGRAM = 'instagram';
    case FACEBOOK = 'facebook';
    case TIKTOK = 'tiktok';
}

#[ORM\Entity(repositoryClass: ReseauSocialCompteRepository::class)]
#[ORM\Table(name: 'reseau_social_compte')]
class ReseauSocialCompte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reseauxSociaux')]
    #[ORM\JoinColumn(nullable: false)]
    private Association $association;

    #[ORM\Column(length: 20, enumType: Plateforme::class)]
    private Plateforme $plateforme;

    #[ORM\Column(length: 150)]
    private string $identifiantExterne;

    /** Stocké chiffré (cf. crypto_key dans .env) — ne jamais logguer cette valeur. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $accessTokenChiffre = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $derniereSynchronisation = null;

    #[ORM\OneToMany(mappedBy: 'compte', targetEntity: PublicationCache::class, orphanRemoval: true)]
    private Collection $publications;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
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

    public function getPlateforme(): Plateforme
    {
        return $this->plateforme;
    }

    public function setPlateforme(Plateforme $plateforme): static
    {
        $this->plateforme = $plateforme;
        return $this;
    }

    public function getIdentifiantExterne(): string
    {
        return $this->identifiantExterne;
    }

    public function setIdentifiantExterne(string $identifiantExterne): static
    {
        $this->identifiantExterne = $identifiantExterne;
        return $this;
    }

    public function getAccessTokenChiffre(): ?string
    {
        return $this->accessTokenChiffre;
    }

    public function setAccessTokenChiffre(?string $accessTokenChiffre): static
    {
        $this->accessTokenChiffre = $accessTokenChiffre;
        return $this;
    }

    public function getDerniereSynchronisation(): ?\DateTimeImmutable
    {
        return $this->derniereSynchronisation;
    }

    public function setDerniereSynchronisation(?\DateTimeImmutable $derniereSynchronisation): static
    {
        $this->derniereSynchronisation = $derniereSynchronisation;
        return $this;
    }

    /** @return Collection<int, PublicationCache> */
    public function getPublications(): Collection
    {
        return $this->publications;
    }
}
