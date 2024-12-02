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
        // Construction de la requête pour récupérer les produits groupés par catégorie
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c')  // Assurez-vous que la relation 'categorie' existe
            ->addSelect('c')
            ->orderBy('c.nom', 'ASC')  // Tri par nom de catégorie
            ->addOrderBy('p.nom', 'ASC')  // Tri par nom de produit

            // Exécution de la requête
            ->getQuery();

        $result = $qb->getResult();

        // Organiser les produits par catégorie
        $produitsParCategorie = [];
        foreach ($result as $produit) {
            $produitsParCategorie[$produit->getCategorie()->getNom()][] = $produit;
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
