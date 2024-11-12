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

    public function __construct()
    {
        $this->lesParcours = new ArrayCollection();
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
}

