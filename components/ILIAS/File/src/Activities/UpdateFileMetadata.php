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
 * Change title and description of a file object.
 *
 * Only what the object is called, never its content - that is
 * `ReplaceFileContent`. Needs the write permission.
 */
class UpdateFileMetadata extends ActivityImpl
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
            "Changes title and description of a file object.\n\n"
            . "Fields that are not sent stay as they are. The user needs the permission to write the object."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'ref_id' => $f->numeric('File', 'Reference id of the file object.')
                    ->withRequired(true)
                    ->withDedicatedName('ref_id'),
                'title' => $f->text('Title', 'New title of the object.')
                    ->withDedicatedName('title'),
                'description' => $f->textarea('Description', 'New description of the object.')
                    ->withDedicatedName('description'),
            ],
            'Change the data of a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('The file object as it is now.'),
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
        );
    }

    /**
     * @param array{ref_id: int, ...} $parameters
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

        return $this->repository->mayWrite($usr_id, $ref_id, 'file');
    }

    /**
     * @param array{ref_id: int, title: ?string, description: ?string} $parameters
     */
    public function perform(mixed $parameters): FileEntry
    {
        return $this->repository->updateFileMetadata(
            (int) $parameters['ref_id'],
            $parameters['title'],
            $parameters['description'],
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
                    new \DomainException('You are not allowed to change this file.', 403)
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
     * @return array{ref_id: int, title: ?string, description: ?string}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = $this->parameter->id($raw_parameters, 'ref_id', 'A file object is required.');

        // only what was sent is changed, an empty title would be nonsense; the
        // fields of the input description strip tags, so these do as well
        $title = $this->parameter->text($raw_parameters, 'title');

        if ($title === '') {
            throw new \InvalidArgumentException('The title cannot be empty.', 400);
        }

        $description = $this->parameter->text($raw_parameters, 'description');

        return ['ref_id' => $ref_id, 'title' => $title, 'description' => $description];
    }
}
