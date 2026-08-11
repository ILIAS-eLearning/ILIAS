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

/**
 * @extends \FilterIterator<non-empty-string, Metadata>
 * @implements \RecursiveIterator<non-empty-string, Metadata>
 */
class ExcludeDirectoryFilterIterator extends \FilterIterator implements \RecursiveIterator
{
    private bool $isRecursive;
    /** @var array<string, true> */
    private array $excludedDirs = [];
    private string $excludedPattern = '';

    /**
     * @param \Iterator<non-empty-string, Metadata> $iterator The Iterator to filter
     */
    public function __construct(private readonly \Iterator $iterator, string ...$directories)
    {
        $this->isRecursive = $iterator instanceof \RecursiveIterator;

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

    public function hasChildren(): bool
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }

    public function getChildren(): self
    {
        $children = new self($this->iterator->getChildren());
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;

        return $children;
    }
}
