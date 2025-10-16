<?php

namespace App\MessageHandler;

use App\Message\RefreshSortiesStateMessage;
use App\Service\SortieStateRefresher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RefreshSortiesStateMessageHandler
{
    public function __construct(private SortieStateRefresher $refresher) {}

    public function __invoke(RefreshSortiesStateMessage $message): void
    {
        $this->refresher->refreshAllDue();
    }
}

