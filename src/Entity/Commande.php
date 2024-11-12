<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $idCommande;

    #[ORM\Column(type: 'datetime')]
    private $dateCommande;

    #[ORM\Column(type: 'string', length: 255)]
    private $etatCommande;

    #[ORM\Column(type: 'decimal', scale: 2)]
    private $totalCommande;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    private $client;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Panier $lesPaniers = null;

    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getEtatCommande(): ?string
    {
        return $this->etatCommande;
    }

    public function setEtatCommande(string $etatCommande): self
    {
        $this->etatCommande = $etatCommande;
        return $this;
    }

    public function getTotalCommande(): ?float
    {
        return $this->totalCommande;
    }

    public function setTotalCommande(float $totalCommande): self
    {
        $this->totalCommande = $totalCommande;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function calculerTotal(): float
    {
        return $this->totalCommande;
    }

    public function suivreCommande(): string
    {
        return "L'état actuel de la commande est : " . $this->etatCommande;
    }

    public function getLesPaniers(): ?Panier
    {
        return $this->lesPaniers;
    }

    public function setLesPaniers(?Panier $lesPaniers): static
    {
        $this->lesPaniers = $lesPaniers;

        return $this;
    }
}
