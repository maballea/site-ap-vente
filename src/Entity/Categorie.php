<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Produit;  // Assurez-vous d'ajouter cette importation

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    // Relation OneToMany : Une catégorie peut avoir plusieurs produits
    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Produit::class)]
    private $produits;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    // Getter et setter pour les produits associés
    public function getProduits()
    {
        return $this->produits;
    }

    public function setProduits($produits): static
    {
        $this->produits = $produits;
        return $this;
    }
}
