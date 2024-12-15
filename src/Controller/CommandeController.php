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

        // Vérifier si l'utilisateur est un administrateur
        if ($this->isGranted('ROLE_ADMIN')) {
            // Si administrateur, récupérer toutes les commandes
            $commandes = $commandeRepository->findAll();
        } else {
            // Sinon, récupérer uniquement les commandes de l'utilisateur
            $commandes = $commandeRepository->findBy(['user' => $user]);
        }

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
        $commande->setEtatCommande('En attente de validation');
        $totalCommande = 0;

        // Ajouter les détails de la commande à partir des produits du panier
        foreach ($panier->getPanierProduits() as $item) {
            $produit = $item->getProduit();

            // Forcer le chargement de l'entité Produit
            $entityManager->initializeObject($produit);

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

        // Vider le panier
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
    public function details(int $id, CommandeRepository $commandeRepository): Response
    {
        // Récupérer la commande par son ID
        $commande = $commandeRepository->find($id);

        // Vérifier que la commande existe
        if (!$commande) {
            throw $this->createNotFoundException("Commande non trouvée.");
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier l'accès à la commande
        if (!$this->isGranted('ROLE_ADMIN') && $commande->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'accéder à cette commande.");
        }

        // Rendre la vue des détails de la commande
        return $this->render('commande/details.html.twig', [
            'commande' => $commande,
        ]);
    }

    /**
     * Modifier l'état d'une commande
     */
    #[Route('/commande/{id}/modifier', name: 'commande_modifier', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function modifierCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        // Vérifier l'état actuel et le changer
        if ($commande->getEtatCommande() === 'En attente de validation') {
            $commande->setEtatCommande('Validée');
        } else {
            $commande->setEtatCommande('En attente de validation');
        }

        // Sauvegarder la commande dans la base de données
        $entityManager->flush();

        $this->addFlash('success', 'État de la commande modifié avec succès.');

        return $this->redirectToRoute('commande');
    }

    /**
     * Supprimer une commande
     */
    #[Route('/commande/{id}/supprimer', name: 'commande_supprimer', methods: ['POST'])]
    #[IsGranted("ROLE_CLIENT")]
    public function supprimerCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier que la commande appartient bien à l'utilisateur connecté
        if ($commande->getUser() !== $user) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de supprimer cette commande.");
        }

        // Supprimer les détails de la commande
        foreach ($commande->getDetailsCommandes() as $detail) {
            $entityManager->remove($detail);
        }

        // Supprimer la commande
        $entityManager->remove($commande);
        $entityManager->flush();

        $this->addFlash('success', 'Commande supprimée avec succès.');

        return $this->redirectToRoute('commande');
    }
}
