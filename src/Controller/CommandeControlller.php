<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commande/{id}', name: 'commande_show')]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/{id}/valider', name: 'commande_valider', methods: ['POST'])]
    public function valider(Commande $commande, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Change the status of the order
        $commande->setStatus('Validée');
        $entityManager->flush();

        // Add a flash message and redirect to the same page
        $this->addFlash('success', 'Commande validée avec succès!');
        return $this->redirectToRoute('commande_show', ['id' => $commande->getId()]);
    }
}
