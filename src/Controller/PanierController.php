<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Repository\ProduitRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
public function afficherPanier(SessionInterface $session, ProduitRepository $produitRepository, Request $request, EntityManagerInterface $em)
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();
    
    

    // Chercher le panier de l'utilisateur dans la base de données
    $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
    $dataPanier = [];
    $total = 0;

    if ($panierEntity) {
        // Récupérer les produits associés au panier
        $panierProduits = $em->getRepository(PanierProduit::class)->findBy(['panier' => $panierEntity]);

        foreach ($panierProduits as $panierProduit) {
            $produit = $panierProduit->getProduit();
            $quantite = $panierProduit->getQuantite();
            $dataPanier[] = [
                'produit' => $produit,
                'quantite' => $quantite
            ];
            $total += $produit->getPrix() * $quantite;
        }
    }

    // Récupérer le critère de tri
    $tri = $request->query->get('tri', 'nom'); // Par défaut, tri par nom

    // Appliquer le tri
    usort($dataPanier, function ($a, $b) use ($tri) {
        if ($tri === 'prix') {
            return $a['produit']->getPrix() <=> $b['produit']->getPrix();
        }
        return strcmp($a['produit']->getNom(), $b['produit']->getNom());
    });

    // Si le panier est vide
    if (empty($dataPanier)) {
        $this->addFlash('info', 'Votre panier est vide');
    }

    return $this->render('panier/index.html.twig', [
        'dataPanier' => $dataPanier,
        'total' => $total,
        'tri' => $tri
    ]);
}


#[Route('/panier/ajouter/{id}', name: 'panier_ajouter', methods: ['POST'])]
public function ajouterProduit(Produit $produit, SessionInterface $session, Request $request, EntityManagerInterface $em)
{
    $panier = $session->get('panier', []);
    $id = $produit->getId();
    $quantite = (int) $request->request->get('quantite', 1); // Récupère la quantité du formulaire

    // Vérifier si la quantité demandée est valide
    if ($quantite <= 0) {
        $this->addFlash('error', 'La quantité doit être supérieure à zéro.');
        return $this->redirectToRoute('produit_catalogue');
    }

    // Vérifier si la quantité demandée est disponible en stock
    if ($quantite > $produit->getStock()) {
        $this->addFlash('error', 'Quantité de produit indisponible.');
        return $this->redirectToRoute('produit_catalogue');
    }

    // Ajouter au panier dans la session
    if (isset($panier[$id])) {
        $panier[$id] += $quantite;
    } else {
        $panier[$id] = $quantite;
    }

    $session->set('panier', $panier);

    // Enregistrer dans la base de données via l'entité PanierProduit
    $user = $this->getUser();
    $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);

    if (!$panierEntity) {
        $panierEntity = new Panier();
        $panierEntity->setUser($user);
        $em->persist($panierEntity);
    }

    // Vérifier si le produit existe déjà dans le panier pour cet utilisateur
    $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
        'panier' => $panierEntity,
        'produit' => $produit
    ]);

    if ($panierProduit) {
        $panierProduit->setQuantite($panierProduit->getQuantite() + $quantite);
    } else {
        $panierProduit = new PanierProduit();
        $panierProduit->setPanier($panierEntity);
        $panierProduit->setProduit($produit);
        $panierProduit->setQuantite($quantite);
        $em->persist($panierProduit);
    }

    // Réduire le stock du produit
    $produit->setStock($produit->getStock() - $quantite);
    $em->persist($produit);

    $em->flush();
    $this->addFlash('success', 'Produit ajouté au panier avec succès.');

    return $this->redirectToRoute('produit_catalogue');
}


#[Route('/panier/reduire/{id}', name: 'panier_reduire')]
public function reduireProduit(Produit $produit, Request $request, SessionInterface $session, EntityManagerInterface $em)
{
    $quantiteRequise = (int) $request->request->get('quantite', 1); // Récupérer la quantité spécifiée dans le formulaire, ou 1 par défaut
    $panier = $session->get('panier', []);
    $id = $produit->getId();

    // Vérifier si le produit est dans le panier
    if (isset($panier[$id])) {
        $quantiteReduite = min($quantiteRequise, $panier[$id]); // Limiter la réduction à la quantité présente dans le panier

        // Réduire la quantité ou supprimer le produit du panier
        if ($panier[$id] > $quantiteRequise) {
            $panier[$id] -= $quantiteRequise;
        } else {
            unset($panier[$id]);
        }

        // Ajouter la quantité réduite au stock du produit
        $produit->setStock($produit->getStock() + $quantiteReduite);
        $em->persist($produit);
    }

    $session->set('panier', $panier);

    // Réduire la quantité dans la base de données pour le panier utilisateur
    $user = $this->getUser();
    $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
    $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
        'panier' => $panierEntity,
        'produit' => $produit
    ]);

    if ($panierProduit) {
        if (isset($panier[$id]) && $panier[$id] > 0) {
            $panierProduit->setQuantite($panier[$id]);
        } else {
            $em->remove($panierProduit);
        }
    }

    $em->flush();

    $this->addFlash('success', 'Quantité réduite et stock mis à jour.');
    return $this->redirectToRoute('panier');
}



    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimerProduit(Produit $produit, SessionInterface $session, EntityManagerInterface $em)
    {
        $panier = $session->get('panier', []);
        $id = $produit->getId();

        if (isset($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        // Supprimer le produit de la base de données
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
        $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
            'panier' => $panierEntity,
            'produit' => $produit
        ]);

        if ($panierProduit) {
            $em->remove($panierProduit);
        }

        $em->flush();

        $this->addFlash('success', 'Produit supprimé du panier.');
        return $this->redirectToRoute('panier');
    }

    #[Route('/panier/supprimer-panier', name: 'panier_supprimer_panier')]
    public function supprimerTout(SessionInterface $session, EntityManagerInterface $em)
    {
        $session->remove('panier');

        // Supprimer tous les produits du panier dans la base de données
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);

        if ($panierEntity) {
            $panierProduits = $em->getRepository(PanierProduit::class)->findBy(['panier' => $panierEntity]);
            foreach ($panierProduits as $panierProduit) {
                $em->remove($panierProduit);
            }
            $em->flush();
        }

        $this->addFlash('success', 'Tout le panier a été vidé.');
        return $this->redirectToRoute('panier');
    }

    #[Route('/panier/quantite', name: 'panier_quantite', methods: ['GET'])]
    public function quantitePanier(SessionInterface $session)
    {
        $panier = $session->get('panier', []);
        $quantiteTotale = array_sum($panier);

        return $this->json(['quantite' => $quantiteTotale]);
    }
}
