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
    // Récupérer toutes les catégories
    $categories = $categorieRepository->findAll();

    // Ajouter la variable hasProducts à chaque catégorie
    foreach ($categories as $categorie) {
        // Vérifier si la catégorie contient des produits
        $produits = $produitRepository->findBy(['categorie' => $categorie]);
        $categorie->hasProducts = count($produits) > 0;
    }

    return $this->render('categorie/index.html.twig', [
        'categories' => $categories,
    ]);
}


    #[Route('/new', name: 'categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        // Vérifier si la catégorie a des produits associés
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
            'hasProducts' => count($produits) > 0,  // Vérifier si la catégorie contient des produits
        ]);
    }

    #[Route('/{id}', name: 'categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): Response
    {
        // Vérifier si la catégorie a des produits associés
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        // Si la catégorie a des produits, afficher un message et ne pas supprimer
        if (count($produits) > 0) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette catégorie car elle contient des produits.');
            return $this->redirectToRoute('categorie_index');
        }

        // Si la catégorie n'a pas de produits, procéder à la suppression
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
