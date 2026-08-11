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
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Filesystem;

/**
 * @implements \RecursiveIterator<Metadata>
 */
class RecursiveDirectoryIterator implements \RecursiveIterator
{
    /** @var array<non-empty-string, Metadata> */
    private array $files = [];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $dir
    ) {
    }

    /**
     * @return non-empty-string|null
     */
    public function key(): string|null
    {
        return key($this->files);
    }

    public function next(): void
    {
        next($this->files);
    }

    public function current(): Metadata|false
    {
        return current($this->files);
    }

    public function valid(): bool
    {
        return current($this->files) instanceof Metadata;
    }

    public function rewind(): void
    {
        $this->files = [];

        try {
            $contents = $this->filesystem->listContents($this->dir, false);
        } catch (DirectoryNotFoundException) {
            // A directory which cannot be listed, e.g. because its path is rejected by the
            // path normalizer, is treated as empty. Otherwise a single unusable directory
            // would abort the traversal of the whole tree.
            return;
        }

        foreach ($contents as $metadata) {
            $this->files[$metadata->getPath()] = $metadata;
        }
    }

    public function hasChildren(): bool
    {
        return $this->current()->isDir();
    }

    public function getChildren(): self
    {
        return new self($this->filesystem, $this->current()->getPath());
    }
}
