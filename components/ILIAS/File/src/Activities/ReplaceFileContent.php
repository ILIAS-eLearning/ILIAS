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
use ILIAS\FileUpload\Activities\FileContent;
use ILIAS\FileUpload\Activities\FileParameter;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * Give an existing file object new content - in one call.
 *
 * Together with CreateFile and ListFiles this closes the life cycle of a file:
 * create it, find it, change it. Like creating one, this is a single activity
 * on purpose; a client neither uploads separately nor knows about revisions.
 *
 * By default the previous content is kept as a version of its own, which is
 * what ILIAS does everywhere else - a file never silently loses its history.
 * With `keep_previous_version = false` the earlier revisions are dropped; the
 * version number goes up either way, that is how the storage counts.
 */
class ReplaceFileContent extends ActivityImpl
{
    public function __construct(
        private readonly FileParameter $file,
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
            "Replaces the content of a file object in the repository.\n\n"
            . "The content is expected to be base64 encoded; over REST a multipart upload works as well. "
            . "The user needs the permission to write the object. By default the previous content is kept "
            . "as an earlier version of the file."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'ref_id' => $f->numeric('File', 'Reference id of the file object.')
                    ->withRequired(true)
                    ->withDedicatedName('ref_id'),

                ...$this->file->describe($f, 'File', 'The new content of the file'),

                'keep_previous_version' => $f->checkbox(
                    'Keep the previous version',
                    'Keep the earlier revisions of the file. Switched off they are dropped, '
                    . 'the version number increases in both cases. Default is on.'
                )->withDedicatedName('keep_previous_version'),
            ],
            'Replace the content of a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('The file object with its new content.'),
            [
                'ref_id' => $f->int($text->simpleDocument('Reference id of the object.')),
                'obj_id' => $f->int($text->simpleDocument('Object id of the object.')),
                'title' => $f->string($text->simpleDocument('Title of the object.')),
                'description' => $f->string($text->simpleDocument('Description of the object.')),
                'filename' => $f->string($text->simpleDocument('Name of the file.')),
                'mime_type' => $f->string($text->simpleDocument('Mime type of the file.')),
                'size' => $f->int($text->simpleDocument('Size of the file in bytes.')),
                'version' => $f->int($text->simpleDocument('Version of the file after the change.')),
                'rid' => $f->string($text->simpleDocument('Identification of the resource holding the content.')),
            ]
        );
    }

    /**
     * @param array{ref_id: int, keep_previous_version: bool} $parameters
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
     * @param array{ref_id: int, keep_previous_version: bool, file: FileContent} $parameters
     */
    public function perform(mixed $parameters): FileEntry
    {
        $file = $parameters['file'];

        try {
            return $this->repository->replaceFileContent(
                (int) $parameters['ref_id'],
                $file,
                (bool) $parameters['keep_previous_version'],
            );
        } finally {
            // the content has served its purpose either way
            $file->release();
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
                    new \DomainException('You are not allowed to change this file.', 403)
                );
            }

            // the file is read after the check: a caller who may not write the
            // object does not get to put bytes into the installation
            $file = $this->file->read($raw_parameters);
        } catch (\Throwable $e) {
            return $this->data->error(Failure::of($e));
        }

        try {
            return $this->data->ok($this->perform([...$parameters, 'file' => $file]));
        } catch (\Throwable $e) {
            return $this->data->error(Failure::of($e));
        }
    }

    /**
     * @param array<string, mixed> $raw_parameters
     *
     * @return array{ref_id: int, keep_previous_version: bool}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = $this->parameter->id($raw_parameters, 'ref_id', 'A file object is required.');

        // keeping the history is the default, it has to be switched off explicitly -
        // anything that is not clearly a "no" must never drop it
        return [
            'ref_id' => $ref_id,
            'keep_previous_version' => $this->parameter->flag($raw_parameters, 'keep_previous_version', true),
        ];
    }
}
