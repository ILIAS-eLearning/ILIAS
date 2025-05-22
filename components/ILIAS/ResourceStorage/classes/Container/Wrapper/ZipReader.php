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

namespace ILIAS\components\ResourceStorage\Container\Wrapper;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Zip;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API.
 */
final class ZipReader
{
    /**
     * @var string
     */
    private const BASE = '/';
    /**
     * @var string
     */
    private const APPLICATION_OCTET_STREAM = 'application/octet-stream';
    private array $ignored = ['.', '..', '__MACOSX', '.info', '.DS_Store', Zip::DOT_EMPTY];
    private \ZipArchive $zip;
    private ?array $structure = null;

    public function __construct(
        private FileStream $stream
    ) {
        $this->zip = new \ZipArchive();
        if (!$this->zip->open($this->stream->getMetadata()['uri'], \ZipArchive::RDONLY)) {
            throw new \InvalidArgumentException('Could not open ZIP-File');
        }
    }

    public function getStructure(): array
    {
        if ($this->structure !== null) {
            return $this->structure;
        }

        $structure = [];
        for ($i = 0; $i < $this->zip->count(); $i++) {
            $path_original = $this->zip->getNameIndex($i);
            $path = '/' . ltrim($path_original, './');
            if ($path === '/') {
                continue;
            }
            $dirname = dirname($path);
            $basename = basename($path_original);
            if (in_array($basename, $this->ignored, true)) {
                continue;
            }

            $is_dir = (str_ends_with($path, '/') || str_ends_with($path, '\\'));

            $stats = $this->zip->statIndex($i, \ZipArchive::FL_UNCHANGED);

            $mime_type = null;
            $size = null;
            $modified = $modified = (int) ($stats['mtime'] ?? 0);
            if (!$is_dir) {
                try {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    // We only need the first few bytes to determine the mime-type this helps to reduce RAM-Usage
                    $stream = $this->zip->getStream($path_original);
                    $fread = fread($stream, 256);
                    $mime_type = finfo_buffer($finfo, $fread);
                    fclose($stream);
                    $size = (int) ($stats['size'] ?? 0);
                } catch (\Throwable) {
                    // ignore
                    $mime_type = self::APPLICATION_OCTET_STREAM;
                }

                // make sure we have a directory for this file as well. if this is missing in the ZIP
                $parent = dirname($path_original);
                $structure[$parent] = [
                    'path' => $parent,
                    'dirname' => dirname($parent),
                    'basename' => basename($parent),
                    'is_dir' => true,
                    'mime_type' => null,
                    'size' => null,
                    'modified' => $modified,
                ];
            }

            $structure[$path_original] = [
                'path' => $path,
                'dirname' => $dirname,
                'basename' => $basename,
                'is_dir' => $is_dir,
                'mime_type' => $mime_type,
                'size' => $size,
                'modified' => $modified,
            ];
        }

        return $this->structure = $structure;
    }

    /**
     * @param array|null $structure pass the existing structure to avoid re-reading the ZIP. pass null to read the ZIP
     */
    public function getItem(string $path_inside_zip, ?array $structure = null): array
    {
        $structure ??= $this->getStructure();
        $info = $structure[$path_inside_zip] ?? [];

        //$stream = Streams::ofString($this->zip->getFromName($path_inside_zip));

        $stream = Streams::ofFileInsideZIP(
            $this->stream->getMetadata()['uri'],
            $path_inside_zip
        );

        return [$stream, $info];
    }

}
