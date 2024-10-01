<?php

namespace App\Repository;

use App\DTO\RechercheUtilisateur;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UtilisateurRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }


    public function find(mixed $id, int|LockMode|null $lockMode = null, ?int $lockVersion = null): object|null
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->innerJoin('u.campus', 'campus')->addSelect('campus')
            ->getQuery()
            ->getOneOrNullResult();
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
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\Utilisateur u
                WHERE u.pseudo = :query
                OR u.email = :query'
        )
            ->setParameter('query', $identifier)
            ->getOneOrNullResult();
    }
}
