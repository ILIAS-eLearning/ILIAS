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

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilBackgroundTasksSetupConfig implements Setup\Config
{
    public const TYPE_SYNCHRONOUS = "sync";
    public const TYPE_ASYNCHRONOUS = "async";

    protected string $type;

    protected int $max_concurrent_tasks;

    public function __construct(
        string $type,
        int $max_concurrent_tasks
    ) {
        $types = [
            self::TYPE_SYNCHRONOUS,
            self::TYPE_ASYNCHRONOUS
        ];
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException(
                "Unknown background tasks type: '$type'"
            );
        }
        if ($max_concurrent_tasks < 1) {
            throw new \InvalidArgumentException(
                "There must be at least 1 concurrent background task."
            );
        }
        $this->type = $type;
        $this->max_concurrent_tasks = $max_concurrent_tasks;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMaxCurrentTasks(): int
    {
        return $this->max_concurrent_tasks;
    }
}
