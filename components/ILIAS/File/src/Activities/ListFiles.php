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
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\FileUpload\Activities\Failure;
use ILIAS\FileUpload\Activities\Parameter;

/**
 * List the files in a container of the repository.
 *
 * The counterpart of CreateFile: it answers "what is in there" and hands out
 * the resource identification of every file, so the content can be fetched with
 * `ILIAS\ResourceStorage\Activities\DeliverResource` without another lookup.
 *
 * Finding the objects is not this activity's job - that is the same question for
 * every type, and `ListContainerContent` answers it, including the permission
 * check per object. This activity performs it and then adds what only the file
 * component knows: file name, mime type, size, version, resource.
 */
class ListFiles extends Query
{
    public function __construct(
        private readonly RepositoryProvider $repository,
        private readonly ListContainerContent $list_content,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Lists the file objects of a container in the repository.\n\n"
            . "Only files the user is allowed to read are returned. Every entry carries the "
            . "identification of the resource holding the content."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'parent_ref_id' => $f->numeric(
                    'Container',
                    'Reference id of the container to look into.'
                )->withRequired(true)->withDedicatedName('parent_ref_id'),
                'recursive' => $f->checkbox(
                    'Recursive',
                    'Also list the files of the containers below this one.'
                )->withDedicatedName('recursive'),
            ],
            'List files'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->list(
            $text->simpleDocument('The files of the container.'),
            $f->object(
                $text->simpleDocument('A file object.'),
                [
                    'ref_id' => $f->int($text->simpleDocument('Reference id of the object.')),
                    'obj_id' => $f->int($text->simpleDocument('Object id of the object.')),
                    'title' => $f->string($text->simpleDocument('Title of the object.')),
                    'description' => $f->string($text->simpleDocument('Description of the object.')),
                    'filename' => $f->string($text->simpleDocument('Name of the file.')),
                    'mime_type' => $f->string($text->simpleDocument('Mime type of the file.')),
                    'size' => $f->int($text->simpleDocument('Size of the file in bytes.')),
                    'version' => $f->int($text->simpleDocument('Current version of the file.')),
                    'rid' => $f->string($text->simpleDocument('Identification of the resource holding the content.')),
                ]
            )
        );
    }

    /**
     * @param array{parent_ref_id: int, recursive: bool} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // the same question the general listing answers
        return $this->list_content->isAllowedToPerform($usr_id, $parameters);
    }

    /**
     * @param array{parent_ref_id: int, recursive: bool, usr_id: int} $parameters
     *
     * @return list<FileEntry>
     */
    public function perform(mixed $parameters): array
    {
        // the general listing knows the tree and the permissions, this one only
        // adds what a file is
        $entries = $this->list_content->perform([...$parameters, 'types' => ['file']]);

        $files = array_map(
            fn(ContainerEntry $entry): ?FileEntry => $this->repository->getFileEntry($entry->ref_id),
            $entries
        );

        return array_values(array_filter($files));
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
