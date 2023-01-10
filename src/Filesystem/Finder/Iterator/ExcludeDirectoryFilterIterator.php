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

use FilterIterator;
use ILIAS\Filesystem\DTO\Metadata;
use InvalidArgumentException;
use Iterator as PhpIterator;
use RecursiveIterator;

/**
 * Class ExcludeDirectoryFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ExcludeDirectoryFilterIterator extends FilterIterator implements RecursiveIterator
{
    private bool $isRecursive;
    /** @var string[] */
    private array $excludedDirs = [];
    private string $excludedPattern = '';

    /**
     * @param PhpIterator $iterator    The Iterator to filter
     * @param string[]    $directories An array of directories to exclude
     * @throws InvalidArgumentException
     */
    public function __construct(private PhpIterator $iterator, array $directories)
    {
        array_walk($directories, static function ($directory): void {
            if (!is_string($directory)) {
                throw new InvalidArgumentException(sprintf('Invalid directory given: %s', $directory::class));
            }
        });
        $this->isRecursive = $iterator instanceof RecursiveIterator;

        $patterns = [];
        foreach ($directories as $directory) {
            $directory = rtrim($directory, '/');
            if (!$this->isRecursive || str_contains($directory, '/')) {
                $patterns[] = preg_quote($directory, '#');
            } else {
                $this->excludedDirs[$directory] = true;
            }
        }

        if ($patterns !== []) {
            $this->excludedPattern = '#(?:^|/)(' . implode('|', $patterns) . ')(?:/|$)#';
        }

        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept(): bool
    {
        /** @var Metadata $metadata */
        $metadata = $this->current();

        if ($this->isRecursive && isset($this->excludedDirs[$metadata->getPath()]) && $metadata->isDir()) {
            return false;
        }

        if ($this->excludedPattern !== '' && $this->excludedPattern !== '0') {
            $path = $metadata->getPath();
            $path = str_replace('\\', '/', $path);

            return !preg_match($this->excludedPattern, $path);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren(): bool
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): \ILIAS\Filesystem\Finder\Iterator\ExcludeDirectoryFilterIterator
    {
        $children = new self($this->iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;

        return $children;
    }
}
