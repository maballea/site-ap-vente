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
    // Route pour afficher le catalogue des produits
    #[Route('/catalogue', name: 'produit_catalogue')]
    public function catalogue(Request $request, ProduitRepository $produitRepository)
    {
        // Récupération du critère de tri pour les produits et les catégories
        $triProduits = $request->query->get('tri', 'nom'); // Par défaut : trier par nom A-Z
        $triCategories = $request->query->get('tri_categories', 'categorie_asc'); // Par défaut : trier les catégories A-Z

        // Récupérer les produits groupés par catégorie
        $produitsParCategorie = $produitRepository->findAllGroupedByCategory();

        // Tri des catégories (A-Z ou Z-A)
        if (in_array($triCategories, ['categorie_asc', 'categorie_desc'])) {
            if ($triCategories == 'categorie_desc') {
                krsort($produitsParCategorie); // Trier par nom décroissant
            } else {
                ksort($produitsParCategorie); // Trier par nom croissant
            }
        } elseif (in_array($triCategories, ['produits_asc', 'produits_desc'])) {
            // Tri des catégories par nombre de produits
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

        // Rendu de la vue avec les produits triés par catégorie
        return $this->render('produit/catalogue.html.twig', [
            'produitsParCategorie' => $produitsParCategorie,
        ]);
    }

    // Route pour ajouter un nouveau produit
    #[Route('/produit/new', name: 'produit_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérification des droits d'accès (admin uniquement)
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérification des droits admin

        // Création du produit et du formulaire associé
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, on persiste le produit
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            // Message de succès et redirection vers le catalogue
            $this->addFlash('success', 'Le produit a été créé avec succès.');
            return $this->redirectToRoute('produit_catalogue');
        }

        // Rendu de la vue du formulaire de création de produit
        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour éditer un produit existant
    #[Route('/produit/{id}/edit', name: 'produit_edit')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        // Vérification des droits d'accès (admin uniquement)
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérification des droits admin

        // Création du formulaire de modification du produit
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, on met à jour le produit
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Message de succès et redirection vers le catalogue
            $this->addFlash('success', 'Le produit a été mis à jour avec succès.');
            return $this->redirectToRoute('produit_catalogue');
        }

        // Rendu de la vue du formulaire d'édition de produit
        return $this->render('produit/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour supprimer un produit
    #[Route('/produit/{id}/delete', name: 'produit_delete')]
    public function delete(Produit $produit, EntityManagerInterface $entityManager): Response
    {
        // Vérification des droits d'accès (admin uniquement)
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Vérification des droits admin

        // Suppression du produit et flush de l'entité
        $entityManager->remove($produit);
        $entityManager->flush();

        // Message de succès et redirection vers le catalogue
        $this->addFlash('success', 'Le produit a été supprimé avec succès.');
        return $this->redirectToRoute('produit_catalogue');
    }

    // Route pour ajouter une quantité au stock d'un produit
    #[Route('/produit/{id}/ajouter', name: 'produit_ajouter', methods: ['POST'])]
    public function ajouterProduit(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        // Récupération de la quantité à ajouter
        $quantite = (int) $request->request->get('quantite', 1);

        // Vérification que la quantité est valide
        if ($quantite <= 0) {
            $this->addFlash('error', 'La quantité doit être supérieure à zéro.');
            return $this->redirectToRoute('produit_catalogue');
        }

        // Mise à jour du stock du produit
        $produit->setStock($produit->getStock() + $quantite);
        $em->flush();

        // Message de succès et redirection vers le panier
        $this->addFlash('success', 'Quantité ajoutée au stock.');
        return $this->redirectToRoute('panier');
    }

    // Route pour réduire la quantité d'un produit en stock
    #[Route('/produit/{id}/reduire', name: 'produit_reduire', methods: ['POST'])]
    public function reduireProduit(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        // Récupération de la quantité à réduire
        $quantite = (int) $request->request->get('quantite', 1);

        // Vérification que la quantité est valide
        if ($quantite <= 0) {
            $this->addFlash('error', 'La quantité doit être supérieure à zéro.');
            return $this->redirectToRoute('produit_catalogue');
        }

        // Réduction du stock du produit si la quantité est disponible
        if ($produit->getStock() >= $quantite) {
            $produit->setStock($produit->getStock() - $quantite);
            $em->flush();
            $this->addFlash('success', 'Quantité réduite du stock.');
        } else {
            $this->addFlash('error', 'Stock insuffisant.');
        }

        // Redirection vers le catalogue des produits
        return $this->redirectToRoute('produit_catalogue');
    }
}
