<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'panier')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    // La relation ManyToMany avec Produit
    #[ORM\ManyToMany(targetEntity: Produit::class, mappedBy: 'paniers')]
    private $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
{
    if (!$this->produits->contains($produit)) {
        $this->produits[] = $produit;
        $produit->getPaniers()->add($this); // Synchronise avec l'entité Produit
    }
    return $this;
}



public function removeProduit(Produit $produit): self
{
    if ($this->produits->contains($produit)) {
        $this->produits->removeElement($produit);
        $produit->getPaniers()->removeElement($this); // Synchronisation avec l'entité Produit
    }
    return $this;
}

}
