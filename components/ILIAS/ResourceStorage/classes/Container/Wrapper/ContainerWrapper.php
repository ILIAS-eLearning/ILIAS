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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\ZIPStream;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API.
 */
final class ContainerWrapper
{
    private const BASE = '/';
    private array $ignored = ['.', '..', '__MACOSX', '.info', '.DS_Store'];

    private string $current_level;
    private array $data;
    private \ILIAS\ResourceStorage\Services $irss;
    private bool $use_flavour = true;
    private ZipReader $reader;
    private \ILIAS\FileDelivery\Services $file_delivery;

    public function __construct(
        private ResourceIdentification $rid,
        ?string $current_level = self::BASE ?? self::BASE
    ) {
        global $DIC;
        // dependencies
        $this->irss = $DIC->resourceStorage();
        $this->file_delivery = $DIC->fileDelivery();
        $this->reader = new ZipReader(
            $this->irss->consume()->stream($rid)->getStream()
        );

        // Init Data
        $this->initData($rid);

        $this->initCurrentLevel($current_level);
    }

    protected function initData(
        ResourceIdentification $rid
    ): void {
        // init ZIP

        if ($this->use_flavour) {
            $flavour_definition = new \ZipStructureDefinition();
            $flavour = $this->irss->flavours()->get(
                $rid,
                $flavour_definition
            );
            $flavour_stream = $flavour->getStreamResolvers()[0] ?? null;
            if ($flavour_stream !== null) {
                $this->data = $flavour_definition->wake((string) $flavour_stream->getStream());
            }
        }

        if (empty($this->data)) {
            $this->data = $this->reader->getStructure();
        }

        // remove items from array with key . or /
        $this->data = array_filter(
            $this->data,
            static fn($key) => !in_array($key, ['.', '/', './', '..'], true),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function download(string $path_inside_zip): never
    {
        [$stream, $info] = $this->reader->getItem($path_inside_zip, $this->data);

        $supported_for_inline = [
            'image/*',
            'application/pdf',
        ];

        $regex = array_map(
            static fn(string $mime_type) => str_replace('*', '.*', preg_quote($mime_type, '/')),
            $supported_for_inline
        );
        $regex = implode('|', $regex);

        $disposition = preg_match("/($regex)/", $info['mime_type']) ? Disposition::INLINE : Disposition::ATTACHMENT;

        $this->file_delivery->delivery()->deliver(
            $stream,
            $info['basename'],
            $info['mime_type'],
            $disposition
        );
    }

    public function unzip(string $path_inside_zip): bool
    {
        [$stream, $info] = $this->reader->getItem($path_inside_zip, $this->data);
        if ($info['mime_type'] !== 'application/zip' && $info['mime_type'] !== 'application/x-zip-compressed') {
            return false;
        }

        // save stream to temporary file
        $tmp_file = tempnam(sys_get_temp_dir(), 'ilias_zip_');

        /** @var ZIPStream $stream */
        $return = file_put_contents($tmp_file, $stream->detach());

        $zip_reader = new ZipReader(
            Streams::ofResource(fopen($tmp_file, 'rb'))
        );

        foreach ($zip_reader->getStructure() as $append_path_inside_zip => $item) {
            if ($item['is_dir']) {
                continue;
            }
            [$stream, $info] = $zip_reader->getItem($append_path_inside_zip, $this->data);
            $this->irss->manageContainer()->addStreamToContainer(
                $this->rid,
                $stream,
                $this->current_level . '/' . ltrim($append_path_inside_zip, './')
            );
        }

        unlink($tmp_file);
        return true;
    }

    public function getEntries(): \Generator
    {
        // sort by basename, but directories after files
        uasort($this->data, static function (array $a, array $b): int {
            if ($a['is_dir'] === $b['is_dir']) {
                return strnatcasecmp($a['basename'], $b['basename']);
            }
            return $a['is_dir'] ? 1 : -1;
        });

        foreach ($this->data as $path => $path_data) {
            $dirname = $path_data['dirname'] ?? './';
            if ($dirname !== $this->current_level) {
                continue;
            }
            $basename = $path_data['basename'] ?? '';
            if (in_array($basename, $this->ignored, true)) {
                continue;
            }

            if ($path_data['is_dir'] ?? false) {
                // directory
                yield $this->directory($path, $path_data);
            } else {
                // file
                $file = $this->file($path, $path_data);
                if ($file !== null) {
                    yield $file;
                }
            }
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function basename(string $full_path): string
    {
        return basename($full_path);
        // return preg_replace('/^' . preg_quote($this->current_level, '/') . '/', '', $full_path);
    }

    public function initCurrentLevel(string $current_level): void
    {
        // init current level
        $current_level = '/' . ltrim($current_level, './');
        $current_level = rtrim($current_level, '/');
        $this->current_level = $current_level === '' ? self::BASE : $current_level;
    }

    protected function directory(string $path_inside_zip, array $data): ?Dir
    {
        return new Dir(
            $path_inside_zip,
            $this->basename($path_inside_zip),
            new \DateTimeImmutable('@' . ($data['modified'] ?? 0))
        );
    }

    protected function file(string $path_inside_zip, array $data): ?File
    {
        return new File(
            $path_inside_zip,
            $this->basename($path_inside_zip),
            $data['mime_type'] ?? 'application/octet-stream',
            $data['size'] ?? 0,
            new \DateTimeImmutable('@' . ($data['modified'] ?? 0))
        );
    }

}
