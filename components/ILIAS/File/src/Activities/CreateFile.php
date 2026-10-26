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
 * Create a file object in the repository - in one call.
 *
 * This is what a user means by "create a file": the bytes have to get into the
 * system, an object has to be created, put into the tree below a container the
 * user may write to, and it has to inherit the permissions from there. Doing
 * that in a single activity is the point - a client should not have to know the
 * order of these steps, nor should it be able to leave half an object behind.
 *
 * The file comes with the call, described and read by FileParameter; there is
 * no upload of its own that could leave a file nobody asked for in the
 * installation. Storing it is left to `ilObjFile`, which brings its own
 * stakeholder and its own revision handling - which is why this activity does
 * not use the ResourceStorage activities.
 */
class CreateFile extends ActivityImpl
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
            "Creates a file object in the repository below the given container, in one call.\n\n"
            . "The content is expected to be base64 encoded; over REST a multipart upload works as well. "
            . "The user needs the permission to create files in the target container."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'parent_ref_id' => $f->numeric(
                    'Target',
                    'Reference id of the container the file is created in.'
                )->withRequired(true)->withDedicatedName('parent_ref_id'),

                ...$this->file->describe($f, 'File', 'The content of the new file'),

                'title' => $f->text('Title', 'Title of the object, defaults to the file name.')
                    ->withDedicatedName('title'),
                'description' => $f->textarea('Description', 'Description of the object.')
                    ->withDedicatedName('description'),
            ],
            'Create a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        $text = $this->data->text()->markdown();

        return $f->object(
            $text->simpleDocument('The file object that has been created.'),
            [
                'ref_id' => $f->int($text->simpleDocument('Reference id of the new object.')),
                'obj_id' => $f->int($text->simpleDocument('Object id of the new object.')),
                'title' => $f->string($text->simpleDocument('Title of the object.')),
                'description' => $f->string($text->simpleDocument('Description of the object.')),
                'filename' => $f->string($text->simpleDocument('Name of the file.')),
                'mime_type' => $f->string($text->simpleDocument('Mime type of the file.')),
                'size' => $f->int($text->simpleDocument('Size of the file in bytes.')),
                'version' => $f->int($text->simpleDocument('Version of the file, 1 for a new object.')),
                'rid' => $f->string($text->simpleDocument('Identification of the resource holding the content.')),
            ]
        );
    }

    /**
     * @param array{parent_ref_id: int, ...} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // the legacy code doing the work acts as the user of the session, so
        // the check has to be about that one - see RepositoryProvider::isActingUser()
        if (!AccessCheck::isKnownUser($usr_id) || !$this->repository->isActingUser($usr_id)) {
            return false;
        }

        $parent_ref_id = (int) $parameters['parent_ref_id'];

        // a file can only live below something that holds objects - rbac alone
        // would happily let one be put into another file
        if (!$this->repository->isContainer($parent_ref_id)) {
            return false;
        }

        return $this->repository->mayCreate($usr_id, $parent_ref_id, 'file');
    }

    /**
     * @param array{parent_ref_id: int, title: ?string, description: ?string, file: FileContent} $parameters
     */
    public function perform(mixed $parameters): FileEntry
    {
        $file = $parameters['file'];

        try {
            return $this->repository->createFile(
                (int) $parameters['parent_ref_id'],
                $parameters['title'],
                $parameters['description'],
                $file,
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
                    new \DomainException('You are not allowed to create files here.', 403)
                );
            }

            // the file is read after the check: a caller who may not create
            // files here does not get to put bytes into the installation
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
     * @return array{parent_ref_id: int, title: ?string, description: ?string}
     */
    private function toParameters(array $raw_parameters): array
    {
        $parent_ref_id = $this->parameter->id($raw_parameters, 'parent_ref_id', 'A target container is required.');

        // the fields of the input description strip tags, so these do as well
        $title = $this->parameter->text($raw_parameters, 'title');
        $description = $this->parameter->text($raw_parameters, 'description');

        return [
            'parent_ref_id' => $parent_ref_id,
            'title' => $title === '' ? null : $title,
            'description' => $description === '' ? null : $description,
        ];
    }
}
