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

namespace ILIAS\Cron;

final class InMemoryCronJobRegistry implements CronJobRegistry
{
    /**
     * @param list<CronJob> $jobs
     */
    public function __construct(
        private readonly array $jobs
    ) {
        $seen = [];
        foreach ($this->jobs as $job) {
            $id = $job->getId();

            if ($id === '') {
                throw new \InvalidArgumentException('Cron job id must not be empty.');
            }

            if (isset($seen[$id])) {
                throw new \LogicException('Duplicate cron job id contributed: ' . $id);
            }

            $seen[$id] = true;
        }
    }

    public function getAllJobs(): array
    {
        return $this->jobs;
    }
}
