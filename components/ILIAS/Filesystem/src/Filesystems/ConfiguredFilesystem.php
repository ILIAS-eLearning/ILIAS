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
 * A filesystem that is only built when it is first used.
 *
 * Which one it builds is decided by the interface it was configured for; the
 * factory maps that to a directory. That directory comes from the ini files and
 * the current client, neither of which exists while the component bootstrap is
 * being built, so nothing may be resolved in the constructor.
 *
 * The subclasses in this namespace add nothing but their own type: the
 * bootstrap wires six separate filesystem services, and each of them has to be
 * an instance of its own interface.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ConfiguredFilesystem implements Filesystem
{
    private ?Filesystem $filesystem = null;

    /**
     * @param string $for_interface one of the filesystem interfaces of this
     *                              namespace, see DelegatingFilesystemFactory
     */
    public function __construct(
        private readonly FilesystemFactory $factory,
        private readonly string $for_interface,
    ) {
    }

    protected function filesystem(): Filesystem
    {
        return $this->filesystem ??= $this->factory->buildFor($this->for_interface);
    }

    public function finder(): Finder
    {
        return $this->filesystem()->finder();
    }

    // FileStreamReadAccess

    public function readStream(string $path): FileStream
    {
        return $this->filesystem()->readStream($path);
    }

    // FileStreamWriteAccess

    public function writeStream(string $path, FileStream $stream): void
    {
        $this->filesystem()->writeStream($path, $stream);
    }

    public function putStream(string $path, FileStream $stream): void
    {
        $this->filesystem()->putStream($path, $stream);
    }

    public function updateStream(string $path, FileStream $stream): void
    {
        $this->filesystem()->updateStream($path, $stream);
    }

    // FileReadAccess

    public function read(string $path): string
    {
        return $this->filesystem()->read($path);
    }

    public function has(string $path): bool
    {
        return $this->filesystem()->has($path);
    }

    public function getMimeType(string $path): string
    {
        return $this->filesystem()->getMimeType($path);
    }

    public function getTimestamp(string $path): \DateTimeImmutable
    {
        return $this->filesystem()->getTimestamp($path);
    }

    public function getSize(string $path, int $unit): DataSize
    {
        return $this->filesystem()->getSize($path, $unit);
    }

    public function setVisibility(string $path, string $visibility): bool
    {
        return $this->filesystem()->setVisibility($path, $visibility);
    }

    public function getVisibility(string $path): string
    {
        return $this->filesystem()->getVisibility($path);
    }

    // FileWriteAccess

    public function write(string $path, string $content): void
    {
        $this->filesystem()->write($path, $content);
    }

    public function update(string $path, string $new_content): void
    {
        $this->filesystem()->update($path, $new_content);
    }

    public function put(string $path, string $content): void
    {
        $this->filesystem()->put($path, $content);
    }

    public function delete(string $path): void
    {
        $this->filesystem()->delete($path);
    }

    public function readAndDelete(string $path): string
    {
        return $this->filesystem()->readAndDelete($path);
    }

    public function rename(string $path, string $new_path): void
    {
        $this->filesystem()->rename($path, $new_path);
    }

    public function copy(string $path, string $copy_path): void
    {
        $this->filesystem()->copy($path, $copy_path);
    }

    // DirectoryReadAccess

    public function hasDir(string $path): bool
    {
        return $this->filesystem()->hasDir($path);
    }

    public function listContents(string $path = '', bool $recursive = false): array
    {
        return $this->filesystem()->listContents($path, $recursive);
    }

    // DirectoryWriteAccess

    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS): void
    {
        $this->filesystem()->createDir($path, $visibility);
    }

    public function copyDir(string $source, string $destination): void
    {
        $this->filesystem()->copyDir($source, $destination);
    }

    public function deleteDir(string $path): void
    {
        $this->filesystem()->deleteDir($path);
    }
}
