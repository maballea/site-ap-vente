<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'decimal', scale: 2)]
    private $prix;

    #[ORM\Column(type: 'integer')]
    private $stock;

    /**
     * @var Collection<int, ParcoursEntrepot>
     */
    #[ORM\ManyToMany(targetEntity: ParcoursEntrepot::class, mappedBy: 'lesProduits')]
    private Collection $lesParcours;

    /**
     * @var Collection<int, Panier>
     */
    #[ORM\ManyToMany(targetEntity: Panier::class, mappedBy: 'lesProduits')]
    private Collection $lesPaniers;

    #[ORM\ManyToOne(inversedBy: 'lesProduits')]
    private ?DetailsCommande $lesDetailsCommande = null;

    #[ORM\ManyToOne(inversedBy: 'lesProduits')]
    private ?Commande $lesCommandes = null;

    public function __construct()
    {
        $this->lesParcours = new ArrayCollection();
        $this->lesPaniers = new ArrayCollection();
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        return $this;
    }

    public function afficherDetails(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prix' => $this->prix,
            'stock' => $this->stock,
        ];
    }

    /**
     * @return Collection<int, ParcoursEntrepot>
     */
    public function getLesParcours(): Collection
    {
        return $this->lesParcours;
    }

    public function addLesParcour(ParcoursEntrepot $lesParcour): static
    {
        if (!$this->lesParcours->contains($lesParcour)) {
            $this->lesParcours->add($lesParcour);
            $lesParcour->addLesProduit($this);
        }

        return $this;
    }

    public function removeLesParcour(ParcoursEntrepot $lesParcour): static
    {
        if ($this->lesParcours->removeElement($lesParcour)) {
            $lesParcour->removeLesProduit($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getLesPaniers(): Collection
    {
        return $this->lesPaniers;
    }

    public function addLesPanier(Panier $lesPanier): static
    {
        if (!$this->lesPaniers->contains($lesPanier)) {
            $this->lesPaniers->add($lesPanier);
            $lesPanier->addLesProduit($this);
        }

        return $this;
    }

    public function removeLesPanier(Panier $lesPanier): static
    {
        if ($this->lesPaniers->removeElement($lesPanier)) {
            $lesPanier->removeLesProduit($this);
        }

        return $this;
    }

    public function getLesDetailsCommande(): ?DetailsCommande
    {
        return $this->lesDetailsCommande;
    }

    public function setLesDetailsCommande(?DetailsCommande $lesDetailsCommande): static
    {
        $this->lesDetailsCommande = $lesDetailsCommande;

        return $this;
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

