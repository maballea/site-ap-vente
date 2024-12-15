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
    // Route pour afficher le panier
    #[Route('/panier', name: 'panier')]
    public function afficherPanier(SessionInterface $session, ProduitRepository $produitRepository, Request $request, EntityManagerInterface $em)
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // Chercher le panier de l'utilisateur dans la base de données
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
        $dataPanier = [];
        $total = 0;

        // Si le panier existe, récupérer les produits associés
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
                // Calculer le total du panier
                $total += $produit->getPrix() * $quantite;
            }
        }

        // Récupérer le critère de tri
        $tri = $request->query->get('tri', 'nom'); // Par défaut, tri par nom

        // Appliquer le tri sur les produits du panier
        usort($dataPanier, function ($a, $b) use ($tri) {
            if ($tri === 'prix') {
                return $a['produit']->getPrix() <=> $b['produit']->getPrix();
            }
            return strcmp($a['produit']->getNom(), $b['produit']->getNom());
        });

        // Si le panier est vide, afficher un message
        if (empty($dataPanier)) {
            $this->addFlash('info', 'Votre panier est vide');
        }

        // Rendre la vue avec les données du panier
        return $this->render('panier/index.html.twig', [
            'dataPanier' => $dataPanier,
            'total' => $total,
            'tri' => $tri
        ]);
    }

    // Route pour ajouter un produit au panier
    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter', methods: ['POST'])]
    public function ajouterProduit(Produit $produit, SessionInterface $session, Request $request, EntityManagerInterface $em)
    {
        $panier = $session->get('panier', []); // Récupérer le panier de la session
        $id = $produit->getId();
        $quantite = (int) $request->request->get('quantite', 1); // Récupérer la quantité du formulaire

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

        // Ajouter le produit au panier dans la session
        if (isset($panier[$id])) {
            $panier[$id] += $quantite;
        } else {
            $panier[$id] = $quantite;
        }

        $session->set('panier', $panier); // Mettre à jour le panier dans la session

        // Enregistrer le produit dans la base de données via l'entité PanierProduit
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);

        // Si le panier n'existe pas, créer un nouveau panier
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

        // Si le produit existe déjà, mettre à jour la quantité
        if ($panierProduit) {
            $panierProduit->setQuantite($panierProduit->getQuantite() + $quantite);
        } else {
            // Sinon, ajouter un nouveau produit au panier
            $panierProduit = new PanierProduit();
            $panierProduit->setPanier($panierEntity);
            $panierProduit->setProduit($produit);
            $panierProduit->setQuantite($quantite);
            $em->persist($panierProduit);
        }

        // Réduire le stock du produit
        $produit->setStock($produit->getStock() - $quantite);
        $em->persist($produit);

        $em->flush(); // Sauvegarder les changements en base de données
        $this->addFlash('success', 'Produit ajouté au panier avec succès.');

        return $this->redirectToRoute('produit_catalogue');
    }

    // Route pour réduire la quantité d'un produit dans le panier
    #[Route('/panier/reduire/{id}', name: 'panier_reduire')]
    public function reduireProduit(Produit $produit, Request $request, SessionInterface $session, EntityManagerInterface $em)
    {
        $quantiteRequise = (int) $request->request->get('quantite', 1); // Récupérer la quantité spécifiée dans le formulaire, ou 1 par défaut
        $panier = $session->get('panier', []); // Récupérer le panier de la session
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

        $session->set('panier', $panier); // Mettre à jour le panier dans la session

        // Réduire la quantité dans la base de données pour le panier utilisateur
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
        $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
            'panier' => $panierEntity,
            'produit' => $produit
        ]);

        // Si le produit est trouvé, mettre à jour la quantité dans la base de données
        if ($panierProduit) {
            if (isset($panier[$id]) && $panier[$id] > 0) {
                $panierProduit->setQuantite($panier[$id]);
            } else {
                $em->remove($panierProduit); // Supprimer le produit si la quantité est 0
            }
        }

        $em->flush(); // Sauvegarder les changements en base de données

        $this->addFlash('success', 'Quantité réduite et stock mis à jour.');
        return $this->redirectToRoute('panier');
    }

    // Route pour supprimer un produit du panier
    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimerProduit(Produit $produit, SessionInterface $session, EntityManagerInterface $em)
    {
        $panier = $session->get('panier', []); // Récupérer le panier de la session
        $id = $produit->getId();

        // Si le produit est dans le panier, le supprimer
        if (isset($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier); // Mettre à jour le panier dans la session

        // Supprimer le produit de la base de données
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);
        $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
            'panier' => $panierEntity,
            'produit' => $produit
        ]);

        // Supprimer le produit du panier dans la base de données
        if ($panierProduit) {
            $em->remove($panierProduit);
        }

        $em->flush(); // Sauvegarder les changements en base de données

        $this->addFlash('success', 'Produit supprimé du panier.');
        return $this->redirectToRoute('panier');
    }

    // Route pour vider tout le panier
    #[Route('/panier/supprimer-panier', name: 'panier_supprimer_panier')]
    public function supprimerTout(SessionInterface $session, EntityManagerInterface $em)
    {
        $session->remove('panier'); // Supprimer le panier de la session

        // Supprimer tous les produits du panier dans la base de données
        $user = $this->getUser();
        $panierEntity = $em->getRepository(Panier::class)->findOneBy(['user' => $user]);

        if ($panierEntity) {
            $panierProduits = $em->getRepository(PanierProduit::class)->findBy(['panier' => $panierEntity]);
            foreach ($panierProduits as $panierProduit) {
                $em->remove($panierProduit); // Supprimer chaque produit du panier
            }
            $em->flush(); // Sauvegarder les changements en base de données
        }

        $this->addFlash('success', 'Tout le panier a été vidé.');
        return $this->redirectToRoute('panier');
    }

    // Route pour obtenir la quantité totale de produits dans le panier
    #[Route('/panier/quantite', name: 'panier_quantite', methods: ['GET'])]
    public function quantitePanier(SessionInterface $session)
    {
        $panier = $session->get('panier', []); // Récupérer le panier de la session
        $quantiteTotale = array_sum($panier); // Calculer la quantité totale des produits dans le panier

        return $this->json(['quantite' => $quantiteTotale]); // Retourner la quantité totale en format JSON
    }
}
