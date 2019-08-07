<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Filesystem;

/**
 * Class SortableIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class SortableIterator implements \IteratorAggregate
{
    const SORT_BY_NONE = 0;
    const SORT_BY_NAME = 1;
    const SORT_BY_TYPE = 2;
    const SORT_BY_NAME_NATURAL = 4;
    const SORT_BY_TIME = 5;

    /** @var FileSystem */
    private $filesystem;

    /** @var \Traversable */
    private $iterator;

    /** @var callable|\Closure|int */
    private $sort;

    /**
     * Sortable constructor.
     * @param Filesystem $filesystem
     * @param \Traversable $iterator
     * @param int|callable|\Closure $sort
     * @param bool $reverseOrder
     */
    public function __construct(Filesystem $filesystem, \Traversable $iterator, $sort, $reverseOrder = false)
    {
        $this->filesystem = $filesystem;
        $this->iterator = $iterator;
        $order = $reverseOrder ? -1 : 1;

        if (self::SORT_BY_NAME === $sort) {
            $this->sort = function (Metadata $left, Metadata $right) use ($order) {
                $leftRealPath = $left->getPath();
                $rightRealPath = $right->getPath();

                return $order * strcmp($leftRealPath, $rightRealPath);
            };
        } elseif (self::SORT_BY_NAME_NATURAL === $sort) {
            $this->sort = function (Metadata $left, Metadata $right) use ($order) {
                $leftRealPath = $left->getPath();
                $rightRealPath = $right->getPath();

                return $order * strnatcmp($leftRealPath, $rightRealPath);
            };
        } elseif (self::SORT_BY_TYPE === $sort) {
            $this->sort = function (Metadata $left, Metadata $right) use ($order) {
                if ($left->isDir() && $right->isFile()) {
                    return -$order;
                } elseif ($left->isFile() && $right->isDir()) {
                    return $order;
                }

                $leftRealPath = $left->getPath();
                $rightRealPath = $right->getPath();

                return $order * strcmp($leftRealPath, $rightRealPath);
            };
        } elseif (self::SORT_BY_TIME === $sort) {
            $this->sort = function (Metadata $left, Metadata $right) use ($order) {
                $leftTimestamp = $this->filesystem->getTimestamp($left->getPath());
                $rightTimestamp = $this->filesystem->getTimestamp($right->getPath());

                return $order * ($leftTimestamp->getTimestamp() - $rightTimestamp->getTimestamp());
            };
        } elseif (self::SORT_BY_NONE === $sort) {
            $this->sort = $order;
        } elseif (is_callable($sort)) {
            $this->sort = $sort;
            if ($reverseOrder) {
                $this->sort = function (Metadata $left, Metadata $right) use ($sort) {
                    return -$sort($left, $right);
                };
            }
        } else {
            throw new \InvalidArgumentException('The SortableIterator takes a PHP callable or a valid built-in sort algorithm as an argument.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        if (1 === $this->sort) {
            return $this->iterator;
        }

        $array = iterator_to_array($this->iterator, true);
        if (-1 === $this->sort) {
            $array = array_reverse($array);
        } else {
            uasort($array, $this->sort);
        }

        return new \ArrayIterator($array);
    }
}