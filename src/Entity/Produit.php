<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'text')]
    private $description;

    #[ORM\Column(type: 'float')]
    private $prix;

    

    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: DetailsCommande::class, cascade: ['persist', 'remove'])]
private Collection $detailsCommandes;

public function __construct()
{
    $this->detailsCommandes = new ArrayCollection();
}

public function getDetailsCommandes(): Collection
{
    return $this->detailsCommandes;
}

public function addDetailsCommande(DetailsCommande $detailsCommande): self
{
    if (!$this->detailsCommandes->contains($detailsCommande)) {
        $this->detailsCommandes[] = $detailsCommande;
        $detailsCommande->setProduit($this);
    }

    return $this;
}

public function removeDetailsCommande(DetailsCommande $detailsCommande): self
{
    if ($this->detailsCommandes->removeElement($detailsCommande)) {
        // Set the owning side to null (unless already changed)
        if ($detailsCommande->getProduit() === $this) {
            $detailsCommande->setProduit(null);
        }
    }

    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }
}
