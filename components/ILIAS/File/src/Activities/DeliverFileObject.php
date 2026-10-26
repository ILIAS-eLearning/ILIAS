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
use ILIAS\FileDelivery\Activities\FilePayload;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\ResourceStorage\Activities\DeliverResource;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * Deliver the content of a file object of the repository.
 *
 * This is the sanctioned way to get at a file of the repository: addressed by
 * its reference id, guarded by the read permission on it - which is the only
 * authority on who may download a file.
 *
 * It is also the example of how a resource of the ResourceStorage is delivered
 * internally: `DeliverResource` refuses to hand out resources of other users
 * when it is called from the outside, because it cannot know who may see them.
 * This activity does know - it owns the object the resource belongs to - so it
 * checks the permission itself and then performs `DeliverResource`, bypassing
 * that ownership rule on purpose.
 */
class DeliverFileObject extends Query
{
    public function __construct(
        private readonly RepositoryProvider $repository,
        private readonly DeliverResource $deliver_resource,
        private readonly FilePayloadFactory $payloads,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Delivers the content of a file object of the repository.\n\n"
            . "The user needs the permission to read the object. The content is returned "
            . "base64 encoded."
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
            'Deliver a file object'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $this->payloads->getDescription($f, 'The content of the file object.');
    }

    /**
     * @param array{ref_id: int} $parameters
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        // a request without a session has no user, and rbac must not be asked
        // about user 0 - the anonymous user may read plenty in a browser, but
        // this is an API
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
     * Delivers the content without asking anybody, as `perform()` does by
     * contract: this is the door for code that has checked `read` already, like
     * DownloadContainerAsZip. A ref_id from a request belongs into
     * `maybePerformAs()` - here it would hand out any file of the repository.
     *
     * @param array{ref_id: int} $parameters
     */
    public function perform(mixed $parameters): FilePayload
    {
        $rid = $this->repository->lookupResourceId((int) $parameters['ref_id']);

        if ($rid === '') {
            throw new \OutOfBoundsException('This file object has no content.', 404);
        }

        // the permission has been checked above, so the resource may be read
        // regardless of who owns it
        return $this->deliver_resource->perform(['rid' => $rid]);
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
     * @return array{ref_id: int}
     */
    private function toParameters(array $raw_parameters): array
    {
        $ref_id = $this->parameter->id($raw_parameters, 'ref_id', 'A file object is required.');

        return ['ref_id' => $ref_id];
    }
}
