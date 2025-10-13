<?php

namespace App\EventListener;

use App\Message\ArchiveSortiesMessage;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Psr\Log\LoggerInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 1000)]
class ArchiveSchedulerListener
{
    private bool $initialized = false;

    public function __construct(
        private MessageBusInterface $messageBus,
        private Connection $connection,
        private LoggerInterface $logger,
        private string $archiveTime = '23:30:00'
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        // Ne s'exécute qu'une seule fois par démarrage de l'application
        if ($this->initialized || !$event->isMainRequest()) {
            return;
        }

        $this->initialized = true;

        // Vérifier si un message d'archivage est déjà planifié
        if ($this->hasScheduledArchive()) {
            return;
        }

        // Planifier le premier archivage automatiquement
        $this->scheduleFirstArchive();
    }

    private function hasScheduledArchive(): bool
    {
        try {
            $result = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM messenger_messages WHERE queue_name = 'default' AND delivered_at IS NULL AND body LIKE '%ArchiveSortiesMessage%'"
            );

            return $result > 0;
        } catch (\Exception $e) {
            // Si la table n'existe pas encore, on retourne false
            return false;
        }
    }

    private function scheduleFirstArchive(): void
    {
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $scheduledTime = new \DateTime('today ' . $this->archiveTime, new \DateTimeZone('Europe/Paris'));

        // Si on est déjà passé à l'heure prévue, planifier pour demain
        if ($now > $scheduledTime) {
            $scheduledTime->modify('+1 day');
        }

        $delay = $scheduledTime->getTimestamp() - $now->getTimestamp();
        $delayInMilliseconds = $delay * 1000;

        $message = new ArchiveSortiesMessage($scheduledTime);
        $this->messageBus->dispatch($message, [
            new DelayStamp($delayInMilliseconds)
        ]);

        $this->logger->info(sprintf(
            '[AUTO-INIT] Premier archivage planifié automatiquement pour %s',
            $scheduledTime->format('d/m/Y à H:i:s')
        ));
    }
}
