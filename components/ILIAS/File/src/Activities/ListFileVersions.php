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
use ILIAS\FileUpload\Activities\AccessCheck;
use ILIAS\FileUpload\Activities\Failure;
use ILIAS\FileUpload\Activities\Parameter;
use ILIAS\ResourceStorage\Activities\ResourceVersion;
use ILIAS\ResourceStorage\Activities\ServiceProvider;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * List the versions of a file object.
 *
 * The storage keeps every revision of a file; this makes them visible. The list
 * names who uploaded each version, so it needs the write permission - the same
 * the GUI asks for its versions tab (`Capabilities::MANAGE_VERSIONS`). Reading
 * is not enough, a reader would learn who worked on the file and when.
 */
class ListFileVersions extends Query
{
    public function __construct(
        private readonly RepositoryProvider $repository,
        private readonly ServiceProvider $storage,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Lists all versions of a file object, oldest first.\n\n"
            . "The user needs the permission to write the object, as for the versions in the user "
            . "interface. Use "
            . "`ILIAS\\File\\Activities\\DeliverFileVersion` to get the content of one of them."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'ref_id' => $f->numeric('File', 'Reference id of the file object.')
                    ->withRequired(true)
                    ->withDedicatedName('ref_id'),
            ],
            'List the versions of a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->list(
            $text->simpleDocument('The versions of the file.'),
            $f->object(
                $text->simpleDocument('One version of the file.'),
                [
                    'version' => $f->int($text->simpleDocument('Version number.')),
                    'title' => $f->string($text->simpleDocument('Title of this version.')),
                    'mime_type' => $f->string($text->simpleDocument('Mime type of this version.')),
                    'size' => $f->int($text->simpleDocument('Size of this version in bytes.')),
                    'owner_id' => $f->int($text->simpleDocument('User who created this version.')),
                    'created' => $f->string($text->simpleDocument('When this version was created, ISO 8601.')),
                ]
            )
        );
    }

    /**
     * @param array{ref_id: int} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        if (!AccessCheck::isKnownUser($usr_id)) {
            return false;
        }

        $ref_id = (int) $parameters['ref_id'];

        if ($this->repository->lookupType($ref_id) !== 'file') {
            return false;
        }

        return $this->repository->mayWrite($usr_id, $ref_id, 'file');
    }

    /**
     * @param array{ref_id: int} $parameters
     *
     * @return list<ResourceVersion>
     */
    public function perform(mixed $parameters): array
    {
        $rid = $this->storage->find($this->resourceIdOf((int) $parameters['ref_id']));

        if ($rid === null) {
            throw new \OutOfBoundsException('This file object has no content.', 404);
        }

        return array_map(ResourceVersion::of(...), $this->storage->getRevisions($rid));
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
                    new \DomainException('You are not allowed to see the versions of this file.', 403)
                );
            }

            return $this->data->ok($this->perform($parameters));
        } catch (\Throwable $e) {
            return $this->data->error(Failure::of($e));
        }
    }

    private function resourceIdOf(int $ref_id): string
    {
        $rid = $this->repository->lookupResourceId($ref_id);

        if ($rid === '') {
            throw new \OutOfBoundsException('This file object has no content.', 404);
        }

        return $rid;
    }

    /**
     * @param array<string, mixed> $raw_parameters
     *
     * @return array{ref_id: int}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = $this->parameter->id($raw_parameters, 'ref_id', 'A file object is required.');

        return ['ref_id' => $ref_id];
    }
}
