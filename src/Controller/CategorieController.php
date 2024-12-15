<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categorie')]
final class CategorieController extends AbstractController
{
    #[Route(name: 'categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository, ProduitRepository $produitRepository): Response
    {
        // Récupération des catégories
        $categories = $categorieRepository->findAll();

        // Vérification produits dans chaque catégorie
        foreach ($categories as $categorie) {
            $produits = $produitRepository->findBy(['categorie' => $categorie]);
            $categorie->hasProducts = count($produits) > 0; // Catégorie avec produits
        }

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories, // Données des catégories
        ]);
    }

    #[Route('/new', name: 'categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création nouvelle catégorie
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        // Validation du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush(); // Sauvegarder dans la base

            return $this->redirectToRoute('categorie_index', [], Response::HTTP_SEE_OTHER); // Redirection après ajout
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie, // Catégorie à créer
            'form' => $form, // Formulaire de création
        ]);
    }

    #[Route('/{id}/edit', name: 'categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        // Vérification produits associés à la catégorie
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        // Validation du formulaire de modification
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Sauvegarde des modifications

            return $this->redirectToRoute('categorie_index', [], Response::HTTP_SEE_OTHER); // Redirection après modification
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie, // Catégorie à éditer
            'form' => $form, // Formulaire d'édition
            'hasProducts' => count($produits) > 0, // Indicateur produits associés
        ]);
    }

    #[Route('/{id}', name: 'categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        // Vérification produits dans la catégorie avant suppression
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        // Condition d'empêchement de suppression si produits présents
        if (count($produits) > 0) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette catégorie car elle contient des produits.'); // Message erreur
            return $this->redirectToRoute('categorie_index'); // Redirection après erreur
        }

        // Suppression de la catégorie si pas de produits
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie); // Suppression de la catégorie
            $entityManager->flush(); // Sauvegarde après suppression
        }

        return $this->redirectToRoute('categorie_index', [], Response::HTTP_SEE_OTHER); // Redirection après suppression
    }
}
