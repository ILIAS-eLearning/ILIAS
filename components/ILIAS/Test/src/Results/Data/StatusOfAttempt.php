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

namespace ILIAS\Test\Results\Data;

enum StatusOfAttempt: string
{
    case NOT_YET_STARTED = 'not_started';
    case RUNNING = 'running';
    case FINISHED_BY_UNKNOWN = 'finished_by_unknown';
    case FINISHED_BY_ADMINISTRATOR = 'finished_by_administrator';
    case FINISHED_BY_DURATION = 'finished_by_duration';
    case FINISHED_BY_PARTICIPANT = 'finished_by_participant';
    case FINISHED_BY_CRONJOB = 'finished_by_cronjob';

    public function isFinished(): bool
    {
        return in_array($this, [
            self::FINISHED_BY_UNKNOWN,
            self::FINISHED_BY_ADMINISTRATOR,
            self::FINISHED_BY_CRONJOB,
            self::FINISHED_BY_DURATION,
            self::FINISHED_BY_PARTICIPANT,
        ]);
    }
}
