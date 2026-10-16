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

namespace ILIAS\File\Activities;

use ILIAS\Component\Activities\Query;
use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Data\Text\SimpleDocumentMarkdown;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\FileUpload\Activities\Failure;
use ILIAS\FileUpload\Activities\Parameter;

/**
 * Deliver the files of a container as one zip archive.
 *
 * Nothing here decides who may see what: `ListFiles` already answers that, and
 * `DeliverFileObject` checks the permission per file. This activity performs
 * both and only does the packing - which is why a user can never zip their way
 * around a permission.
 *
 * A file whose content cannot be read does not cost the user the archive: it is
 * left out and named in the answer. The reason stays generic, what went wrong in
 * the storage is nothing a client should learn about.
 */
class DownloadContainerAsZip extends Query
{
    private const int MAX_FILES = 500;

    /**
     * The archive is built and handed out in memory, encoded on top of that, so
     * the sum of what goes into it is limited as well as the number of files.
     */
    private const int MAX_SIZE = 100 * 1024 * 1024;

    public function __construct(
        private readonly ListFiles $list_files,
        private readonly DeliverFileObject $deliver_file,
        private readonly FilePayloadFactory $payloads,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Packs the files of a container into a zip archive.\n\n"
            . "Only files the user may read end up in it, at most " . self::MAX_FILES . " of them and "
            . self::MAX_SIZE . " bytes together. "
            . "Files whose content cannot be read are left out and listed in `skipped`. "
            . "The archive is returned base64 encoded; over REST `?raw=1` gives the zip itself."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'parent_ref_id' => $f->numeric('Container', 'Reference id of the container to pack.')
                    ->withRequired(true)
                    ->withDedicatedName('parent_ref_id'),
                'recursive' => $f->checkbox(
                    'Recursive',
                    'Also pack the files of the containers below this one.'
                )->withDedicatedName('recursive'),
            ],
            'Download a container as zip'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $this->payloads->getDescription(
            $f,
            'The zip archive with the files of the container.',
            [
                'skipped' => $f->list(
                    $text->simpleDocument('Files that could not be packed, empty if the archive is complete.'),
                    $f->object(
                        $text->simpleDocument('A file left out of the archive.'),
                        [
                            'ref_id' => $f->int($text->simpleDocument('Reference id of the file.')),
                            'title' => $f->string($text->simpleDocument('Title of the file.')),
                            'reason' => $f->string($text->simpleDocument('Why the file was left out.')),
                        ]
                    )
                ),
            ]
        );
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // the listing decides who may look into the container
        return $this->list_files->isAllowedToPerform($usr_id, $parameters);
    }

    /**
     * @param array{parent_ref_id: int, recursive: bool, usr_id: int} $parameters
     */
    public function perform(mixed $parameters): ContainerArchive
    {
        $files = $this->list_files->perform($parameters);

        if (count($files) > self::MAX_FILES) {
            throw new \DomainException(
                'This container holds more than ' . self::MAX_FILES . ' files, refusing to pack it.',
                422
            );
        }

        $size = array_sum(array_map(static fn(FileEntry $file): int => $file->size, $files));

        if ($size > self::MAX_SIZE) {
            throw new \DomainException(
                'The files of this container are larger than ' . self::MAX_SIZE . ' bytes, refusing to pack them.',
                422
            );
        }

        $name = 'container_' . $parameters['parent_ref_id'] . '.zip';

        // ZipArchive works on a path, so this is built outside of the ILIAS
        // filesystems and read back in one go
        $path = tempnam(sys_get_temp_dir(), 'ilias_zip_');

        if ($path === false) {
            throw new \RuntimeException('The archive could not be created.');
        }

        try {
            $skipped = $this->pack($path, $files);

            return ContainerArchive::of(
                $this->payloads->fromString($name, 'application/zip', (string) file_get_contents($path)),
                $skipped
            );
        } finally {
            @unlink($path);
        }
    }

    /**
     * @param array<string, mixed> $raw_parameters
     */
    public function maybePerformAs(
        InputFactory $input_factory,
        int $usr_id,
        array $raw_parameters
    ): Result {
        try {
            $parameters = $this->toParameters($raw_parameters);

            if (!$this->isAllowedToPerform($usr_id, $parameters)) {
                return $this->data->error(
                    new \DomainException('You are not allowed to look into this container.', 403)
                );
            }

            return $this->data->ok($this->perform([...$parameters, 'usr_id' => $usr_id]));
        } catch (\Throwable $e) {
            return $this->data->error(Failure::of($e));
        }
    }

    /**
     * @param list<FileEntry> $files
     *
     * @return list<SkippedFile>
     */
    private function pack(string $path, array $files): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('The archive could not be created.');
        }

        $names = [];
        $skipped = [];
        $added = 0;

        foreach ($files as $file) {
            $name = null;
            $content = false;

            try {
                // the listing has already dropped everything the user may not read,
                // so the content may be taken through the internal door
                $payload = $this->deliver_file->perform(['ref_id' => $file->ref_id]);
                $name = $this->entryName($payload->filename);
                $content = base64_decode($payload->content, true);
            } catch (\Throwable) {
                // left out below, the reason stays inside
            }

            // a file that cannot be read, decoded or added is left out and named,
            // it must not end up in the archive as an empty one
            if ($name === null
                || $content === false
                || !$zip->addFromString($this->uniqueName($names, $name), $content)
            ) {
                $skipped[] = new SkippedFile(
                    $file->ref_id,
                    $file->title,
                    'The content of this file could not be read.'
                );
                continue;
            }

            $added++;
        }

        // the entries are only written now, so this is where an archive breaks
        if (!$zip->close()) {
            throw new \RuntimeException('The archive could not be created.');
        }

        // ZipArchive writes nothing for an archive without entries, which would
        // hand out an empty file; an empty zip is its end of central directory
        if ($added === 0) {
            file_put_contents($path, "PK\x05\x06" . str_repeat("\0", 18));
        }

        return $skipped;
    }

    /**
     * A name for an entry that stays in the folder it is unpacked into: the
     * storage names its resources after uploaded file names, which are cleaned
     * on the way in - but an archive is opened elsewhere, by programs that trust
     * what it says, so a path in a name must not survive.
     */
    private function entryName(string $filename): string
    {
        $parts = explode('/', str_replace('\\', '/', $filename));
        $name = ltrim((string) end($parts), '.');

        return trim($name) === '' ? 'file' : $name;
    }

    /**
     * @param array<string, int> $names
     */
    private function uniqueName(array &$names, string $filename): string
    {
        if (!isset($names[$filename])) {
            $names[$filename] = 1;

            return $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $base = pathinfo($filename, PATHINFO_FILENAME);

        // a file may already carry the name a duplicate would get
        do {
            $name = $base . ' (' . ++$names[$filename] . ')' . ($extension === '' ? '' : '.' . $extension);
        } while (isset($names[$name]));

        $names[$name] = 1;

        return $name;
    }

    /**
     * @param array<string, mixed> $raw_parameters
     *
     * @return array{parent_ref_id: int, recursive: bool}
     */
    private function toParameters(array $raw_parameters): array
    {
        $parent_ref_id = $this->parameter->id($raw_parameters, 'parent_ref_id', 'A container is required.');

        return [
            'parent_ref_id' => $parent_ref_id,
            'recursive' => $this->parameter->flag($raw_parameters, 'recursive', false),
        ];
    }
}
