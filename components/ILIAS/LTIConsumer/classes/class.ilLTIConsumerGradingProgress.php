<?php

declare(strict_types=1);

enum ilLTIConsumerGradingProgress: string
{
    case FULLY_GRADED = 'FullyGraded';
    case PENDING = 'Pending';
    case PENDING_MANUAL = 'PendingManual';
    case FAILED = 'Failed';
    case NOT_READY = 'NotReady';

    public function isPending(): bool
    {
        return $this !== self::FULLY_GRADED;
    }
}
