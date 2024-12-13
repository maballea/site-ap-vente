<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailsCommande;
use App\Repository\PanierRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommandeController extends AbstractController
{
    /**
     * Liste des commandes d'un utilisateur connecté
     */
    #[Route('/commande', name: 'commande')]
    #[IsGranted("ROLE_CLIENT")]
    public function index(CommandeRepository $commandeRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les commandes de cet utilisateur
        $commandes = $commandeRepository->findBy(['user' => $user]);

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }


    /**
     * Créer une commande à partir du panier
     */
    #[Route('/commande/creer', name: 'commande_creer')]
    #[IsGranted("ROLE_CLIENT")]
    public function creerCommande(PanierRepository $panierRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le panier de l'utilisateur
        $panier = $panierRepository->findOneBy(['user' => $user]);

        // Vérifier que le panier n'est pas vide
        if (empty($panier) || $panier->getPanierProduits()->isEmpty()) {
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('panier');
        }

        // Créer une nouvelle commande
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDateCommande(new \DateTime());
        $commande->setEtatCommande('En cours de validation');
        $totalCommande = 0;

        // Ajouter les détails de la commande à partir des produits du panier
        foreach ($panier->getPanierProduits() as $item) {  // Accéder à chaque PanierProduit
            $produit = $item->getProduit();  // Récupérer le produit associé à PanierProduit

            // Forcer le chargement de l'entité Produit (pour éviter les problèmes avec les proxies)
            $entityManager->initializeObject($produit); // Cette ligne assure que le proxy est chargé.

            $quantite = $item->getQuantite();

            // Créer un détail de commande
            $detail = new DetailsCommande();
            $detail->setProduit($produit);
            $detail->setQuantite($quantite);
            $detail->setPrixUnitaire($produit->getPrix());

            // Ajouter le détail à la commande
            $commande->addDetailsCommande($detail);
            $entityManager->persist($detail);

            // Calculer le total de la commande
            $totalCommande += $produit->getPrix() * $quantite;
        }

        // Ajouter le total de la commande
        $commande->setTotalCommande($totalCommande);
        $entityManager->persist($commande);

        // Sauvegarder la commande et ses détails dans la base de données
        $entityManager->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'Commande créée avec succès !');

        foreach ($panier->getPanierProduits() as $item) {
            $entityManager->remove($item);
        }
        $entityManager->flush();
        

        // Rediriger vers la page des commandes
        return $this->redirectToRoute('commande');
    }
    
    /**
     * Détails d'une commande spécifique
     */
    #[Route('/commande/{id}', name: 'app_commande_details')]
    #[IsGranted("ROLE_CLIENT")]
    public function details(string $id, CommandeRepository $commandeRepository): Response
    {
        $id = (int) $id; // Conversion explicite en entier
        $commande = $commandeRepository->find($id);

        // Vérifier que la commande existe et appartient bien à l'utilisateur connecté
        $user = $this->getUser();
        if (!$commande || $commande->getUser() !== $user) {
            throw $this->createNotFoundException("Commande non trouvée ou accès refusé.");
        }

        return $this->render('commande/details.html.twig', [
            'commande' => $commande,
        ]);
    }
}
