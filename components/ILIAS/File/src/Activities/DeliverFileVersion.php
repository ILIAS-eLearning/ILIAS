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
use ILIAS\FileDelivery\Activities\FilePayload;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\FileUpload\Activities\AccessCheck;
use ILIAS\FileUpload\Activities\Failure;
use ILIAS\ResourceStorage\Activities\ServiceProvider;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * Deliver an earlier version of a file object.
 *
 * Same rule as for the current content: the read permission on the object
 * decides, not who once uploaded that version.
 */
class DeliverFileVersion extends Query
{
    public function __construct(
        private readonly RepositoryProvider $repository,
        private readonly ServiceProvider $storage,
        private readonly FilePayloadFactory $payloads,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Delivers a specific version of a file object.\n\n"
            . "The user needs the permission to read the object. The content is returned base64 encoded."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'ref_id' => $f->numeric('File', 'Reference id of the file object.')
                    ->withRequired(true)
                    ->withDedicatedName('ref_id'),
                'version' => $f->numeric('Version', 'Version number to deliver.')
                    ->withRequired(true)
                    ->withDedicatedName('version'),
            ],
            'Deliver a version of a file'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $this->payloads->getDescription($f, 'The requested version of the file.');
    }

    /**
     * @param array{ref_id: int, version: int} $parameters
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

        return $this->repository->mayRead($usr_id, $ref_id, 'file');
    }

    /**
     * @param array{ref_id: int, version: int} $parameters
     */
    public function perform(mixed $parameters): FilePayload
    {
        $version = (int) $parameters['version'];
        $identification = $this->repository->lookupResourceId((int) $parameters['ref_id']);

        if ($identification === '') {
            throw new \OutOfBoundsException('This file object has no content.', 404);
        }

        $rid = $this->storage->find($identification);
        $revision = $rid === null ? null : $this->storage->getRevision($rid, $version);

        if ($rid === null || $revision === null) {
            throw new \OutOfBoundsException("There is no version {$version} of this file.", 404);
        }

        $information = $revision->getInformation();

        return $this->payloads->fromStream(
            $information->getTitle(),
            $information->getMimeType(),
            $this->storage->readStream($rid, $version),
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
                    new \DomainException('You are not allowed to read this file.', 403)
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
     * @return array{ref_id: int, version: int}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = (int) ($raw_parameters['ref_id'] ?? 0);
        $version = (int) ($raw_parameters['version'] ?? 0);

        if ($ref_id <= 0) {
            throw new \InvalidArgumentException('A file object is required.', 400);
        }

        if ($version <= 0) {
            throw new \InvalidArgumentException('A version is required.', 400);
        }

        return ['ref_id' => $ref_id, 'version' => $version];
    }
}
