<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ParcoursEntrepot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $idParcours;

    #[ORM\Column(type: 'array')]
    private $listeProduits;

    #[ORM\Column(type: 'string', length: 255)]
    private $cheminOptimal;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'lesParcours')]
    private Collection $lesProduits;

    public function __construct()
    {
        $this->lesProduits = new ArrayCollection();
    }

    public function getIdParcours(): ?int
    {
        return $this->idParcours;
    }

    public function getListeProduits(): ?array
    {
        return $this->listeProduits;
    }

    public function setListeProduits(array $listeProduits): self
    {
        $this->listeProduits = $listeProduits;
        return $this;
    }

    public function getCheminOptimal(): ?string
    {
        return $this->cheminOptimal;
    }

    public function calculerCheminOptimal()
    {
        // Algorithme pour calculer le chemin optimal
        $this->cheminOptimal = 'Chemin optimal calculé';
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getLesProduits(): Collection
    {
        return $this->lesProduits;
    }

    public function addLesProduit(Produit $lesProduit): static
    {
        if (!$this->lesProduits->contains($lesProduit)) {
            $this->lesProduits->add($lesProduit);
        }

        return $this;
    }

    public function removeLesProduit(Produit $lesProduit): static
    {
        $this->lesProduits->removeElement($lesProduit);

        return $this;
    }
}
