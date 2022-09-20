<?php

declare(strict_types=1);

namespace ILIAS\Filesystem\Finder;

use AppendIterator;
use ArrayIterator;
use Closure;
use Countable;
use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\MetadataType;
use InvalidArgumentException;
use Iterator as PhpIterator;
use IteratorAggregate;
use LogicException;
use RecursiveIteratorIterator;
use ReturnTypeWillChange;
use Traversable;
use ILIAS\Filesystem\Finder\Iterator\SortableIterator;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class Finder
 * Port of the Symfony2 bundle to work with the ILIAS FileSystem abstraction
 * @package ILIAS\Filesystem\Finder
 * @see     : https://github.com/symfony/finder
 * @author  Michael Jansen <mjansen@databay.de>
 */
final class Finder implements IteratorAggregate, Countable
{
    private const IGNORE_VCS_FILES = 1;
    private const IGNORE_DOT_FILES = 2;

    private \ILIAS\Filesystem\Filesystem $filesystem;
    /** @var string[] */
    private array $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];
    /** @var PhpIterator[] */
    private array $iterators = [];
    /** @var string[] */
    protected array $dirs = [];
    /** @var string[] */
    private array $exclude = [];
    private int $ignore = 0;
    private int $mode = Iterator\FileTypeFilterIterator::ALL;
    private bool $reverseSorting = false;
    /** @var Comparator\DateComparator[] */
    private array $dates = [];
    /** @var Comparator\NumberComparator[] */
    private array $sizes = [];
    /** @var Comparator\NumberComparator[] */
    private array $depths = [];
    /** @var int|Closure  */
    private $sort = SortableIterator::SORT_BY_NONE;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->ignore = self::IGNORE_VCS_FILES | self::IGNORE_DOT_FILES;
    }

    public function files(): self
    {
        $clone = clone $this;
        $clone->mode = Iterator\FileTypeFilterIterator::ONLY_FILES;

        return $clone;
    }

    public function directories(): self
    {
        $clone = clone $this;
        $clone->mode = Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES;

        return $clone;
    }

    public function allTypes(): self
    {
        $clone = clone $this;
        $clone->mode = Iterator\FileTypeFilterIterator::ALL;

        return $clone;
    }

    /**
     * @param string[] $directories
     * @return Finder
     * @throws InvalidArgumentException
     */
    public function exclude(array $directories): self
    {
        array_walk($directories, static function ($directory): void {
            if (!is_string($directory)) {
                if (is_object($directory)) {
                    throw new InvalidArgumentException(sprintf('Invalid directory given: %s', get_class($directory)));
                }

                throw new InvalidArgumentException(sprintf('Invalid directory given: %s', gettype($directory)));
            }
        });

        $clone = clone $this;
        $clone->exclude = array_merge($clone->exclude, $directories);

        return $clone;
    }

    /**
     * @param string[] $directories
     * @return Finder
     * @throws InvalidArgumentException
     */
    public function in(array $directories): self
    {
        array_walk($directories, static function ($directory): void {
            if (!is_string($directory)) {
                if (is_object($directory)) {
                    throw new InvalidArgumentException(sprintf('Invalid directory given: %s', get_class($directory)));
                }

                throw new InvalidArgumentException(sprintf('Invalid directory given: %s', gettype($directory)));
            }
        });

        $clone = clone $this;
        $clone->dirs = array_unique(array_merge($clone->dirs, $directories));

        return $clone;
    }

    /**
     * Adds tests for the directory depth.
     * Usage:
     *
     *     $finder->depth('> 1') // the Finder will start matching at level 1.
     *     $finder->depth('< 3') // the Finder will descend at most 3 levels of directories below the starting point.
     *
     * @param string|int $level The depth level expression
     * @return Finder
     * @see DepthRangeFilterIterator
     * @see NumberComparator
     */
    public function depth($level): self
    {
        $clone = clone $this;
        $clone->depths[] = new Comparator\NumberComparator((string) $level);

        return $clone;
    }

    /**
     * Adds tests for file dates.
     * The date must be something that strtotime() is able to parse:
     *
     *     $finder->date('since yesterday');
     *     $finder->date('until 2 days ago');
     *     $finder->date('> now - 2 hours');
     *     $finder->date('>= 2005-10-15');
     *
     * @param string $date A date range string
     * @return Finder
     * @see strtotime
     * @see DateRangeFilterIterator
     * @see DateComparator
     * @see \ILIAS\FileSystem\Filesystem::getTimestamp()
     */
    public function date(string $date): self
    {
        $clone = clone $this;
        $clone->dates[] = new Comparator\DateComparator($date);

        return $clone;
    }

    /**
     * Adds tests for file sizes.
     *
     *     $finder->size('> 10K');
     *     $finder->size('<= 1Ki');
     *     $finder->size(4);
     *     $finder->size(['> 10K', '< 20K'])
     *
     * @param string|int|string[]|int[] $sizes A size range string or an integer or an array of size ranges
     * @return Finder
     * @see SizeRangeFilterIterator
     * @see NumberComparator
     * @see \ILIAS\FileSystem\Filesystem::getSize()
     */
    public function size($sizes): self
    {
        if (!is_array($sizes)) {
            $sizes = [$sizes];
        }

        $clone = clone $this;

        foreach ($sizes as $size) {
            $clone->sizes[] = new Comparator\NumberComparator((string) $size);
        }

        return $clone;
    }

    public function reverseSorting(): self
    {
        $clone = clone $this;
        $clone->reverseSorting = true;

        return $clone;
    }

    public function ignoreVCS(bool $ignoreVCS): self
    {
        $clone = clone $this;
        if ($ignoreVCS) {
            $clone->ignore |= self::IGNORE_VCS_FILES;
        } else {
            $clone->ignore &= ~self::IGNORE_VCS_FILES;
        }

        return $clone;
    }

    /**
     * @param string[] $pattern
     * @return Finder
     * @throws InvalidArgumentException
     */
    public function addVCSPattern(array $pattern): self
    {
        array_walk($pattern, static function ($p): void {
            if (!is_string($p)) {
                if (is_object($p)) {
                    throw new InvalidArgumentException(sprintf('Invalid pattern given: %s', get_class($p)));
                }

                throw new InvalidArgumentException(sprintf('Invalid pattern given: %s', gettype($p)));
            }
        });

        $clone = clone $this;
        foreach ($pattern as $p) {
            $clone->vcsPatterns[] = $p;
        }

        $clone->vcsPatterns = array_unique($clone->vcsPatterns);

        return $clone;
    }

    /**
     * Sorts files and directories by an anonymous function.
     * The anonymous function receives two Metadata instances to compare.
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     * @param Closure $closure
     * @return Finder
     */
    public function sort(Closure $closure): self
    {
        $clone = clone $this;
        $clone->sort = $closure;

        return $clone;
    }

    public function sortByName(bool $useNaturalSort = false): self
    {
        $clone = clone $this;
        $clone->sort = Iterator\SortableIterator::SORT_BY_NAME;
        if ($useNaturalSort) {
            $clone->sort = Iterator\SortableIterator::SORT_BY_NAME_NATURAL;
        }

        return $clone;
    }

    public function sortByType(): self
    {
        $clone = clone $this;
        $clone->sort = Iterator\SortableIterator::SORT_BY_TYPE;

        return $clone;
    }

    public function sortByTime(): self
    {
        $clone = clone $this;
        $clone->sort = Iterator\SortableIterator::SORT_BY_TIME;

        return $clone;
    }

    /**
     * Appends an existing set of files/directories to the finder.
     * The set can be another Finder, an Iterator, an IteratorAggregate, or even a plain array.
     * @param iterable $iterator
     * @return Finder
     * @throws InvalidArgumentException when the given argument is not iterable
     */
    public function append(iterable $iterator): self
    {
        $clone = clone $this;

        if ($iterator instanceof IteratorAggregate) {
            $clone->iterators[] = $iterator->getIterator();
        } elseif ($iterator instanceof PhpIterator) {
            $clone->iterators[] = $iterator;
        } elseif ($iterator instanceof Traversable || is_array($iterator)) {
            $it = new ArrayIterator();
            foreach ($iterator as $file) {
                if ($file instanceof MetadataType) {
                    $it->append($file);
                } else {
                    throw new InvalidArgumentException('Finder::append() method wrong argument type in passed iterator.');
                }
            }
            $clone->iterators[] = $it;
        } else {
            throw new InvalidArgumentException('Finder::append() method wrong argument type.');
        }

        return $clone;
    }

    private function searchInDirectory(string $dir): PhpIterator
    {
        if (self::IGNORE_VCS_FILES === (self::IGNORE_VCS_FILES & $this->ignore)) {
            $this->exclude = array_merge($this->exclude, $this->vcsPatterns);
        }

        $iterator = new Iterator\RecursiveDirectoryIterator($this->filesystem, $dir);

        if ($this->exclude) {
            $iterator = new Iterator\ExcludeDirectoryFilterIterator($iterator, $this->exclude);
        }

        $iterator = new RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        if ($this->depths) {
            $iterator = new Iterator\DepthRangeFilterIterator($iterator, $this->depths);
        }

        if ($this->mode) {
            $iterator = new Iterator\FileTypeFilterIterator($iterator, $this->mode);
        }

        if ($this->dates) {
            $iterator = new Iterator\DateRangeFilterIterator($this->filesystem, $iterator, $this->dates);
        }

        if ($this->sizes) {
            $iterator = new Iterator\SizeRangeFilterIterator($this->filesystem, $iterator, $this->sizes);
        }

        if ($this->sort || $this->reverseSorting) {
            $iteratorAggregate = new Iterator\SortableIterator(
                $this->filesystem,
                $iterator,
                $this->sort,
                $this->reverseSorting
            );
            $iterator = $iteratorAggregate->getIterator();
        }

        return $iterator;
    }

    /**
     * @inheritdoc
     * @return PhpIterator|Metadata[]
     * @throws LogicException
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        if (0 === count($this->dirs) && 0 === count($this->iterators)) {
            throw new LogicException('You must call one of in() or append() methods before iterating over a Finder.');
        }

        if (1 === count($this->dirs) && 0 === count($this->iterators)) {
            return $this->searchInDirectory($this->dirs[0]);
        }

        $iterator = new AppendIterator();
        foreach ($this->dirs as $dir) {
            $iterator->append($this->searchInDirectory($dir));
        }

        foreach ($this->iterators as $it) {
            $iterator->append($it);
        }

        return $iterator;
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return iterator_count($this->getIterator());
    }
}
