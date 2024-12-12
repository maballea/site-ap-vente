<?php

namespace App\Entity;

use App\Repository\PanierProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierProduitRepository::class)]
class PanierProduit
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Panier::class, inversedBy: 'panierProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private $panier;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'panierProduits')]
    #[ORM\JoinColumn(nullable: false)]
    private $produit;

    #[ORM\Column(type: 'integer')]
    private $quantite;

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(Panier $panier): self
    {
        $this->panier = $panier;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
}
