<?php

namespace App\Service;

use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SortieStateRefresher
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private SortieStateResolver $resolver,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {}

    /**
     * Rafraîchit les états des sorties non archivées. Retourne le nombre de sorties mises à jour.
     */
    public function refreshAllDue(): int
    {
        $now = new \DateTimeImmutable('now');
        $toCheck = $this->sortieRepository->findNonArchivees();
        $changed = 0;
        foreach ($toCheck as $s) {
            if ($this->resolver->resolveAndApply($s, $now)) {
                $changed++;
            }
        }
        if ($changed > 0) {
            $this->em->flush();
        }
        $this->logger->info(sprintf('[STATE] Rafraîchissement états: %d modifié(s), %d vérifié(s)', $changed, \count($toCheck)));
        return $changed;
    }
}

