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

namespace ILIAS\components\WOPI\Handler;

use ILIAS\HTTP\Services;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
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

    // WOPI Header
    /**
     * @var string
     */
    private const HEADER_X_REQUEST_ID = 'X-Request-ID';
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

    private Services $http;
    private \ILIAS\ResourceStorage\Services $irss;
    private DataSigner $data_signer;
    private ?int $token_user_id = null;
    private ?string $token_resource_id = null;
    private ResourceStakeholder $stakeholder;
    private int $saving_interval = 0;
    private bool $editable = false;

    public function __construct()
    {
        global $DIC;
        $this->http = $DIC->http();
        $this->data_signer = $DIC['file_delivery.data_signer'];
        $this->irss = $DIC->resourceStorage();
        $this->saving_interval = (int) $DIC->settings()->get('saving_interval');
    }

    protected function checkAuth(): void
    {
        $auth = $this->http->request()->getHeader(self::HEADER_AUTHORIZATION)[0] ?? '';
        // spit and check bearer token
        $bearer = explode(' ', $auth);
        if ($auth !== '' && ($bearer[0] ?? '') !== self::HEADER_AUTHORIZATION_BEARER) {
            throw new \InvalidArgumentException();
        }
        $bearer_token = $bearer[1] ?? '';
        if ($auth === '' && $bearer_token === '') {
            // we try to get the token from GET
            $bearer_token = $this->http->request()->getQueryParams()['access_token'] ?? '';
        }
        if ($bearer_token === '') {
            throw new \InvalidArgumentException();
        }
        if (($token_data = $this->data_signer->verify($bearer_token, 'wopi')) === null) {
            throw new \InvalidArgumentException();
        }

        $this->token_user_id = (int) ($token_data['user_id'] ?? 0);
        $this->token_resource_id = (string) ($token_data['resource_id'] ?? '');
        $this->editable = (bool) ($token_data['editable'] ?? '');
        $stakeholder = $token_data['stakeholder'] ?? null;
        if ($stakeholder !== null) {
            try {
                $this->stakeholder = new WOPIStakeholderWrapper();
                $this->stakeholder->init($stakeholder, $this->token_user_id);
            } catch (\Throwable) {
                $this->stakeholder = new WOPIUnknownStakeholder($this->token_user_id);
            }
        }
    }

    /**
     * @return never
     */
    public function handleRequest(): void
    {
        try {
            $this->checkAuth();

            $uri = $this->http->request()->getUri()->getPath();
            $request = substr($uri, strpos($uri, self::WOPI_BASE_URL) + strlen(self::WOPI_BASE_URL));
            $request = explode('/', $request);
            $method = $this->http->request()->getMethod();

            $resource_id = $request[1];
            $action = $request[2] ?? '';

            // check resource_id
            if ($this->token_resource_id !== $resource_id) {
                $this->http->close();
            }

            $resource_id = $this->irss->manage()->find($resource_id);
            if (!$resource_id instanceof ResourceIdentification) {
                $this->http->close();
            }
            $resource = $this->irss->manage()->getResource($resource_id);
            $current_revision = $this->editable ? $resource->getCurrentRevisionIncludingDraft() : $resource->getCurrentRevision();

            $method_override = $this->http->request()->getHeader(self::HEADER_X_WOPI_OVERRIDE)[0] ?? null;
            $is_file_convertion = (bool) ($this->http->request()->getHeader(
                self::HEADER_X_WOPI_FILE_CONVERSION
            )[0] ?? false);

            // GET
            switch ($method_override ?? $method) {
                case 'GET':
                    switch ($action) {
                        case '':
                            // CheckFileInfo
                            $response = new GetFileInfoResponse(
                                $current_revision,
                                $this->token_user_id,
                                $this->editable
                            );
                            $this->http->saveResponse(
                                $this->http->response()->withBody(
                                    Streams::ofString(json_encode($response, JSON_THROW_ON_ERROR))
                                )
                            );

                            break;
                        case 'contents':
                            // GetFile
                            $stream = $this->irss->consume()->stream($resource_id)->setRevisionNumber(
                                $current_revision->getVersionNumber()
                            )->getStream();
                            $this->http->saveResponse(
                                $this->http->response()->withBody($stream)
                            );

                            break;
                    }
                    break;
                case 'PUT_RELATIVE':
                    if (!$is_file_convertion) {
                        throw new \InvalidArgumentException();
                    }
                    // no break
                case 'PUT':
                    switch ($action) {
                        case 'contents':
                            // PutFile
                            $body_stream = $this->http->request()->getBody();
                            $body = $body_stream->getContents();
                            $file_stream = Streams::ofString($body);

                            $draft = true;

                            if ($this->saving_interval > 0) {
                                $latest_revision = $resource->getCurrentRevision();
                                $creation_time = $latest_revision->getInformation()->getCreationDate()->getTimestamp();
                                $current_time = time();
                                $time_diff = $current_time - $creation_time;
                                if ($time_diff > $this->saving_interval) {
                                    $this->irss->manage()->publish($resource_id);
                                }
                            }

                            $new_revision = $this->irss->manage()->appendNewRevisionFromStream(
                                $resource_id,
                                $file_stream,
                                $this->stakeholder,
                                $current_revision->getTitle(),
                                $draft
                            );

                            // CheckFileInfo
                            $response = new GetFileInfoResponse(
                                $new_revision,
                                $this->token_user_id
                            );
                            $this->http->saveResponse(
                                $this->http->response()->withBody(
                                    Streams::ofString(json_encode($response, JSON_THROW_ON_ERROR))
                                )
                            );

                            break;
                        case '': // if we want to create new files in the future, this will be a separate case
                            break;
                    }
                    break;
                case 'POST':
                    switch ($action) {
                        case 'contents':
                            $this->http->saveResponse(
                                $this->http->response()->withBody(
                                    Streams::ofString('')
                                )
                            );

                            break;
                        case '':
                            // Lock
                            $lock = $this->http->request()->getHeader(self::HEADER_X_WOPI_LOCK)[0] ?? null;
                            $this->http->saveResponse(
                                $this->http->response()->withBody(
                                    Streams::ofString('')
                                )
                            );
                    }
                    break;
            }
        } catch (\Throwable $t) {
            $message = $t->getMessage();
            // append simple stacktrace
            $trace = array_map(
                static fn(array $trace): string => $trace['file'] . ':' . $trace['line'],
                $t->getTrace()
            );

            $message .= "\n" . implode("\n", $trace);

            $this->http->saveResponse(
                $this->http->response()
                           ->withBody(Streams::ofString($message))
                           ->withStatus(500)
                           ->withHeader('X-WOPI-ServerError', $t->getMessage())
            );
        }
        $this->http->sendResponse();
        $this->http->close();
    }
}
