<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/admin/acceuil', name: 'admin_acceuil')]
    public function adminAcceuil(): Response
    {
        return $this->render('user/admin/accueil.html.twig');
    }

    #[Route('/client/acceuil', name: 'client_acceuil')]
    public function clientAcceuil(): Response
    {
        return $this->render('user/client/accueil.html.twig');
    }
}
