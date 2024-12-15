<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
   
    #[Route('/login', name: 'app_login')]
    
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si l'utilisateur est déjà connecté, et redirige vers la page cible si c'est le cas
        if ($this->getUser()) {
            return $this->redirectToRoute('target_path'); // Remplacez 'target_path' par la route de votre choix
        }

        // Récupère l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Récupère le dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Renvoie la vue de la page de connexion avec les variables nécessaires
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error, // Si une erreur s'est produite, elle sera affichée ici
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode est interceptée par le mécanisme de déconnexion de Symfony
        throw new \LogicException('Cette méthode peut rester vide, elle sera interceptée par la clé logout de votre pare-feu.');
    }
}
