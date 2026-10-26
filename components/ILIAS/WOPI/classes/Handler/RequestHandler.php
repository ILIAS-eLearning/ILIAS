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

namespace ILIAS\WOPI\Handler;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\FileDelivery\Token\DataSigning;
use ILIAS\WOPI\Lock\LockService;
use ILIAS\WOPI\Lock\LockResult;
use Psr\Http\Message\ResponseInterface;

/**
 * Handles the requests of a WOPI client, see
 * https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/rest/
 *
 * process() answers a request without sending it, handleRequest() sends the answer and
 * ends the request.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class RequestHandler
{
    /**
     * @var string
     */
    public const WOPI_BASE_URL = '/wopi/index.php/';
    /**
     * @var string
     */
    public const NAMESPACE_FILES = 'files';
    /**
     * @var string
     */
    private const HEADER_AUTHORIZATION = 'Authorization';
    /**
     * @var string
     */
    private const HEADER_AUTHORIZATION_BEARER = 'Bearer';
    /**
     * @var string
     */
    public const HEADER_X_WOPI_OVERRIDE = 'X-WOPI-Override';
    /**
     * @var string
     */
    public const HEADER_X_WOPI_LOCK = 'X-WOPI-Lock';
    /**
     * @var string
     */
    public const HEADER_X_WOPI_FILE_CONVERSION = 'X-WOPI-FileConversion';
    public const HEADER_X_WOPI_OLD_LOCK = 'X-WOPI-OldLock';
    public const HEADER_X_WOPI_LOCK_FAILURE_REASON = 'X-WOPI-LockFailureReason';
    public const HEADER_X_WOPI_SERVER_ERROR = 'X-WOPI-ServerError';
    public const HEADER_X_WOPI_ITEM_VERSION = 'X-WOPI-ItemVersion';
    public const HEADER_X_WOPI_MAX_EXPECTED_SIZE = 'X-WOPI-MaxExpectedSize';
    public const HEADER_X_WOPI_PROOF = 'X-WOPI-Proof';
    public const HEADER_X_WOPI_PROOF_OLD = 'X-WOPI-ProofOld';
    public const HEADER_X_WOPI_TIMESTAMP = 'X-WOPI-TimeStamp';
    /**
     * Operations which change the resource or its lock and therefore need a token issued for
     * editing.
     */
    private const WRITING_OPERATIONS = ['PUT', 'PUT_RELATIVE', 'LOCK', 'REFRESH_LOCK', 'UNLOCK'];

    private string $access_token = '';
    private int $token_user_id = 0;
    private string $token_resource_id = '';
    /**
     * class name of the stakeholder the token has been issued for
     */
    private string $token_stakeholder = '';
    private bool $editable = false;
    private bool $user_can_write = false;

    /**
     * @param string $public_url      scheme and host (and port) under which the WOPI client reaches
     *                                ILIAS, the URLs the client signs start with it
     * @param bool   $activated       whether WOPI is activated in the administration, all requests
     *                                are refused otherwise
     * @param int    $saving_interval seconds after which a save publishes the content, 0 keeps
     *                                every save as draft
     * @param int    $max_upload_size bytes a save may have at most, 0 for no limit
     * @param \Closure(): ?\ILIAS\WOPI\Discovery\ProofKeys|null $refresh_proof_keys fetches the proof keys
     *                                again when a request could not be verified, null if nothing was fetched
     * @param \ilLogger|null $logger  receives the errors, the WOPI client only learns that one occurred
     */
    public function __construct(
        private GlobalHttpState $http,
        private \ILIAS\ResourceStorage\Services $irss,
        private DataSigning $data_signer,
        private LockService $locks,
        private ProofValidator $proof_validator,
        private string $public_url,
        private bool $activated,
        private int $saving_interval = 0,
        private int $max_upload_size = 0,
        private ?\Closure $refresh_proof_keys = null,
        private ?\ilLogger $logger = null
    ) {
    }

    public function handleRequest(): never
    {
        $this->http->saveResponse($this->process());
        $this->http->sendResponse();
        $this->http->close();
    }

    public function process(): ResponseInterface
    {
        if (!$this->activated) {
            return $this->respond(404);
        }

        try {
            if (!$this->checkAuth()) {
                return $this->respond(401);
            }
            if (!$this->hasValidProof($this->proof_validator)) {
                // the client may have rotated its keys since the discovery has been read
                $keys = $this->refresh_proof_keys !== null ? ($this->refresh_proof_keys)() : null;
                if ($keys === null || !$this->hasValidProof($this->proof_validator->withKeys($keys))) {
                    return $this->respondServerError('Proof validation failed');
                }
            }

            $uri = $this->http->request()->getUri()->getPath();
            $request = explode(
                '/',
                substr($uri, strpos($uri, self::WOPI_BASE_URL) + strlen(self::WOPI_BASE_URL))
            );
            if ($request[0] !== self::NAMESPACE_FILES) {
                return $this->respond(404);
            }
            $resource_id = $request[1] ?? '';
            $action = $request[2] ?? '';

            // the token is only valid for the resource it was issued for
            if ($resource_id === '' || $this->token_resource_id !== $resource_id) {
                return $this->respond(401);
            }

            $identification = $this->irss->manage()->find($resource_id);
            if (!$identification instanceof ResourceIdentification) {
                return $this->respond(404);
            }

            $method_override = $this->http->request()->getHeader(self::HEADER_X_WOPI_OVERRIDE)[0] ?? null;
            $operation = $method_override ?? $this->http->request()->getMethod();

            if (
                in_array($operation, self::WRITING_OPERATIONS, true)
                && (!$this->editable || !$this->user_can_write)
            ) {
                return $this->respond(401);
            }

            return match ($operation) {
                'GET' => match ($action) {
                    '' => $this->checkFileInfo($identification),
                    'contents' => $this->getFile($identification),
                    default => $this->respond(501),
                },
                'PUT_RELATIVE', 'PUT' => $this->putFile($identification, $operation, $action),
                'LOCK' => $this->lock($resource_id),
                'GET_LOCK' => $this->respond(200, [self::HEADER_X_WOPI_LOCK => $this->locks->getLock($resource_id)]),
                'REFRESH_LOCK' => $this->respondLockResult(
                    $this->locks->refresh($resource_id, $this->lockHeader(), $this->token_user_id)
                ),
                'UNLOCK' => $this->respondLockResult(
                    $this->locks->unlock($resource_id, $this->lockHeader())
                ),
                default => $this->respond(501),
            };
        } catch (\Throwable $t) {
            // the message and the trace may disclose internals, only the log gets them
            $this->logger?->error($t->getMessage());
            $this->logger?->error($t->getTraceAsString());
            return $this->respondServerError('Internal server error');
        }
    }

    private function checkAuth(): bool
    {
        $auth = $this->http->request()->getHeader(self::HEADER_AUTHORIZATION)[0] ?? '';
        // spit and check bearer token
        $bearer = explode(' ', $auth);
        if ($auth !== '' && ($bearer[0] ?? '') !== self::HEADER_AUTHORIZATION_BEARER) {
            return false;
        }
        $bearer_token = $bearer[1] ?? '';
        if ($auth === '' && $bearer_token === '') {
            // we try to get the token from GET
            $bearer_token = $this->http->request()->getQueryParams()['access_token'] ?? '';
        }
        if ($bearer_token === '') {
            return false;
        }
        if (($token_data = $this->data_signer->verify($bearer_token, 'wopi')) === null) {
            return false;
        }

        $this->access_token = $bearer_token;
        $this->token_user_id = (int) ($token_data['user_id'] ?? 0);
        $this->token_resource_id = (string) ($token_data['resource_id'] ?? '');
        $this->editable = (bool) ($token_data['editable'] ?? '');
        $this->user_can_write = (bool) ($token_data['user_can_write'] ?? $this->editable);
        $this->token_stakeholder = (string) ($token_data['stakeholder'] ?? '');

        return true;
    }

    /**
     * IRSS adds the stakeholder of a new revision to the resource. The revision is therefore
     * stored on behalf of a stakeholder the resource already has - preferably the one the token
     * has been issued for - and owned by the user of the token. Saving never adds a stakeholder
     * which would take part in the access checks of the resource.
     */
    private function stakeholderFor(StorableResource $resource): ResourceStakeholder
    {
        $stakeholders = array_values($resource->getStakeholders());
        $stakeholder = $stakeholders[0] ?? null;
        foreach ($stakeholders as $candidate) {
            if (ltrim($candidate->getFullyQualifiedClassName(), '\\') === ltrim($this->token_stakeholder, '\\')) {
                $stakeholder = $candidate;
                break;
            }
        }
        if ($stakeholder === null) {
            return new WOPIUnknownStakeholder($this->token_user_id);
        }

        $wrapper = new WOPIStakeholderWrapper($this->token_user_id);
        $wrapper->init($stakeholder, $this->token_user_id);
        return $wrapper;
    }

    private function hasValidProof(ProofValidator $validator): bool
    {
        $request = $this->http->request();
        $query = $request->getUri()->getQuery();
        $url = rtrim($this->public_url, '/') . $request->getUri()->getPath() . ($query === '' ? '' : '?' . $query);

        return $validator->isValid(
            $this->access_token,
            $url,
            $request->getHeader(self::HEADER_X_WOPI_TIMESTAMP)[0] ?? null,
            $request->getHeader(self::HEADER_X_WOPI_PROOF)[0] ?? null,
            $request->getHeader(self::HEADER_X_WOPI_PROOF_OLD)[0] ?? null
        );
    }

    private function currentRevision(ResourceIdentification $identification): \ILIAS\ResourceStorage\Revision\Revision
    {
        $resource = $this->irss->manage()->getResource($identification);
        if ($this->editable) {
            return $resource->getCurrentRevisionIncludingDraft();
        }

        // A new, empty document has its content only as draft until it is published. Showing
        // the empty published revision makes the WOPI client try to initialise the file,
        // which a viewer must not - so the viewer shows the draft instead.
        $published = $resource->getCurrentRevision();

        return $published->getInformation()->getSize() === 0
            ? $resource->getCurrentRevisionIncludingDraft()
            : $published;
    }

    private function checkFileInfo(ResourceIdentification $identification): ResponseInterface
    {
        return $this->respondJson(
            new GetFileInfoResponse(
                $this->currentRevision($identification),
                $this->token_user_id,
                $this->editable,
                $this->user_can_write
            )
        );
    }

    private function getFile(ResourceIdentification $identification): ResponseInterface
    {
        $revision = $this->currentRevision($identification);

        $max_expected_size = $this->http->request()->getHeader(self::HEADER_X_WOPI_MAX_EXPECTED_SIZE)[0] ?? null;
        if (
            $max_expected_size !== null
            && ctype_digit($max_expected_size)
            && $revision->getInformation()->getSize() > (int) $max_expected_size
        ) {
            return $this->respond(412);
        }

        $stream = $this->irss->consume()->stream($identification)->setRevisionNumber(
            $revision->getVersionNumber()
        )->getStream();

        return $this->http->response()
                          ->withStatus(200)
                          ->withHeader(self::HEADER_X_WOPI_ITEM_VERSION, (string) $revision->getVersionNumber())
                          ->withBody($stream);
    }

    private function putFile(
        ResourceIdentification $identification,
        string $operation,
        string $action
    ): ResponseInterface {
        $is_file_conversion = (bool) ($this->http->request()->getHeader(
            self::HEADER_X_WOPI_FILE_CONVERSION
        )[0] ?? false);
        if ($operation === 'PUT_RELATIVE' && !$is_file_conversion) {
            return $this->respond(501);
        }
        // creating new files (PutRelativeFile on the file itself) is not supported
        if ($action !== 'contents') {
            return $this->respond(501);
        }

        $content_length = $this->http->request()->getHeader('Content-Length')[0] ?? null;
        if (
            $this->max_upload_size > 0
            && $content_length !== null
            && ctype_digit($content_length)
            && (int) $content_length > $this->max_upload_size
        ) {
            return $this->respond(413);
        }

        $resource = $this->irss->manage()->getResource($identification);
        $current_revision = $resource->getCurrentRevisionIncludingDraft();

        $lock_result = $this->locks->canWrite(
            $identification->serialize(),
            $this->http->request()->getHeader(self::HEADER_X_WOPI_LOCK)[0] ?? null,
            $current_revision->getInformation()->getSize() === 0
        );
        if (!$lock_result->success) {
            return $this->respondConflict($lock_result);
        }

        // the content is always stored as draft first, IRSS does not allow to append a
        // published revision while a draft exists. Publishing afterwards makes the content
        // just received the published one - publishing before appending would always
        // publish the previous save.
        $publish = false;
        if ($this->saving_interval > 0) {
            $creation_time = $resource->getCurrentRevision()
                                      ->getInformation()
                                      ->getCreationDate()
                                      ->getTimestamp();
            $publish = (time() - $creation_time) > $this->saving_interval;
        }

        $content = $this->http->request()->getBody()->getContents();
        if ($this->max_upload_size > 0 && strlen($content) > $this->max_upload_size) {
            return $this->respond(413);
        }

        $new_revision = $this->irss->manage()->appendNewRevisionFromStream(
            $identification,
            Streams::ofString($content),
            $this->stakeholderFor($resource),
            $current_revision->getTitle(),
            true
        );

        if ($publish) {
            $this->irss->manage()->publish($identification);
        }

        // WOPI expects no body here, only the new version. LastModifiedTime is what some
        // clients (Collabora) read to detect conflicting saves, the others ignore the body.
        return $this->respondJson(
            new Response([
                'LastModifiedTime' => $new_revision->getInformation()->getCreationDate()->format(DATE_ATOM),
            ])
        )->withHeader(self::HEADER_X_WOPI_ITEM_VERSION, (string) $new_revision->getVersionNumber());
    }

    private function lock(string $resource_id): ResponseInterface
    {
        $old_lock = $this->http->request()->getHeader(self::HEADER_X_WOPI_OLD_LOCK)[0] ?? null;

        return $this->respondLockResult(
            $old_lock === null
                ? $this->locks->lock($resource_id, $this->lockHeader(), $this->token_user_id)
                : $this->locks->unlockAndRelock($resource_id, $old_lock, $this->lockHeader(), $this->token_user_id)
        );
    }

    private function lockHeader(): string
    {
        return $this->http->request()->getHeader(self::HEADER_X_WOPI_LOCK)[0] ?? '';
    }

    /**
     * @param array<string, string> $headers
     */
    private function respond(int $status, array $headers = []): ResponseInterface
    {
        $response = $this->http->response()
                               ->withStatus($status)
                               ->withBody(Streams::ofString(''));
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    private function respondJson(Response $data): ResponseInterface
    {
        return $this->http->response()
                          ->withStatus(200)
                          ->withHeader('Content-Type', 'application/json')
                          ->withBody(Streams::ofString(json_encode($data, JSON_THROW_ON_ERROR)));
    }

    private function respondServerError(string $message): ResponseInterface
    {
        return $this->http->response()
                          ->withStatus(500)
                          ->withBody(Streams::ofString($message))
                          ->withHeader(self::HEADER_X_WOPI_SERVER_ERROR, $message);
    }

    private function respondLockResult(LockResult $result): ResponseInterface
    {
        return $result->success ? $this->respond(200) : $this->respondConflict($result);
    }

    /**
     * A conflict must always tell the WOPI client the current lock, an empty one included.
     */
    private function respondConflict(LockResult $result): ResponseInterface
    {
        return $this->respond(409, [
            self::HEADER_X_WOPI_LOCK => $result->current_lock,
            self::HEADER_X_WOPI_LOCK_FAILURE_REASON => (string) $result->reason,
        ]);
    }
}
