<?php

namespace App\MessageHandler;

use App\Message\RefreshOneSortieStateMessage;
use App\Repository\SortieRepository;
use App\Service\SortieStateResolver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RefreshOneSortieStateMessageHandler
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private SortieStateResolver $resolver,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(RefreshOneSortieStateMessage $message): void
    {
        $id = $message->getSortieId();
        $s = $this->sortieRepository->find($id);
        if (!$s) {
            $this->logger->warning('[STATE] Sortie introuvable pour refresh', ['id' => $id]);
            return;
        }
        $changed = $this->resolver->resolveAndApply($s, new \DateTimeImmutable('now'));
        if ($changed) {
            $this->em->flush();
            $this->logger->info(sprintf('[STATE] Sortie #%d: état mis à jour → %s', $s->getId(), $s->getEtat()?->getLibelle() ?? 'null'));
        }
    }
}
