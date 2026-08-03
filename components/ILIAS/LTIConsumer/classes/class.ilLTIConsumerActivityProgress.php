<?php

declare(strict_types=1);

enum ilLTIConsumerActivityProgress: string
{
    case INITIALIZED = 'Initialized';
    case STARTED = 'Started';
    case IN_PROGRESS = 'InProgress';
    case SUBMITTED = 'Submitted';
    case COMPLETED = 'Completed';

    public function isInProgress(): bool
    {
        return $this === self::STARTED || $this === self::IN_PROGRESS;
    }

    public function isSubmittedOrCompleted(): bool
    {
        return $this === self::SUBMITTED || $this === self::COMPLETED;
    }
}
