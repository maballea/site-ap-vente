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
    public function catalogue(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
        return $this->render('produit/catalogue.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produit/new', name: 'produit_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();
            return $this->redirectToRoute('produit_catalogue');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/{id}/edit', name: 'produit_edit')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('produit_catalogue');
        }

        return $this->render('produit/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
