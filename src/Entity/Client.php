<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $adresseLivraison;

    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'client')]
    private $historiqueCommandes;

    #[ORM\OneToOne(targetEntity: Panier::class)]
    private $panier;

    public function __construct()
    {
        $this->historiqueCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(string $adresseLivraison): self
    {
        $this->adresseLivraison = $adresseLivraison;
        return $this;
    }

    public function getHistoriqueCommandes()
    {
        return $this->historiqueCommandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->historiqueCommandes->contains($commande)) {
            $this->historiqueCommandes[] = $commande;
            $commande->setClient($this);
        }
        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(Panier $panier): self
    {
        $this->panier = $panier;
        return $this;
    }

    public function ajouterProduitPanier(Produit $produit)
    {
        if ($this->panier) {
            $this->panier->ajouterProduit($produit);
        }
    }

    public function modifierPanier(Produit $produit, int $quantite)
    {
        if ($this->panier) {
            $this->panier->modifierQuantite($produit, $quantite);
        }
    }

    public function validerCommande()
    {
        if ($this->panier && !$this->panier->estVide()) {
            $commande = new Commande();
            $commande->setDateCommande(new \DateTime());
            $commande->setTotalCommande($this->panier->calculerTotal());
            $commande->setEtatCommande('En traitement');
            $commande->setClient($this);
            $this->historiqueCommandes[] = $commande;

            $this->panier->viderPanier();

            return $commande;
        }
        return null;
    }
}

