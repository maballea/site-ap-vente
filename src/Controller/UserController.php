<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    /**
     * Route pour la page d'accueil de l'utilisateur
     * 
     * @Route("/", name="app_user")
     */
    public function index(): Response
    {
        // Rend la vue 'user/index.html.twig' avec une variable 'controller_name'
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController', // Variable utilisée dans la vue
        ]);
    }

    /**
     * Route pour la page d'accueil de l'administrateur
     * 
     * @Route("/admin/acceuil", name="admin_acceuil")
     */
    public function adminAcceuil(): Response
    {
        // Rend la vue 'user/admin/accueil.html.twig' pour l'administrateur
        return $this->render('user/admin/accueil.html.twig');
    }

    /**
     * Route pour la page d'accueil du client
     * 
     * @Route("/client/acceuil", name="client_acceuil")
     */
    public function clientAcceuil(EntityManagerInterface $em): Response
    {
        // Requête pour récupérer les 6 produits les plus vendus
        $produitsEnVogue = $em->createQuery(
            'SELECT p, SUM(dc.quantite) as totalVentes
             FROM App\Entity\Produit p
             JOIN App\Entity\DetailsCommande dc WITH p.id = dc.produit
             GROUP BY p.id
             ORDER BY totalVentes DESC'
        )
        // Limite les résultats à 6 produits
        ->setMaxResults(6)
        // Exécute la requête et récupère les résultats
        ->getResult();

        // Rend la vue 'user/client/accueil.html.twig' et passe les produits en vogue
        return $this->render('user/client/accueil.html.twig', [
            'produitsEnVogue' => $produitsEnVogue, // Variable utilisée dans la vue
        ]);
    }
}
