<?php

namespace App\MessageHandler;

use App\Message\ArchiveSortiesMessage;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class ArchiveSortiesMessageHandler
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
        private string $archiveTime = '23:30:00'
    ) {
    }

    public function __invoke(ArchiveSortiesMessage $message): void
    {
        $this->logger->info('Début de l\'archivage automatique des sorties');

        $dateLimit = new \DateTime('-1 month');

        $sorties = $this->sortieRepository->createQueryBuilder('s')
            ->where('s.dateHeureDebut < :dateLimit')
            ->andWhere('s.archivee = false')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($sorties as $sortie) {
            $sortie->setArchivee(true);
            $count++;
        }

        if ($count > 0) {
            $this->em->flush();
        }

        $this->logger->info(sprintf('%d sortie(s) archivée(s) avec succès', $count));

        // AUTO-REPLANIFICATION : Planifier le prochain archivage pour demain à 23h30
        $this->scheduleNextArchive();
    }

    private function scheduleNextArchive(): void
    {
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $scheduledTime = new \DateTime('tomorrow ' . $this->archiveTime, new \DateTimeZone('Europe/Paris'));

        $delay = $scheduledTime->getTimestamp() - $now->getTimestamp();
        $delayInMilliseconds = $delay * 1000;

        $message = new ArchiveSortiesMessage($scheduledTime);
        $this->messageBus->dispatch($message, [
            new DelayStamp($delayInMilliseconds)
        ]);

        $this->logger->info(sprintf(
            'Prochain archivage planifié pour %s',
            $scheduledTime->format('d/m/Y à H:i:s')
        ));
    }
}
