<?php

namespace App\Repository;

use App\DTO\RechercheSortie;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    private $etatRepository;
    public function __construct(ManagerRegistry $registry, EtatRepository $etatRepository)
    {
        parent::__construct($registry, Sortie::class);
        $this->etatRepository = $etatRepository;
    }

    public function chercheSortiesNonHistorisees()
    {
        $etatHistorisee = $this->etatRepository->findByLibelle('Historisée');
        $etatId = $etatHistorisee->getId();
        return $this->createQueryBuilder('s')
            ->where('s.etat != :etatExclu')
            ->setParameter('etatExclu', $etatId)
            ->getQuery()
            ->getResult();
    }

    public function chercheSortieAvecFiltre(RechercheSortie $filtres, EtatRepository $etatRepository, UserInterface $utilisateur)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $etatHistorisee = $this->etatRepository->findByLibelle('Historisée');
        if ($etatHistorisee) {
            $queryBuilder->andWhere('s.etat != :etatHistorisee')
                ->setParameter('etatHistorisee', $etatHistorisee->getId());
        }

        if (isset($filtres->search) && $filtres->search) {
            $queryBuilder->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%'.$filtres->search.'%');
        }
        if (isset($filtres->campus) && $filtres->campus->getId()) {
            $queryBuilder->andWhere('s.campus = :campus')
                ->setParameter('campus', $filtres->campus->getId());
        }
        if ($filtres->estOrganisateur) {
            $queryBuilder->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $utilisateur->getId());
        }
        if ($filtres->estInscrit && !$filtres->estPasInscrit) {
            $queryBuilder->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $utilisateur->getId());
        } else if (!$filtres->estInscrit && $filtres->estPasInscrit) {
            $queryBuilder->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $utilisateur->getId());
        }
        if ($filtres->estTerminees) {
            $queryBuilder->andWhere('s.etat LIKE :terminees')
                ->setParameter('terminees', "Terminée");
        }
        if ($filtres->dateDebut && !$filtres->dateFin) {
            $queryBuilder->andWhere('s.dateHeureDebut > :dateDebut')
                ->setParameter('dateDebut', $filtres->dateDebut->format("Y-m-d H:i:s"));
        }
        if ($filtres->dateDebut && $filtres->dateFin) {
            $queryBuilder->andWhere('s.dateHeureDebut > :startDate')
                ->andWhere('s.dateHeureDebut < :endDate')
                ->setParameter('startDate', $filtres->dateDebut->format("Y-m-d H:i:s"))
                ->setParameter('endDate', $filtres->dateFin->format("Y-m-d H:i:s"));
        }
        if ($filtres->dateFin && !$filtres->dateDebut) {
            $queryBuilder->andWhere('DATE_ADD(s.dateHeureDebut, s.duree, \'minute\') < :dateFin')
                ->setParameter('dateFin', $filtres->dateFin->format("Y-m-d H:i:s"));
        }

        return $queryBuilder->getQuery()->getResult();
    }
    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult();
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult();
    //    }
}

