<?php

namespace App\Repository;

use App\DTO\RechercheUtilisateur;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function chercheUtilisateurAvecFiltre(RechercheUtilisateur $filtres) {
        $queryBuilder = $this->createQueryBuilder('utilisateur')
            ->innerJoin('utilisateur.campus', 'campus')->addSelect('campus');

        if (isset($filtres->search) && $filtres->search) {
            $queryBuilder->andWhere( $queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('utilisateur.nom', ':search'),
                $queryBuilder->expr()->like('utilisateur.prenom', ':search'),
                $queryBuilder->expr()->like('utilisateur.pseudo', ':search')
            ))
                ->setParameter('search', '%'.$filtres->search.'%');
        }
        if (isset($filtres->campus) && $filtres->campus->getId()) {
            $queryBuilder->andWhere('utilisateur.campus = :campus')
                ->setParameter('campus', $filtres->campus->getId());
        }
        if ($filtres->estActif) {
            $queryBuilder->andWhere('utilisateur.estActif = :actif')
                ->setParameter('actif', true);
        }

        return $queryBuilder->getQuery()->getResult();
    }
    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
