<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findForListing(array $filters, ?\App\Entity\Participant $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.site', 'site')->addSelect('site')
            ->leftJoin('s.etat', 'etat')->addSelect('etat')
            ->leftJoin('s.participantOrganisateur', 'org')->addSelect('org')
            ->leftJoin('s.participantInscrit', 'inscrits')->addSelect('inscrits')
            ->leftJoin('s.lieu', 'lieu')->addSelect('lieu')
            ->andWhere('s.archivee = false')
            ->orderBy('s.dateHeureDebut', 'ASC');

        if (!empty($filters['site'])) {
            $qb->andWhere('s.site = :site')->setParameter('site', $filters['site']);
        }
        if (!empty($filters['q'])) {
            $qb->andWhere('LOWER(s.nom) LIKE :q')->setParameter('q', '%'.mb_strtolower($filters['q']).'%');
        }
        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('s.dateHeureDebut >= :from')->setParameter('from', $filters['dateFrom']);
        }
        if (!empty($filters['dateTo'])) {
            $to = (clone $filters['dateTo'])->setTime(23,59,59);
            $qb->andWhere('s.dateHeureDebut <= :to')->setParameter('to', $to);
        }

        if ($user) {
            if (!empty($filters['isOrganisateur'])) {
                $qb->andWhere('s.participantOrganisateur = :me')->setParameter('me', $user);
            }
            if (!empty($filters['isInscrit'])) {
                $qb->andWhere(':me MEMBER OF s.participantInscrit')->setParameter('me', $user);
            }
            if (!empty($filters['isNotInscrit'])) {
                $qb->andWhere(':me NOT MEMBER OF s.participantInscrit')->setParameter('me', $user);
            }
        }

        if (empty($filters['isPast'])) {
            $qb->andWhere('s.dateHeureDebut >= :today')->setParameter('today', (new \DateTime())->setTime(0,0));
        }

        return $qb->getQuery()->getResult();
    }

    public function findNonArchivees(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.archivee = false')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();
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
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
