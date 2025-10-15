<?php

namespace App\Message;

final class RefreshOneSortieStateMessage
{
    public function __construct(private int $sortieId)
    {
    }

    public function getSortieId(): int
    {
        return $this->sortieId;
    }
}

