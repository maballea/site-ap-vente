<?php

namespace App\Controller;

use App\Repository\ProduitRepository; // Assurez-vous d'avoir un repository pour les produits
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(ProduitRepository $produitRepository): Response
    {
        // Récupérer tous les produits depuis la base de données
        $produits = $produitRepository->findAll();

        return $this->render('home/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
