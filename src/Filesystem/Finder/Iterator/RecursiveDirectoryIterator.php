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
 * Class RecursiveDirectoryIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class RecursiveDirectoryIterator implements \RecursiveIterator
{
    /** @var Metadata[] */
    protected array $files = [];

    /**
     * RecursiveDirectoryIterator constructor.
     */
    public function __construct(private Filesystem $filesystem, protected string $dir)
    {
    }

    /**
     * @inheritdoc
     */
    public function key(): string
    {
        return key($this->files);
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        next($this->files);
    }

    /**
     * @inheritdoc
     */
    public function current(): bool|\ILIAS\Filesystem\DTO\Metadata
    {
        return current($this->files);
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return current($this->files) instanceof Metadata;
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $contents = $this->filesystem->listContents($this->dir, false);
        $this->files = array_combine(
            array_map(static fn (Metadata $metadata): string => $metadata->getPath(), $contents),
            $contents
        );
    }

    /**
     * @inheritdoc
     */
    public function hasChildren(): bool
    {
        return $this->current()->isDir();
    }

    /**
     * @inheritdoc
     */
    public function getChildren(): \ILIAS\Filesystem\Finder\Iterator\RecursiveDirectoryIterator
    {
        return new self($this->filesystem, $this->current()->getPath());
    }
}
