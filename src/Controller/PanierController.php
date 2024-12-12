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
    
    if (!$user) {
        // Si l'utilisateur n'est pas connecté, on redirige ou on affiche un message
        return $this->redirectToRoute('login');
    }

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

        if ($quantite <= 0) {
            $this->addFlash('error', 'La quantité doit être supérieure à zéro.');
            return $this->redirectToRoute('panier');
        }

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

        $em->flush();
        $this->addFlash('success', 'Produit ajouté au panier avec succès.');

        return $this->redirectToRoute('panier');
    }

    #[Route('/panier/reduire/{id}', name: 'panier_reduire')]
    public function reduireProduit(Produit $produit, SessionInterface $session, EntityManagerInterface $em)
    {
        $panier = $session->get('panier', []);
        $id = $produit->getId();

        if (isset($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);

        // Réduire la quantité dans la base de données
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
        $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
            'panier' => $panierEntity,
            'produit' => $produit
        ]);

        if ($panierProduit) {
            if ($panier[$id] > 0) {
                $panierProduit->setQuantite($panier[$id]);
            } else {
                $em->remove($panierProduit);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Quantité réduite.');
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
