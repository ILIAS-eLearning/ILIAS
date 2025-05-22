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

final class OrderedJobEntities implements \ILIAS\Cron\Job\JobCollection
{
    final public const int ORDER_BY_NONE = 0;
    final public const int ORDER_BY_NAME = 1;
    final public const int ORDER_BY_STATUS = 2;

    final public const int ORDER_AS_PROVIDED = 1;
    final public const int ORDER_REVERSE = -1;

    /** @var int|callable(\ILIAS\Cron\Job\JobEntity, \ILIAS\Cron\Job\JobEntity): int|\Closure(\ILIAS\Cron\Job\JobEntity, \ILIAS\Cron\Job\JobEntity): int */
    private mixed $sort;
    /**
     * @var null|list<\ILIAS\Cron\Job\JobEntity> Cached sorted jobs
     */
    private ?array $sorted_jobs = null;

    /**
     * @param int|callable(\ILIAS\Cron\Job\JobEntity, \ILIAS\Cron\Job\JobEntity): int|\Closure(\ILIAS\Cron\Job\JobEntity, \ILIAS\Cron\Job\JobEntity): int $sort
     */
    public function __construct(
        private readonly \ILIAS\Cron\Job\JobCollection $origin,
        mixed $sort,
        bool $reverse_order = false
    ) {
        $order = $reverse_order ? self::ORDER_REVERSE : self::ORDER_AS_PROVIDED;

        if ($sort === self::ORDER_BY_NAME) {
            $this->sort = static function (\ILIAS\Cron\Job\JobEntity $left, \ILIAS\Cron\Job\JobEntity $right) use (
                $order
            ): int {
                return $order * \ilStr::strCmp($left->getEffectiveTitle(), $right->getEffectiveTitle());
            };
        } elseif ($sort === self::ORDER_BY_STATUS) {
            $this->sort = static function (\ILIAS\Cron\Job\JobEntity $left, \ILIAS\Cron\Job\JobEntity $right) use (
                $order
            ): int {
                return $order * ($right->getJobStatus() <=> $left->getJobStatus());
            };
        } elseif ($sort === self::ORDER_BY_NONE) {
            $this->sort = $order;
        } elseif (\is_callable($sort)) {
            $this->sort = $sort;
            if ($reverse_order) {
                $this->sort = static fn(
                    \ILIAS\Cron\Job\JobEntity $left,
                    \ILIAS\Cron\Job\JobEntity $right
                ): int => -$sort($left, $right);
            }
        } else {
            throw new \InvalidArgumentException(
                'The SortableIterator takes a PHP callable or a valid built-in sort algorithm as an argument.'
            );
        }
    }

    /**
     * @return \ArrayIterator<int, \ILIAS\Cron\Job\JobEntity>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getSortedJobs());
    }

    public function count(): int
    {
        return $this->origin->count();
    }

    public function add(\ILIAS\Cron\Job\JobEntity $job): void
    {
        $this->origin->add($job);
        $this->sorted_jobs = null;
    }

    public function filter(callable $callable): static
    {
        return new static(
            $this->origin->filter($callable),
            $this->sort
        );
    }

    public function slice(int $offset, ?int $length = null): static
    {
        return new static(
            new JobEntities(...\array_slice($this->getSortedJobs(), $offset, $length)),
            $this->sort
        );
    }

    public function toArray(): array
    {
        return $this->getSortedJobs();
    }

    /**
     * @return list<\ILIAS\Cron\Job\JobEntity>
     */
    private function getSortedJobs(): array
    {
        if ($this->sorted_jobs === null) {
            $list = iterator_to_array($this->origin->toArray(), false);
            if ($this->sort !== self::ORDER_AS_PROVIDED) {
                if ($this->sort === self::ORDER_REVERSE) {
                    $list = array_reverse($list);
                } else {
                    uasort($list, $this->sort);
                }
            }
            $this->sorted_jobs = $list;
        }

        return $this->sorted_jobs;
    }
}
