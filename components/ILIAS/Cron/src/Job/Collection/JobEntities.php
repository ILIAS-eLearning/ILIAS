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

namespace ILIAS\Cron\Job\Collection;

final readonly class JobEntities implements \ILIAS\Cron\Job\JobCollection
{
    /**
     * @var \ArrayIterator<int, \ILIAS\Cron\Job\JobEntity>
     */
    private \ArrayIterator $jobs;

    public function __construct(\ILIAS\Cron\Job\JobEntity ...$jobs)
    {
        $this->jobs = new \ArrayIterator(array_values($jobs));
    }

    /**
     * @return \ArrayIterator<int, \ILIAS\Cron\Job\JobEntity>
     */
    public function getIterator(): \ArrayIterator
    {
        return $this->jobs;
    }

    public function count(): int
    {
        return iterator_count($this);
    }

    public function add(\ILIAS\Cron\Job\JobEntity $job): void
    {
        $this->jobs->append($job);
    }

    public function filter(callable $callable): static
    {
        return new static(...array_values(array_filter(iterator_to_array($this), $callable)));
    }

    public function slice(int $offset, ?int $length = null): static
    {
        return new static(...\array_slice(iterator_to_array($this), $offset, $length, true));
    }

    /**
     * @return list<\ILIAS\Cron\Job\JobEntity>
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }
}
