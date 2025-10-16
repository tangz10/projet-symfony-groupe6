<?php

namespace App\EventListener;

use App\Message\RefreshSortiesStateMessage;
use App\Service\SortieStateRefresher;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Contracts\Cache\CacheInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 900)]
class StatusRefreshSchedulerListener
{
    private bool $initialized = false;

    public function __construct(
        private MessageBusInterface $messageBus,
        private Connection $connection,
        private LoggerInterface $logger,
        private SortieStateRefresher $refresher,
        private CacheInterface $cache,
        private int $intervalMinutes = 5,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($this->initialized || !$event->isMainRequest()) {
            return;
        }
        $this->initialized = true;

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        // Fallback: exécuter le refresh immédiatement puis mémoriser un TTL pour relancer au bout de X minutes
        $cacheKey = 'state_refresh_tick';
        $this->cache->get($cacheKey, function ($item) {
            $item->expiresAfter($this->intervalMinutes * 60);
            $count = $this->refresher->refreshAllDue();
            $this->logger->info(sprintf('[FALLBACK][STATE] Rafraîchissement effectué (%d maj)', $count));
            return true;
        });

        // Si un refresh est déjà planifié en file, ne rien replanifier
        if ($this->hasScheduledRefresh()) {
            return;
        }

        $this->scheduleNextRefreshFrom($now);
    }

    private function hasScheduledRefresh(): bool
    {
        try {
            $result = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM messenger_messages WHERE queue_name = 'default' AND delivered_at IS NULL AND body LIKE '%RefreshSortiesStateMessage%'"
            );
            return (int)$result > 0;
        } catch (\Throwable $e) {
            // Si la table n'existe pas encore
            return false;
        }
    }

    private function scheduleNextRefreshFrom(\DateTimeInterface $now): void
    {
        $delaySeconds = max(1, $this->intervalMinutes * 60);
        $runAt = (new \DateTimeImmutable('@'.($now->getTimestamp() + $delaySeconds)))->setTimezone(new \DateTimeZone('Europe/Paris'));
        $this->messageBus->dispatch(new RefreshSortiesStateMessage(), [
            new DelayStamp($delaySeconds * 1000)
        ]);
        $this->logger->info(sprintf('[AUTO-INIT][STATE] Refresh planifié pour %s (+%d min)', $runAt->format('d/m/Y H:i:s'), $this->intervalMinutes));
    }
}

