<?php

namespace App\Message;

class ArchiveSortiesMessage
{
    public function __construct(
        private readonly \DateTimeInterface $executedAt
    ) {
    }

    public function getExecutedAt(): \DateTimeInterface
    {
        return $this->executedAt;
    }
}

