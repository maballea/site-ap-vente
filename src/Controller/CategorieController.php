<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categorie/new', name: 'categorie_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // S'assurer que l'utilisateur est admin

        $categorie = new Categorie(); // Créer une nouvelle instance de la catégorie
        $form = $this->createForm(CategorieType::class, $categorie); // Créer le formulaire pour la catégorie
        $form->handleRequest($request); // Traiter la requête

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est soumis et valide, persister la catégorie
            $entityManager->persist($categorie);
            $entityManager->flush(); // Sauvegarder dans la base de données

            // Rediriger vers une autre page
            return $this->redirectToRoute('produit_catalogue');
        }

        // Rendre la vue du formulaire
        return $this->render('categorie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
