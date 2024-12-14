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
    $triProduits = $request->query->get('tri', 'nom'); // Par défaut : trier par nom A-Z
    $triCategories = $request->query->get('tri_categories', 'categorie_asc'); // Par défaut : trier les catégories A-Z

    // Récupérer les produits groupés par catégorie
    $produitsParCategorie = $produitRepository->findAllGroupedByCategory();

    // Tri des catégories
    if (in_array($triCategories, ['categorie_asc', 'categorie_desc'])) {
        if ($triCategories == 'categorie_desc') {
            krsort($produitsParCategorie); // Trier par nom décroissant
        } else {
            ksort($produitsParCategorie); // Trier par nom croissant
        }
    } elseif (in_array($triCategories, ['produits_asc', 'produits_desc'])) {
        uasort($produitsParCategorie, function($a, $b) use ($triCategories) {
            $countA = count($a);
            $countB = count($b);
            if ($triCategories == 'produits_asc') {
                return $countA <=> $countB; // Nombre de produits croissant
            } else {
                return $countB <=> $countA; // Nombre de produits décroissant
            }
        });
    }

    // Tri des produits dans chaque catégorie
    foreach ($produitsParCategorie as $categorie => &$produits) {
        switch ($triProduits) {
            case 'nom_desc':
                usort($produits, function($a, $b) {
                    return strcmp($b->getNom(), $a->getNom()); // Nom Z-A
                });
                break;

            case 'prix':
                usort($produits, function($a, $b) {
                    return $a->getPrix() <=> $b->getPrix(); // Prix croissant
                });
                break;

            case 'prix_desc':
                usort($produits, function($a, $b) {
                    return $b->getPrix() <=> $a->getPrix(); // Prix décroissant
                });
                break;

            default:
                usort($produits, function($a, $b) {
                    return strcmp($a->getNom(), $b->getNom()); // Nom A-Z
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

#[Route('/produit/{id}/ajouter', name: 'produit_ajouter', methods: ['POST'])]
public function ajouterProduit(Produit $produit, Request $request, EntityManagerInterface $em): Response
{
    $quantite = (int) $request->request->get('quantite', 1); // Récupère la quantité à ajouter

    if ($quantite <= 0) {
        $this->addFlash('error', 'La quantité doit être supérieure à zéro.');
        return $this->redirectToRoute('produit_catalogue');
    }

    // Mise à jour de la quantité dans la base de données
    $produit->setStock($produit->getStock() + $quantite);
    $em->flush();

    $this->addFlash('success', 'Quantité ajoutée au stock.');
    return $this->redirectToRoute('panier');
}

#[Route('/produit/{id}/reduire', name: 'produit_reduire', methods: ['POST'])]
public function reduireProduit(Produit $produit, Request $request, EntityManagerInterface $em): Response
{
    $quantite = (int) $request->request->get('quantite', 1); // Récupère la quantité à réduire

    if ($quantite <= 0) {
        $this->addFlash('error', 'La quantité doit être supérieure à zéro.');
        return $this->redirectToRoute('produit_catalogue');
    }

    // Réduire la quantité dans la base de données
    if ($produit->getStock() >= $quantite) {
        $produit->setStock($produit->getStock() - $quantite);
        $em->flush();
        $this->addFlash('success', 'Quantité réduite du stock.');
    } else {
        $this->addFlash('error', 'Stock insuffisant.');
    }

    return $this->redirectToRoute('produit_catalogue');
}



}
