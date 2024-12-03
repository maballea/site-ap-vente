<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findAllGroupedByCategory()
{
    // Accéder à l'EntityManager via getEntityManager()
    $qb = $this->getEntityManager()->createQueryBuilder()
        ->select('c, p')
        ->from('App\Entity\Categorie', 'c') // Requête sur la table des catégories
        ->leftJoin('c.produits', 'p')       // Relation avec les produits
        ->orderBy('c.nom', 'ASC')          // Trier par nom de catégorie
        ->addOrderBy('p.nom', 'ASC');      // Trier les produits par nom

    $result = $qb->getQuery()->getResult();

    // Organiser les produits par catégorie
    $produitsParCategorie = [];
    foreach ($result as $categorie) {
        $produitsParCategorie[$categorie->getNom()] = $categorie->getProduits()->toArray();
    }

    return $produitsParCategorie;
}



    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
