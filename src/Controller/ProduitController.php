<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/catalogue', name: 'produit_catalogue')]
    public function catalogue(Request $request, ProduitRepository $produitRepository)
    {
        $tri = $request->query->get('tri', 'nom'); // Par défaut, trier par nom
    
        // Récupérer les produits par catégorie
        $produitsParCategorie = $produitRepository->findAllGroupedByCategory();
    
        // Tri des produits dans chaque catégorie
        foreach ($produitsParCategorie as $categorie => &$produits) {
            if ($tri == 'prix') {
                usort($produits, function($a, $b) {
                    return $a->getPrix() <=> $b->getPrix();
                });
            } else {
                usort($produits, function($a, $b) {
                    return strcmp($a->getNom(), $b->getNom());
                });
            }
        }
    
        return $this->render('produit/catalogue.html.twig', [
            'produitsParCategorie' => $produitsParCategorie,
        ]);
    }


    #[Route('/produit/new', name: 'produit_new')]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérification des droits admin

    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Persist du produit avec sa catégorie
        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été créé avec succès.');

        return $this->redirectToRoute('produit_catalogue');
    }

    return $this->render('produit/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route('/produit/{id}/edit', name: 'produit_edit')]
public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérification des droits admin

    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été mis à jour avec succès.');

        return $this->redirectToRoute('produit_catalogue');
    }

    return $this->render('produit/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/produit/{id}/delete', name: 'produit_delete')]
public function delete(Produit $produit, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérification des droits admin

    $entityManager->remove($produit);
    $entityManager->flush();

    $this->addFlash('success', 'Le produit a été supprimé avec succès.');

    return $this->redirectToRoute('produit_catalogue');
}


}
