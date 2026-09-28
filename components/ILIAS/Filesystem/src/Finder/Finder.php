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

namespace ILIAS\Filesystem\Finder;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Iterator\SortableIterator;
use ILIAS\Filesystem\Finder\Iterator\LazyIterator;

/**
 * Port of the Symfony2 bundle to work with the ILIAS FileSystem abstraction
 * @see https://github.com/symfony/finder
 * @implements \IteratorAggregate<non-empty-string, Metadata>
 */
final class Finder implements \IteratorAggregate, \Countable
{
    private const IGNORE_VCS_FILES = 1;
    private const IGNORE_DOT_FILES = 2;
    /** @var list<string> */
    private array $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];
    /** @var list<\Iterator> */
    private array $iterators = [];
    /** @var list<string> */
    protected array $dirs = [];
    /** @var list<string> */
    private array $exclude = [];
    private int $ignore;
    private int $mode = Iterator\FileTypeFilterIterator::ALL;
    private bool $reverseSorting = false;
    /** @var Comparator\DateComparator[] */
    private array $dates = [];
    /** @var Comparator\NumberComparator[] */
    private array $sizes = [];
    /** @var Comparator\NumberComparator[] */
    private array $depths = [];
    /** @var int|Closure */
    private $sort = SortableIterator::SORT_BY_NONE;
    private ?int $limit = null;

    public function __construct(private readonly Filesystem $filesystem)
    {
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
     * @param list<string> $directories
     * @throws \InvalidArgumentException
     */
    public function exclude(array $directories): self
    {
        array_walk($directories, static function ($directory): void {
            if (!\is_string($directory)) {
                throw new \InvalidArgumentException(\sprintf('Invalid directory given: %s', $directory::class));
            }
        });

        $clone = clone $this;
        $clone->exclude = array_merge($clone->exclude, $directories);

        return $clone;
    }

    /**
     * @param list<string> $directories
     * @throws \InvalidArgumentException
     */
    public function in(array $directories): self
    {
        array_walk($directories, static function ($directory): void {
            if (!\is_string($directory)) {
                throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', $directory::class));
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
     * @see DepthRangeFilterIterator
     * @see NumberComparator
     */
    public function depth(string|int $level): self
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
     * @see SizeRangeFilterIterator
     * @see NumberComparator
     * @see \ILIAS\FileSystem\Filesystem::getSize()
     */
    public function size(string|int|array $sizes): self
    {
        $sizes = \is_array($sizes) ? $sizes : [$sizes];

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

    public function limit(int $limit): self
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('Limit must be greater than or equal to 0.');
        }

        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }

    /**
     * Checks whether at least one entry matches the current finder criteria.
     */
    public function hasAny(): bool
    {
        $clone = clone $this;
        $clone->sort = SortableIterator::SORT_BY_NONE;
        $clone->reverseSorting = false;
        $clone->limit = 1;

        foreach ($clone->getIterator() as $_) {
            return true;
        }

        return false;
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
     * @param list<string> $pattern
     * @throws \InvalidArgumentException
     */
    public function addVCSPattern(array $pattern): self
    {
        array_walk($pattern, static function ($p): void {
            if (!\is_string($p)) {
                throw new \InvalidArgumentException(\sprintf('Invalid pattern given: %s', $p::class));
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
     */
    public function sort(\Closure $closure): self
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
     * The set can be another {@see Finder}, an {@see \Iterator}, an {@see \IteratorAggregate}, or even a plain array.
     * @param iterable<non-empty-string, Metadata> $iterator
     * @throws \InvalidArgumentException when the given argument is not iterable
     */
    public function append(iterable $iterator): self
    {
        $clone = clone $this;
        $clone->iterators[] = $iterator;

        return $clone;
    }

    private function searchInDirectory(string $dir): \Traversable
    {
        $exclude = $this->exclude;

        if (self::IGNORE_VCS_FILES === (self::IGNORE_VCS_FILES & $this->ignore)) {
            $exclude = array_merge($exclude, $this->vcsPatterns);
        }

        $iterator = new Iterator\RecursiveDirectoryIterator($this->filesystem, $dir);

        if ($exclude) {
            $iterator = new Iterator\ExcludeDirectoryFilterIterator($iterator, ...$exclude);
        }

        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        if ($this->depths) {
            $iterator = new Iterator\DepthRangeFilterIterator($iterator, ...$this->depths);
        }

        if ($this->mode !== 0) {
            $iterator = new Iterator\FileTypeFilterIterator($iterator, $this->mode);
        }

        if ($this->dates) {
            $iterator = new Iterator\DateRangeFilterIterator($this->filesystem, $iterator, ...$this->dates);
        }

        if ($this->sizes) {
            $iterator = new Iterator\SizeRangeFilterIterator($this->filesystem, $iterator, ...$this->sizes);
        }

        return $iterator;
    }

    /**
     * @return \Iterator<non-empty-string, Metadata>
     * @throws \LogicException
     */
    public function getIterator(): \Iterator
    {
        if ([] === $this->dirs && [] === $this->iterators) {
            throw new \LogicException('You must call one of in() or append() methods before iterating over a Finder.');
        }

        if ($this->limit === 0) {
            return new \EmptyIterator();
        }

        if (1 === count($this->dirs) && [] === $this->iterators) {
            $iterator = $this->searchInDirectory($this->dirs[0]);
        } else {
            $iterator = new \AppendIterator();
            foreach ($this->dirs as $dir) {
                $iterator->append(new \IteratorIterator(new LazyIterator(fn() => $this->searchInDirectory($dir))));
            }

            foreach ($this->iterators as $it) {
                $iterator->append(
                    new \IteratorIterator(
                        new LazyIterator(
                            static function () use ($it) {
                                foreach ($it as $key => $value) {
                                    yield $key => $value;
                                }
                            }
                        )
                    )
                );
            }
        }

        if ($this->sort || $this->reverseSorting) {
            $iterator = (new SortableIterator(
                $this->filesystem,
                $iterator,
                $this->sort,
                $this->reverseSorting
            ))->getIterator();
        }

        if ($this->limit === 0) {
            return new \EmptyIterator();
        }

        if ($this->limit !== null) {
            $iterator = new \LimitIterator(
                $iterator instanceof \Iterator ? $iterator : new \IteratorIterator($iterator),
                0,
                $this->limit
            );
        }

        return $iterator;
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }
}
