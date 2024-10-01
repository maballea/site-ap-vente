<?php

namespace App\Controller;

use App\Entity\Client; // ou Administrateur
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//TEST

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Créer une instance de l'utilisateur
        $user = new Client(); // ou new Administrateur() si nécessaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe avant de le stocker
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getMdp() // Récupère le mot de passe saisi
            );

            $user->setMdp($hashedPassword); // Stocke le mot de passe hashé
            $user->setStatut('c'); // Définit le statut comme client

            // Persist l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home', ['message' => 'Inscription réussie !']);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
