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
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * List what is in a container of the repository.
 *
 * The general case: it answers with everything the tree holds below a node,
 * optionally limited to certain object types. `ListFiles` is the special case
 * that adds what only a file component knows, and it performs this activity to
 * get there.
 *
 * It is deliberately **not contributed as an Activity**: what lives below a node
 * of the repository is not the domain of the file component. It stays here as
 * code until it has a home of its own - see components/ILIAS/Repository.
 *
 * Only objects the user may read are listed. Being allowed into a container
 * says nothing about the objects in it.
 *
 * Every object costs an access check, so a listing that would hold more than
 * MAX_ENTRIES objects is refused before any of them is checked - recursively
 * from the root, that would be the whole repository.
 */
class ListContainerContent extends Query
{
    public const int MAX_ENTRIES = 5000;

    public function __construct(
        private readonly RepositoryProvider $repository,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Lists the objects of a container in the repository.\n\n"
            . "Only objects the user is allowed to read are returned. The list can be limited "
            . "to certain object types, e.g. `file` or `cat,crs`."
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
                'types' => $f->text(
                    'Types',
                    'Comma separated list of object types to list, e.g. "file,cat". All types if empty.'
                )->withDedicatedName('types'),
                'recursive' => $f->checkbox(
                    'Recursive',
                    'Also list the content of the containers below this one.'
                )->withDedicatedName('recursive'),
            ],
            'List the content of a container'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->list(
            $text->simpleDocument('The objects in the container.'),
            $f->object(
                $text->simpleDocument('An object of the repository.'),
                [
                    'ref_id' => $f->int($text->simpleDocument('Reference id of the object.')),
                    'obj_id' => $f->int($text->simpleDocument('Object id of the object.')),
                    'type' => $f->string($text->simpleDocument('Object type, e.g. "file" or "cat".')),
                    'title' => $f->string($text->simpleDocument('Title of the object.')),
                    'description' => $f->string($text->simpleDocument('Description of the object.')),
                ]
            )
        );
    }

    /**
     * @param array{parent_ref_id: int, ...} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // no session, no listing - rbac must not be asked about user 0
        if (!AccessCheck::isKnownUser($usr_id)) {
            return false;
        }

        $parent_ref_id = (int) $parameters['parent_ref_id'];

        if (!$this->repository->isContainer($parent_ref_id)) {
            return false;
        }

        return $this->repository->mayRead($usr_id, $parent_ref_id);
    }

    /**
     * @param array{parent_ref_id: int, recursive: bool, types: string[], usr_id: int} $parameters
     *
     * @return list<ContainerEntry>
     */
    public function perform(mixed $parameters): array
    {
        $usr_id = (int) $parameters['usr_id'];

        $entries = $this->repository->listContent(
            (int) $parameters['parent_ref_id'],
            (bool) $parameters['recursive'],
            $parameters['types'],
        );

        if (count($entries) > self::MAX_ENTRIES) {
            throw new \DomainException(
                'This container holds more than ' . self::MAX_ENTRIES . ' objects, '
                . 'list a container further down or fewer types.',
                422
            );
        }

        return array_values(array_filter(
            $entries,
            fn(ContainerEntry $entry): bool => $this->repository->mayRead($usr_id, $entry->ref_id, $entry->type)
        ));
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
     * @return array{parent_ref_id: int, recursive: bool, types: string[]}
     */
    public function toParameters(array $raw_parameters): array
    {
        $parent_ref_id = $this->parameter->id($raw_parameters, 'parent_ref_id', 'A container is required.');

        return [
            'parent_ref_id' => $parent_ref_id,
            'recursive' => $this->parameter->flag($raw_parameters, 'recursive', false),
            'types' => $this->parameter->list($raw_parameters, 'types'),
        ];
    }
}
