<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
