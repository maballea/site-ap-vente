<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    private $entityManager;
    private $panierRepository;

    // Injection de l'EntityManagerInterface et du PanierRepository
    public function __construct(EntityManagerInterface $entityManager, PanierRepository $panierRepository)
    {
        $this->entityManager = $entityManager;
        $this->panierRepository = $panierRepository;
    }

    #[Route('/panier', name: 'panier')]
    public function afficherPanier(): Response
    {
        // Assure-toi que l'utilisateur est connecté et a un panier
        $user = $this->getUser();
        if (!$user || !in_array('ROLE_CLIENT', $user->getRoles())) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        // Récupère le panier de l'utilisateur connecté
        $panier = $this->panierRepository->findOneBy(['user' => $user]);

        // Si le panier n'existe pas, en créer un
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        }

        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouterProduit(Produit $produit): Response
    {
        $user = $this->getUser();

        // Vérifie que l'utilisateur est connecté et a le rôle 'ROLE_CLIENT'
        if (!$user || !in_array('ROLE_CLIENT', $user->getRoles())) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        // Récupère le panier de l'utilisateur depuis le PanierRepository
        $panier = $this->panierRepository->findOneBy(['user' => $user]);

        // Si le panier n'existe pas, en créer un
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $this->entityManager->persist($panier);
            $this->entityManager->flush(); // Persiste le panier dans la base de données
        }

        // Ajoute le produit au panier si ce n'est pas déjà fait
        if (!$panier->getProduits()->contains($produit)) {
            $panier->addProduit($produit);
            $this->entityManager->flush(); // Sauvegarde les changements
        }

        // Redirige vers la page du panier
        return $this->redirectToRoute('panier');
    }

    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimerProduit(Produit $produit): Response
    {
        $user = $this->getUser();
        if (!$user || !in_array('ROLE_CLIENT', $user->getRoles())) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        // Récupère le panier de l'utilisateur
        $panier = $user->getPanier();

        // Retire le produit du panier
        if ($panier) {
            $panier->removeProduit($produit);
            $this->entityManager->flush();
        }

        // Redirige vers la page du panier
        return $this->redirectToRoute('panier');
    }
}
