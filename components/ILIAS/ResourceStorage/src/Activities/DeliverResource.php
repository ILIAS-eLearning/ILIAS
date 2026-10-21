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

namespace ILIAS\ResourceStorage\Activities;

use ILIAS\Component\Activities\Query;
use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Data\Text\SimpleDocumentMarkdown;
use ILIAS\FileDelivery\Activities\FilePayload;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use ILIAS\FileUpload\Activities\AccessCheck;
use ILIAS\FileUpload\Activities\Failure;
use ILIAS\FileUpload\Activities\Parameter;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * Deliver a resource of the ResourceStorage.
 *
 * The file is read through a consumer of the storage, which is the only
 * sanctioned way to get to the bytes of a resource. What comes out is the same
 * payload every other delivering activity produces - the shared
 * FilePayloadFactory of FileDelivery makes sure of that, so a consumer does not
 * have to care where a file was stored.
 *
 * ## Who may get what
 *
 * A resource identification says nothing about who may see the resource: that
 * is the business of whoever the resource belongs to - a file object, a badge, a
 * portfolio. This activity has no way of knowing, so it distinguishes two ways
 * of being used:
 *
 * - **Called from the outside** (`maybePerformAs()`, i.e. every REST request):
 *   a user only gets resources of their own, `owner id == user id`. Anything
 *   else would turn an unguessable id into the only protection of a file.
 * - **Called from another activity** (`perform()`): no check happens here. The
 *   calling activity knows what the resource belongs to and MUST check the
 *   access to that thing itself - see `ILIAS\File\Activities\DeliverFileObject`,
 *   which checks the read permission on the file object and then performs this
 *   activity.
 */
class DeliverResource extends Query
{
    public function __construct(
        private readonly ServiceProvider $storage,
        private readonly FilePayloadFactory $payloads,
        private readonly Parameter $parameter,
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Delivers a resource of the ILIAS Resource Storage Service through its consumer.\n\n"
            . "The content is returned base64 encoded."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section(
            [
                'rid' => $f->text('Resource', 'The identification of the resource.')
                    ->withRequired(true)
                    ->withDedicatedName('rid'),
            ],
            'Deliver a resource'
        );
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $this->payloads->getDescription($f, 'The resource that has been requested.');
    }

    /**
     * Only the owner of a resource may fetch it directly.
     *
     * Note that the stakeholders are deliberately not asked: the default
     * implementation of `canBeAccessedByCurrentUser()` answers `true`, which
     * would leave a resource protected by nothing but the secrecy of its id.
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        if (!AccessCheck::isKnownUser($usr_id)) {
            return false;
        }

        $rid = $this->find($parameters['rid']);

        if (!$rid instanceof ResourceIdentification) {
            return false;
        }

        return $this->storage->getOwnerId($rid) === $usr_id;
    }

    /**
     * @param array{rid: string} $parameters
     */
    public function perform(mixed $parameters): FilePayload
    {
        $rid = $this->find($parameters['rid']);

        if (!$rid instanceof ResourceIdentification) {
            throw new \OutOfBoundsException("There is no resource '{$parameters['rid']}'.", 404);
        }

        $information = $this->storage->getInformation($rid);

        return $this->payloads->fromStream(
            $information->getTitle(),
            $information->getMimeType(),
            $this->storage->readStream($rid),
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
                    new \DomainException('You are not allowed to access this resource.', 403)
                );
            }

            return $this->data->ok($this->perform($parameters));
        } catch (\Throwable $e) {
            return $this->data->error(Failure::of($e));
        }
    }

    private function find(string $rid): ?ResourceIdentification
    {
        return $this->storage->find($rid);
    }

    /**
     * @param array<string, mixed> $raw_parameters
     *
     * @return array{rid: string}
     */
    private function toParameters(array $raw_parameters): array
    {
        $rid = (string) $this->parameter->string($raw_parameters, 'rid');

        if ($rid === '') {
            throw new \InvalidArgumentException('A resource identification is required.', 400);
        }

        return ['rid' => $rid];
    }
}
