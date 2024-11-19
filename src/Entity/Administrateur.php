<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Administrateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var Collection<int, ParcoursEntrepot>
     */
    #[ORM\ManyToMany(targetEntity: ParcoursEntrepot::class, inversedBy: 'lesAdministrateurs')]
    private Collection $lesParcoursEntrepots;

    public function __construct()
    {
        $this->lesParcoursEntrepots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function gérerCommandes(array $commandes)
    {
        foreach ($commandes as $commande) {
            if ($commande->getEtatCommande() === 'En traitement') {
                $commande->setEtatCommande('Expédié');
            }
        }
    }

    public function optimiserParcoursEntrepot(ParcoursEntrepot $parcours)
    {
        $parcours->calculerCheminOptimal();
    }

    /**
     * @return Collection<int, ParcoursEntrepot>
     */
    public function getLesParcoursEntrepots(): Collection
    {
        return $this->lesParcoursEntrepots;
    }

    public function addLesParcoursEntrepot(ParcoursEntrepot $lesParcoursEntrepot): static
    {
        if (!$this->lesParcoursEntrepots->contains($lesParcoursEntrepot)) {
            $this->lesParcoursEntrepots->add($lesParcoursEntrepot);
        }

        return $this;
    }

    public function removeLesParcoursEntrepot(ParcoursEntrepot $lesParcoursEntrepot): static
    {
        $this->lesParcoursEntrepots->removeElement($lesParcoursEntrepot);

        return $this;
    }
}

