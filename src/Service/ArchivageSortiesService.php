<?php

namespace App\Service;

use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ArchivageSortiesService
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Archive les sorties dont la date_heure_debut est antérieure à J-1 mois
     * et dont le champ archivee = false. Retourne le nombre d’éléments modifiés.
     */
    public function archiveOldSorties(): int
    {
        $dateLimit = new \DateTime('-1 month');

        $sorties = $this->sortieRepository->createQueryBuilder('s')
            ->where('s.dateHeureDebut < :dateLimit')
            ->andWhere('s.archivee = false')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getResult();

        $ids = [];
        foreach ($sorties as $sortie) {
            $sortie->setArchivee(true);
            $ids[] = $sortie->getId();
        }

        if (count($ids) > 0) {
            $this->em->flush();
            foreach ($ids as $id) {
                // Log lisible qui ressemble à une requête SQL UPDATE
                $this->logger->info(sprintf('UPDATE sortie SET archivee = 1 WHERE id = %d', $id));
            }
            $this->logger->info(sprintf('%d sortie(s) archivée(s) (service).', count($ids)));
        } else {
            $this->logger->info('Aucune sortie à archiver (service).');
        }

        return count($ids);
    }
}
