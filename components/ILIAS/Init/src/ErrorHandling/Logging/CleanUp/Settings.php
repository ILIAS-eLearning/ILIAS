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

namespace ILIAS\Init\ErrorHandling\Logging\CleanUp;

use ilSetting;

class Settings
{
    private const int DEFAULT_CUTOFF = 31;

    private int $deletion_cutoff_in_days;

    public function __construct(
        private readonly ilSetting $storage
    ) {
    }

    public function deletionCutoffInDays(): int
    {
        return $this->deletion_cutoff_in_days ??=
            (int) $this->storage->get('clear_older_then', (string) self::DEFAULT_CUTOFF);
    }

    public function saveDeletionCutoff(int $days): void
    {
        if ($days < 1) {
            $days = 1;
        }
        $this->deletion_cutoff_in_days = $days;
        $this->storage->set('clear_older_then', (string) $days);
    }
}
