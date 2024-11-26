<?php
namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/api/produits', name: 'api_produits', methods: ['GET'])]
    public function getProduits(EntityManagerInterface $em): JsonResponse
    {
        // Récupérer tous les produits depuis la base de données
        $produits = $em->getRepository(Produit::class)->findAll();

        // Mapper les produits en un tableau associatif
        $produitsData = array_map(function($produit) {
            return [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
            ];
        }, $produits);

        // Retourner les produits sous forme de JSON
        return new JsonResponse($produitsData);
    }
}
