<?php

namespace App\Entity;

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
}
