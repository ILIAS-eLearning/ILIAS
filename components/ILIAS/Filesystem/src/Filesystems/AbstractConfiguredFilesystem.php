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

namespace ILIAS\Filesystem\FileSystems;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Finder;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Visibility;
use ILIAS\Filesystem\Provider\FilesystemFactory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractConfiguredFilesystem implements Filesystem
{
    protected ?Filesystem $filesystem = null;

    public function __construct(
        protected FilesystemFactory $factory,
    ) {
    }

    abstract protected function getFQDN(): string;

    public function get(): Filesystem
    {
        return $this->filesystem = $this->factory->buildFor(
            $this->getFQDN(),
        );
    }

    public function finder(): Finder
    {
        return $this->filesystem?->finder() ?? $this->get()->finder();
    }

    // FileStreamReadAccess

    public function readStream(string $path): FileStream
    {
        return ($this->filesystem ?? $this->get())->readStream($path);
    }

    // FileStreamWriteAccess

    public function writeStream(string $path, FileStream $stream): void
    {
        ($this->filesystem ?? $this->get())->writeStream($path, $stream);
    }

    public function putStream(string $path, FileStream $stream): void
    {
        ($this->filesystem ?? $this->get())->putStream($path, $stream);
    }

    public function updateStream(string $path, FileStream $stream): void
    {
        ($this->filesystem ?? $this->get())->updateStream($path, $stream);
    }

    // FileReadAccess

    public function read(string $path): string
    {
        return ($this->filesystem ?? $this->get())->read($path);
    }

    public function has(string $path): bool
    {
        return ($this->filesystem ?? $this->get())->has($path);
    }

    public function getMimeType(string $path): string
    {
        return ($this->filesystem ?? $this->get())->getMimeType($path);
    }

    public function getTimestamp(string $path): \DateTimeImmutable
    {
        return ($this->filesystem ?? $this->get())->getTimestamp($path);
    }

    public function getSize(string $path, int $unit): DataSize
    {
        return ($this->filesystem ?? $this->get())->getSize($path, $unit);
    }

    public function setVisibility(string $path, string $visibility): bool
    {
        return ($this->filesystem ?? $this->get())->setVisibility($path, $visibility);
    }

    public function getVisibility(string $path): string
    {
        return ($this->filesystem ?? $this->get())->getVisibility($path);
    }

    // FileWriteAccess

    public function write(string $path, string $content): void
    {
        ($this->filesystem ?? $this->get())->write($path, $content);
    }

    public function update(string $path, string $new_content): void
    {
        ($this->filesystem ?? $this->get())->update($path, $new_content);
    }

    public function put(string $path, string $content): void
    {
        ($this->filesystem ?? $this->get())->put($path, $content);
    }

    public function delete(string $path): void
    {
        ($this->filesystem ?? $this->get())->delete($path);
    }

    public function readAndDelete(string $path): string
    {
        return ($this->filesystem ?? $this->get())->readAndDelete($path);
    }

    public function rename(string $path, string $new_path): void
    {
        ($this->filesystem ?? $this->get())->rename($path, $new_path);
    }

    public function copy(string $path, string $copy_path): void
    {
        ($this->filesystem ?? $this->get())->copy($path, $copy_path);
    }

    // DirectoryReadAccess

    public function hasDir(string $path): bool
    {
        return ($this->filesystem ?? $this->get())->hasDir($path);
    }

    public function listContents(string $path = '', bool $recursive = false): array
    {
        return ($this->filesystem ?? $this->get())->listContents($path, $recursive);
    }

    // DirectoryWriteAccess

    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS): void
    {
        ($this->filesystem ?? $this->get())->createDir($path, $visibility);
    }

    public function copyDir(string $source, string $destination): void
    {
        ($this->filesystem ?? $this->get())->copyDir($source, $destination);
    }

    public function deleteDir(string $path): void
    {
        ($this->filesystem ?? $this->get())->deleteDir($path);
    }
}
