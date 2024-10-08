<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Administrateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

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
}

