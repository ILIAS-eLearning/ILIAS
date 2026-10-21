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

namespace ILIAS\FileUpload\Activities;

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * The place where the low level file activities put their files: a folder in
 * the temp filesystem of the installation.
 *
 * This is deliberately the simplest thing that can work - it is the low level
 * counterpart to storing a file in the ResourceStorage. A file is addressed by
 * a handle that also carries its original name.
 */
class TempFileStore
{
    public const string DIRECTORY = 'activities';

    /** a handle is a folder of 16 hex characters plus the file name in it */
    private const string HANDLE_REGEXP = '/^[a-f0-9]{16}\/[A-Za-z0-9._]+$/';

    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    public function store(string $filename, string $content): string
    {
        $handle = $this->newHandle($filename);

        $this->filesystem->put($this->pathOf($handle), $content);

        // the filesystems of ILIAS do not refuse a suffix the installation does
        // not accept, they rename the file (t.exe becomes texe.sec) - which is
        // then neither where the handle points nor anything we may keep
        if (!$this->filesystem->has($this->pathOf($handle))) {
            $this->filesystem->deleteDir(self::DIRECTORY . '/' . $this->folderOf($handle));

            throw new \InvalidArgumentException('Files of this type are not accepted.', 400);
        }

        return $handle;
    }

    public function has(string $handle): bool
    {
        return $this->filesystem->has($this->pathOf($handle));
    }

    public function read(string $handle): string
    {
        return $this->filesystem->read($this->pathOf($handle));
    }

    public function readStream(string $handle): FileStream
    {
        return $this->filesystem->readStream($this->pathOf($handle));
    }

    public function delete(string $handle): void
    {
        $this->filesystem->delete($this->pathOf($handle));
        $this->filesystem->deleteDir(self::DIRECTORY . '/' . $this->folderOf($handle));
    }

    public function getSize(string $handle): int
    {
        return (int) $this->filesystem->getSize($this->pathOf($handle), DataSize::Byte)->inBytes();
    }

    public function getMimeType(string $handle): string
    {
        try {
            return $this->filesystem->getMimeType($this->pathOf($handle));
        } catch (\Throwable) {
            return 'application/octet-stream';
        }
    }

    /**
     * The original file name is part of the handle, so a handle is all a
     * delivering activity needs.
     */
    public function getFilename(string $handle): string
    {
        return substr($handle, strpos($handle, '/') + 1);
    }

    /**
     * Every file gets a folder of its own, so it can keep its plain name: the
     * ResourceStorage takes the name of a resource from the file name of the
     * stream it is given.
     */
    public function newHandle(string $filename): string
    {
        return bin2hex(random_bytes(8)) . '/' . self::sanitize($filename);
    }

    public function pathOf(string $handle): string
    {
        if (preg_match(self::HANDLE_REGEXP, $handle) !== 1) {
            throw new \InvalidArgumentException("'{$handle}' is not a valid handle.");
        }

        return self::DIRECTORY . '/' . $handle;
    }

    private function folderOf(string $handle): string
    {
        return substr($handle, 0, (int) strpos($handle, '/'));
    }

    public static function sanitize(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = (string) preg_replace('/[^A-Za-z0-9._]/', '_', $filename);

        return trim($filename, '.') === '' ? 'file' : $filename;
    }
}
