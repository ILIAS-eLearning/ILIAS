<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\MetadataType;

/**
 * Class Finder
 * Port of the Symfony2 bundle to work with the ILIAS FileSystem abstraction
 * @package ILIAS\Filesystem\Finder
 * @see     : https://github.com/symfony/finder
 * @author  Michael Jansen <mjansen@databay.de>
 */
final class Finder implements \IteratorAggregate, \Countable
{
    const IGNORE_VCS_FILES = 1;
    const IGNORE_DOT_FILES = 2;

    /** @var Filesystem */
    private $filesystem;

    /** @var string[] */
    private $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];

    /** @var \Iterator[] */
    private $iterators = [];

    /** @var string[] */
    protected $dirs = [];

    /** @var string[] */
    private $exclude = [];

    /** @var int */
    private $ignore = 0;

    /** @var int */
    private $mode = Iterator\FileTypeFilterIterator::ALL;

    /** @var bool */
    private $reverseSorting = false;

    /** @var Comparator\DateComparator[] */
    private $dates = [];

    /** @var Comparator\NumberComparator[] */
    private $sizes = [];

    /** @var Comparator\NumberComparator[] */
    private $depths = [];

    /** @var bool */
    private $sort = false;

    /**
     * Finder constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }

    /**
     * @return Finder
     */
    public function files() : self
    {
        $clone = clone $this;
        $clone->mode = Iterator\FileTypeFilterIterator::ONLY_FILES;

        return $clone;
    }

    /**
     * @return Finder
     */
    public function directories() : self
    {
        $clone = clone $this;
        $clone->mode = Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES;

        return $clone;
    }

    /**
     * @return Finder
     */
    public function allTypes() : self
    {
        $clone = clone $this;
        $clone->mode = Iterator\FileTypeFilterIterator::ALL;

        return $clone;
    }

    /**
     * @param string[] $directories
     * @return Finder
     */
    public function exclude(array $directories) : self
    {
        array_walk($directories, function ($directory) {
            if (!is_string($directory)) {
                if (is_object($directory)) {
                    throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', get_class($directory)));
                }

                throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', gettype($directory)));
            }
        });

        $clone = clone $this;
        $clone->exclude = array_merge($clone->exclude, $directories);

        return $clone;
    }

    /**
     * @param string[] $directories
     * @return Finder
     */
    public function in(array $directories) : self
    {
        array_walk($directories, function ($directory) {
            if (!is_string($directory)) {
                if (is_object($directory)) {
                    throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', get_class($directory)));
                }

                throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', gettype($directory)));
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
    public function depth($level) : self
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
    public function date(string $date) : self
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
    public function size($sizes) : self
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

    /**
     * @return Finder
     */
    public function reverseSorting() : self
    {
        $clone = clone $this;
        $clone->reverseSorting = true;

        return $clone;
    }

    /**
     * @param bool $ignoreVCS
     * @return Finder
     */
    public function ignoreVCS(bool $ignoreVCS) : self
    {
        $clone = clone $this;
        if ($ignoreVCS) {
            $clone->ignore |= static::IGNORE_VCS_FILES;
        } else {
            $clone->ignore &= ~static::IGNORE_VCS_FILES;
        }

        return $clone;
    }

    /**
     * @param string[] $pattern
     * @return Finder
     */
    public function addVCSPattern(array $pattern) : self
    {
        array_walk($pattern, function ($p) {
            if (!is_string($p)) {
                if (is_object($p)) {
                    throw new \InvalidArgumentException(sprintf('Invalid pattern given: %s', get_class($p)));
                }

                throw new \InvalidArgumentException(sprintf('Invalid pattern given: %s', gettype($p)));
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
     * @param \Closure $closure
     * @return Finder
     */
    public function sort(\Closure $closure) : self
    {
        $clone = clone $this;
        $clone->sort = $closure;

        return $clone;
    }

    /**
     * @param bool $useNaturalSort
     * @return Finder
     */
    public function sortByName(bool $useNaturalSort = false) : self
    {
        $clone = clone $this;
        $clone->sort = Iterator\SortableIterator::SORT_BY_NAME;
        if ($useNaturalSort) {
            $clone->sort = Iterator\SortableIterator::SORT_BY_NAME_NATURAL;
        }

        return $clone;
    }

    /**
     * @return Finder
     */
    public function sortByType() : self
    {
        $clone = clone $this;
        $clone->sort = Iterator\SortableIterator::SORT_BY_TYPE;

        return $clone;
    }

    /**
     * @return Finder
     */
    public function sortByTime() : self
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
     * @throws \InvalidArgumentException when the given argument is not iterable
     */
    public function append(iterable $iterator) : self
    {
        $clone = clone $this;

        if ($iterator instanceof \IteratorAggregate) {
            $clone->iterators[] = $iterator->getIterator();
        } elseif ($iterator instanceof \Iterator) {
            $clone->iterators[] = $iterator;
        } elseif ($iterator instanceof \Traversable || is_array($iterator)) {
            $it = new \ArrayIterator();
            foreach ($iterator as $file) {
                if ($file instanceof MetadataType) {
                    $it->append($file);
                } else {
                    throw new \InvalidArgumentException('Finder::append() method wrong argument type in passed iterator.');
                }
            }
            $clone->iterators[] = $it;
        } else {
            throw new \InvalidArgumentException('Finder::append() method wrong argument type.');
        }

        return $clone;
    }

    /**
     * @param string $dir
     * @return \Iterator
     */
    private function searchInDirectory(string $dir) : \Iterator
    {
        if (static::IGNORE_VCS_FILES === (static::IGNORE_VCS_FILES & $this->ignore)) {
            $this->exclude = array_merge($this->exclude, $this->vcsPatterns);
        }

        $iterator = new Iterator\RecursiveDirectoryIterator($this->filesystem, $dir);

        if ($this->exclude) {
            $iterator = new Iterator\ExcludeDirectoryFilterIterator($iterator, $this->exclude);
        }

        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

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
                $this->filesystem, $iterator, $this->sort, $this->reverseSorting
            );
            $iterator = $iteratorAggregate->getIterator();
        }

        return $iterator;
    }

    /**
     * @inheritdoc
     * @return \Iterator|Metadata[]
     */
    public function getIterator()
    {
        if (0 === count($this->dirs) && 0 === count($this->iterators)) {
            throw new \LogicException('You must call one of in() or append() methods before iterating over a Finder.');
        }

        if (1 === count($this->dirs) && 0 === count($this->iterators)) {
            return $this->searchInDirectory($this->dirs[0]);
        }

        $iterator = new \AppendIterator();
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
    public function count()
    {
        return iterator_count($this->getIterator());
    }
}