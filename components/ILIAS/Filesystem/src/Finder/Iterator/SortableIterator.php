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

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Filesystem;

/**
 * @implements \IteratorAggregate<non-empty-string, Metadata>
 */
class SortableIterator implements \IteratorAggregate
{
    public const SORT_BY_NONE = 0;
    public const SORT_BY_NAME = 1;
    public const SORT_BY_TYPE = 2;
    public const SORT_BY_NAME_NATURAL = 4;
    public const SORT_BY_TIME = 5;

    /** @var callable(Metadata, Metadata): int|Closure(Metadata, Metadata): int|int */
    private $sort;

    /**
     * @param \Traversable<non-empty-string, Metadata>                               $iterator
     * @param int|callable(Metadata, Metadata): int|Closure(Metadata, Metadata): int $sort
     * @param bool                                                                   $reverseOrder
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly \Traversable $iterator,
        $sort,
        bool $reverseOrder = false
    ) {
        $order = $reverseOrder ? -1 : 1;

        if (self::SORT_BY_NAME === $sort) {
            $this->sort = static function (Metadata $left, Metadata $right) use ($order): int {
                $leftRealPath = $left->getPath();
                $rightRealPath = $right->getPath();

                return $order * strcmp($leftRealPath, $rightRealPath);
            };
        } elseif (self::SORT_BY_NAME_NATURAL === $sort) {
            $this->sort = static function (Metadata $left, Metadata $right) use ($order): int {
                $leftRealPath = $left->getPath();
                $rightRealPath = $right->getPath();

                return $order * strnatcmp($leftRealPath, $rightRealPath);
            };
        } elseif (self::SORT_BY_TYPE === $sort) {
            $this->sort = static function (Metadata $left, Metadata $right) use ($order): int {
                if ($left->isDir() && $right->isFile()) {
                    return -$order;
                }
                if ($left->isFile() && $right->isDir()) {
                    return $order;
                }

                $leftRealPath = $left->getPath();
                $rightRealPath = $right->getPath();

                return $order * strcmp($leftRealPath, $rightRealPath);
            };
        } elseif (self::SORT_BY_TIME === $sort) {
            $this->sort = function (Metadata $left, Metadata $right) use ($order): int {
                $leftTimestamp = $this->filesystem->getTimestamp($left->getPath());
                $rightTimestamp = $this->filesystem->getTimestamp($right->getPath());

                return $order * ($leftTimestamp->getTimestamp() - $rightTimestamp->getTimestamp());
            };
        } elseif (self::SORT_BY_NONE === $sort) {
            $this->sort = $order;
        } elseif (\is_callable($sort)) {
            $this->sort = $sort;
            if ($reverseOrder) {
                $this->sort = static fn(Metadata $left, Metadata $right): int|float => -$sort($left, $right);
            }
        } else {
            throw new \InvalidArgumentException(
                'The SortableIterator takes a PHP callable or a valid built-in sort algorithm as an argument.'
            );
        }
    }

    public function getIterator(): \Traversable
    {
        if (1 === $this->sort) {
            yield from $this->iterator;
            return;
        }

        $keys = [];
        $values = [];
        foreach ($this->iterator as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        if (-1 === $this->sort) {
            for ($i = \count($values) - 1; $i >= 0; --$i) {
                yield $keys[$i] => $values[$i];
            }
            return;
        }

        uasort($values, $this->sort);

        foreach ($values as $i => $v) {
            yield $keys[$i] => $v;
        }
    }
}
