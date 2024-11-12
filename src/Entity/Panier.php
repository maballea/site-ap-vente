<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $idPanier;

    #[ORM\ManyToMany(targetEntity: Produit::class)]
    private $produits;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Commande $lesCommandes = null;

    #[ORM\Column(length: 255)]
    private ?string $lesPaniers = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getIdPanier(): ?int
    {
        return $this->idPanier;
    }

    public function getProduits()
    {
        return $this->produits;
    }

    public function ajouterProduit(Produit $produit)
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
        }
    }

    public function modifierQuantite(Produit $produit, int $quantite)
    {
        foreach ($this->produits as $index => $p) {
            if ($p->getId() === $produit->getId()) {
                if ($quantite <= 0) {
                    unset($this->produits[$index]);
                }
                // Implémenter gestion quantité si nécessaire
            }
        }
    }

    public function calculerTotal(): float
    {
        $total = 0;
        foreach ($this->produits as $produit) {
            $total += $produit->getPrix();
        }
        return $total;
    }

    public function estVide(): bool
    {
        return empty($this->produits);
    }

    public function viderPanier()
    {
        $this->produits = [];
    }

    public function getLesCommandes(): ?Commande
    {
        return $this->lesCommandes;
    }

    public function setLesCommandes(?Commande $lesCommandes): static
    {
        $this->lesCommandes = $lesCommandes;

        return $this;
    }
}