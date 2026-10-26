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
 * Delete a file object.
 *
 * This does exactly what the GUI does: the object goes to the trash and an
 * administrator can bring it back - unless the installation has no trash, then
 * it is gone for good, as it would be in the GUI. The answer says which of the
 * two happened, a client must not assume it can be undone.
 */
class DeleteFile extends ActivityImpl
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
            "Deletes a file object, exactly like a deletion in the user interface.\n\n"
            . "If the trash of the installation is enabled, the object is moved there and can be "
            . "restored by an administrator; otherwise it is removed for good. `in_trash` tells which "
            . "of the two happened. The user needs the permission to delete the object."
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
            'Delete a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('What has been deleted.'),
            [
                'ref_id' => $f->int($text->simpleDocument('Reference id of the deleted object.')),
                'title' => $f->string($text->simpleDocument('Title of the deleted object.')),
                'in_trash' => $f->bool($text->simpleDocument(
                    'Whether the object went to the trash and can be restored; false means it is gone for good.'
                )),
            ]
        );
    }

    /**
     * @param array{ref_id: int} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // the legacy code doing the work acts as the user of the session, so
        // the check has to be about that one - see RepositoryProvider::isActingUser()
        if (!AccessCheck::isKnownUser($usr_id) || !$this->repository->isActingUser($usr_id)) {
            return false;
        }

        $ref_id = (int) $parameters['ref_id'];

        if ($this->repository->lookupType($ref_id) !== 'file') {
            return false;
        }

        return $this->repository->mayDelete($usr_id, $ref_id, 'file');
    }

    /**
     * @param array{ref_id: int} $parameters
     */
    public function perform(mixed $parameters): DeletedObject
    {
        $ref_id = (int) $parameters['ref_id'];

        $entry = $this->repository->getFileEntry($ref_id);

        if ($entry === null) {
            throw new \OutOfBoundsException("There is no file object {$ref_id}.", 404);
        }

        $in_trash = $this->repository->delete($ref_id);

        return new DeletedObject($ref_id, $entry->title, $in_trash);
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
                    new \DomainException('You are not allowed to delete this file.', 403)
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
     * @return array{ref_id: int}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = $this->parameter->id($raw_parameters, 'ref_id', 'A file object is required.');

        return ['ref_id' => $ref_id];
    }
}
