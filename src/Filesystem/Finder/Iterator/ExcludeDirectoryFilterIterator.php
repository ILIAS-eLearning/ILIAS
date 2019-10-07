<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\DTO\Metadata;

/**
 * Class ExcludeDirectoryFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ExcludeDirectoryFilterIterator extends \FilterIterator implements \RecursiveIterator
{
    /** @var \Iterator|\RecursiveIterator */
    private $iterator;

    /** @var bool */
    private $isRecursive = false;

    /** @var string[] */
    private $excludedDirs = [];

    /** @var string */
    private $excludedPattern = '';

    /**
     * @param \Iterator $iterator The Iterator to filter
     * @param string[] $directories An array of directories to exclude
     */
    public function __construct(\Iterator $iterator, array $directories)
    {
        array_walk($directories, function ($directory) {
            if (!is_string($directory)) {
                if (is_object($directory)) {
                    throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', get_class($directory)));
                }

                throw new \InvalidArgumentException(sprintf('Invalid directory given: %s', gettype($directory)));
            }
        });

        $this->iterator = $iterator;
        $this->isRecursive = $iterator instanceof \RecursiveIterator;

        $patterns = [];
        foreach ($directories as $directory) {
            $directory = rtrim($directory, '/');
            if (!$this->isRecursive || false !== strpos($directory, '/')) {
                $patterns[] = preg_quote($directory, '#');
            } else {
                $this->excludedDirs[$directory] = true;
            }
        }

        if ($patterns) {
            $this->excludedPattern = '#(?:^|/)(?:' . implode('|', $patterns) . ')(?:/|$)#';
        }

        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept()
    {
        /** @var Metadata $metadata */
        $metadata = $this->current();

        if ($this->isRecursive && isset($this->excludedDirs[$metadata->getPath()]) && $metadata->isDir()) {
            return false;
        }

        if ($this->excludedPattern) {
            $path = $metadata->getPath();
            $path = str_replace('\\', '/', $path);

            return !preg_match($this->excludedPattern, $path);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren()
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        $children = new self($this->iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;

        return $children;
    }
}