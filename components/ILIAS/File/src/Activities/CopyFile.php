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

use ILIAS\Component\Activities\ActivityImpl;
use ILIAS\Component\Activities\ActivityType;
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
 * Copy a file object into another container.
 *
 * Unlike moving, the original stays where it is - so the `copy` permission is
 * enough on that side, the same the GUI asks for, while the target still needs
 * `create_file`. The copy is a new
 * object with its own reference id and its own resource.
 */
class CopyFile extends ActivityImpl
{
    public function __construct(
        private readonly RepositoryProvider $repository,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getType(): ActivityType
    {
        return ActivityType::Command;
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Copies a file object into another container.\n\n"
            . "The user needs the permission to copy the file and to create files in the target "
            . "container. The copy is a new object with a new reference id."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'ref_id' => $f->numeric('File', 'Reference id of the file object.')
                    ->withRequired(true)
                    ->withDedicatedName('ref_id'),
                'target_ref_id' => $f->numeric('Target', 'Reference id of the target container.')
                    ->withRequired(true)
                    ->withDedicatedName('target_ref_id'),
            ],
            'Copy a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('The copy of the file object.'),
            [
                'ref_id' => $f->int($text->simpleDocument('Reference id of the copy.')),
                'obj_id' => $f->int($text->simpleDocument('Object id of the object.')),
                'title' => $f->string($text->simpleDocument('Title of the object.')),
                'description' => $f->string($text->simpleDocument('Description of the object.')),
                'filename' => $f->string($text->simpleDocument('Name of the file.')),
                'mime_type' => $f->string($text->simpleDocument('Mime type of the file.')),
                'size' => $f->int($text->simpleDocument('Size of the file in bytes.')),
                'version' => $f->int($text->simpleDocument('Current version of the file.')),
                'rid' => $f->string($text->simpleDocument('Identification of the resource holding the content.')),
            ]
        );
    }

    /**
     * @param array{ref_id: int, target_ref_id: int} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // the legacy code doing the work acts as the user of the session, so
        // the check has to be about that one - see RepositoryProvider::isActingUser()
        if (!AccessCheck::isKnownUser($usr_id) || !$this->repository->isActingUser($usr_id)) {
            return false;
        }

        $ref_id = (int) $parameters['ref_id'];
        $target_ref_id = (int) $parameters['target_ref_id'];

        if ($this->repository->lookupType($ref_id) !== 'file') {
            return false;
        }

        if (!$this->repository->isContainer($target_ref_id)) {
            return false;
        }

        return $this->repository->mayCopy($usr_id, $ref_id, 'file')
            && $this->repository->mayCreate($usr_id, $target_ref_id, 'file');
    }

    /**
     * @param array{ref_id: int, target_ref_id: int} $parameters
     */
    public function perform(mixed $parameters): FileEntry
    {
        return $this->repository->copyFile(
            (int) $parameters['ref_id'],
            (int) $parameters['target_ref_id'],
        );
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
                    new \DomainException('You are not allowed to copy this file here.', 403)
                );
            }

            return $this->data->ok($this->perform($parameters));
        } catch (\Throwable $e) {
            return $this->data->error(Failure::of($e));
        }
    }

    /**
     * @param array<string, mixed> $raw_parameters
     *
     * @return array{ref_id: int, target_ref_id: int}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = $this->parameter->id($raw_parameters, 'ref_id', 'A file object is required.');
        $target_ref_id = $this->parameter->id($raw_parameters, 'target_ref_id', 'A target container is required.');

        if ($ref_id === $target_ref_id) {
            throw new \InvalidArgumentException('An object cannot be copied into itself.', 400);
        }

        return ['ref_id' => $ref_id, 'target_ref_id' => $target_ref_id];
    }
}
