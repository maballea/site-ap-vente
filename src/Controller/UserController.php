<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]

    public function index(EntityManagerInterface $a): Response
{
    // Récupére les 3 produits les plus vendus
    $produitsEnVogue = $a->createQuery(
        'SELECT p, SUM(dc.quantite) as totalVentes
         FROM App\Entity\Produit p
         JOIN App\Entity\DetailsCommande dc WITH p.id = dc.produit
         GROUP BY p.id
         ORDER BY totalVentes DESC'
    )
    // Max 3 produits
    ->setMaxResults(3)
    ->getResult();

    
    return $this->render('user/index.html.twig', [
        'controller_name' => 'UserController',
        'produitsEnVogue' => $produitsEnVogue, // Variable utilisée dans la vue
    ]);
}


    
    #[Route('/admin/acceuil', name: 'admin_acceuil')]

    public function adminAcceuil(EntityManagerInterface $b): Response
    {

        // Récupére les 9 produits les plus vendus
        $produitsEnVogue = $b->createQuery(
            'SELECT p, SUM(dc.quantite) as totalVentes
             FROM App\Entity\Produit p
             JOIN App\Entity\DetailsCommande dc WITH p.id = dc.produit
             GROUP BY p.id
             ORDER BY totalVentes DESC'
        )
        // Max 9 produits
        ->setMaxResults(9)
        ->getResult();

        return $this->render('user/admin/accueil.html.twig',[
            'produitsEnVogue' => $produitsEnVogue, // Variable utilisée dans la vue
        ]);
    }

   
    #[Route('/client/acceuil', name: 'client_acceuil')]

    public function clientAcceuil(EntityManagerInterface $c): Response
    {
        // Récupére les 6 produits les plus vendus
        $produitsEnVogue = $c->createQuery(
            'SELECT p, SUM(dc.quantite) as totalVentes
             FROM App\Entity\Produit p
             JOIN App\Entity\DetailsCommande dc WITH p.id = dc.produit
             GROUP BY p.id
             ORDER BY totalVentes DESC'
        )
        // Max 6 produits
        ->setMaxResults(6)
        ->getResult();

        return $this->render('user/client/accueil.html.twig', [
            'produitsEnVogue' => $produitsEnVogue, // Variable utilisée dans la vue
        ]);
    }
}
