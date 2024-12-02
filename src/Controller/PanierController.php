<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier')]
public function afficherPanier(SessionInterface $session, ProduitRepository $produitRepository, Request $request)
{
    $panier = $session->get('panier', []);
    $dataPanier = [];
    $total = 0;

    // Récupérer le critère de tri
    $tri = $request->query->get('tri', 'nom'); // Par défaut, tri par nom

    foreach ($panier as $id => $quantite) {
        $produit = $produitRepository->find($id);
        $dataPanier[] = [
            'produit' => $produit,
            'quantite' => $quantite
        ];
        $total += $produit->getPrix() * $quantite;
    }

    // Appliquer le tri
    usort($dataPanier, function ($a, $b) use ($tri) {
        if ($tri === 'prix') {
            return $a['produit']->getPrix() <=> $b['produit']->getPrix();
        }
        return strcmp($a['produit']->getNom(), $b['produit']->getNom());
    });

    return $this->render('panier/index.html.twig', [
        'dataPanier' => $dataPanier,
        'total' => $total,
        'tri' => $tri // Passer la valeur du tri à la vue
    ]);
}


    // Pour augmenter la quantité
#[Route('/panier/ajouterviapanier/{id}', name: 'panier_ajouter-via-panier')]
public function ajouterProduitviaPanier(Produit $produit, SessionInterface $session)
{
    $panier = $session->get('panier', []);
    $id = $produit->getId();

    if (isset($panier[$id])) {
        $panier[$id]++;
    } else {
        $panier[$id] = 1;
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('panier');
}

#[Route('/panier/ajouter/{id}', name: 'panier_ajouter', methods: ['POST'])]
public function ajouterProduit(Produit $produit, SessionInterface $session, Request $request)
{
    $panier = $session->get('panier', []);
    $id = $produit->getId();
    $quantite = (int) $request->request->get('quantite', 1);  // Récupère la quantité du formulaire

    if (isset($panier[$id])) {
        $panier[$id] += $quantite;
    } else {
        $panier[$id] = $quantite;
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('produit_catalogue');
}


// Pour réduire la quantité
#[Route('/panier/reduire/{id}', name: 'panier_reduire')]
public function reduireProduit(Produit $produit, SessionInterface $session)
{
    $panier = $session->get('panier', []);
    $id = $produit->getId();

    if (isset($panier[$id])) {
        if ($panier[$id] > 1) {
            $panier[$id]--;
        } else {
            unset($panier[$id]);
        }
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('panier');
}


#[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
public function supprimerProduit(Produit $produit, SessionInterface $session)
{
    $panier = $session->get('panier', []);
    $id = $produit->getId();

    // Supprime tout le produit du panier
    if (isset($panier[$id])) {
        unset($panier[$id]);
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('panier');
}


    #[Route('/panier/supprimer-panier', name: 'panier_supprimer_panier')]
    public function supprimerTout(SessionInterface $session)
    {
        $session->remove('panier');
        
        return $this->redirectToRoute('panier');
    }

    #[Route('/panier/quantite', name: 'panier_quantite', methods: ['GET'])]
public function quantitePanier(SessionInterface $session)
{
    $panier = $session->get('panier', []);
    $quantiteTotale = array_sum($panier);

    return $this->json(['quantite' => $quantiteTotale]);
}

}
