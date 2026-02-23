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

use ILIAS\HTTP\StatusCode;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Data\URI;
use ILIAS\Data\Factory;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\components\OrgUnit\ARHelper\DIC;

/**
 * Class ilBTControllerGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTControllerGUI implements ilCtrlBaseClassInterface, ilCtrlSecurityInterface
{
    use DIC;
    public const FROM_URL = 'from_url';
    public const OBSERVER_ID = 'observer_id';
    public const SELECTED_OPTION = 'selected_option';
    public const CMD_ABORT = 'abortBucket';
    public const CMD_REMOVE = 'abortBucket';
    public const CMD_USER_INTERACTION = 'userInteraction';
    public const IS_ASYNC = 'bt_task_is_async';
    public const CMD_GET_REPLACEMENT_ITEM = "getAsyncReplacementItem";

    public function executeCommand(): void
    {
        $cmd = $this->ctrl()->getCmd();
        switch ($cmd) {
            case self::CMD_GET_REPLACEMENT_ITEM:
                $this->getAsyncReplacementItem();
                break;
            case self::CMD_USER_INTERACTION:
                $this->userInteraction();
                break;
            case self::CMD_ABORT:
            case self::CMD_REMOVE:
                $this->abortBucket();
                break;
            default:
                break;
        }
    }

    public function getUnsafeGetCommands(): array
    {
        return array_unique([
            self::CMD_ABORT,
            self::CMD_REMOVE,
            self::CMD_USER_INTERACTION,
        ]);
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    protected function userInteraction(): void
    {
        $observer_id = $this->retrieveObserverIdFromRequest();
        $selected_option = $this->retrieveSelectedInteractionOption();
        if ($observer_id === null || $selected_option === null) {
            $this->respondWithError(StatusCode::HTTP_BAD_REQUEST, 'Bad Request');
        }

        $bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

        $this->enforceBucketBelongsToCurrentUser($bucket);

        $this->dic()->backgroundTasks()->taskManager()->continueTask(
            $bucket,
            new UserInteractionOption('', $selected_option)
        );

        $this->redirectToCallerOrClose();
    }


    protected function abortBucket(): void
    {
        $observer_id = $this->retrieveObserverIdFromRequest();
        if ($observer_id === null) {
            $this->respondWithError(StatusCode::HTTP_BAD_REQUEST, 'Bad Request');
        }

        $bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

        $this->enforceBucketBelongsToCurrentUser($bucket);

        $this->dic()->backgroundTasks()->taskManager()->quitBucket($bucket);

        $this->redirectToCallerOrClose();
    }


    /**
     * Loads one single aggregate notification item representing a button async
     * to replace an existing one.
     */
    protected function getAsyncReplacementItem(): void
    {
        $observer_id = $this->retrieveObserverIdFromRequest();
        if ($observer_id === null) {
            $this->respondWithError(StatusCode::HTTP_BAD_REQUEST, 'Bad Request');
        }

        $bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

        $this->enforceBucketBelongsToCurrentUser($bucket);

        $item_source = new ilBTPopOverGUI($this->dic());
        $this->dic()->language()->loadLanguageModule('background_tasks');
        $item = $item_source->getItemForObserver($bucket);

        $this->sendSuccessResponse(
            Streams::ofString(
                $this->dic()->ui()->renderer()->renderAsync($item)
            )
        );
    }


    private function getFromURL(): URI
    {
        $uri = (new Factory())->uri($this->defaultReturnUrl());

        $decoded_from_url = $this->retrieveFromUrlFromRequest();
        if ($decoded_from_url === null || $decoded_from_url === '') {
            return $uri;
        }

        $from_url = self::unhash($decoded_from_url);
        if ($from_url === false) {
            return $uri;
        }

        $from_url_parts = parse_url($from_url);
        if (!is_array($from_url_parts)) {
            return $uri;
        }

        $uri = $uri->withPath(null)->withQuery(null)->withFragment(null);

        $mutators = [
            'path' => static fn(URI $u, string $v): URI => $u->withPath($v),
            'query' => static fn(URI $u, string $v): URI => $u->withQuery($v),
            'fragment' => static fn(URI $u, string $v): URI => $u->withFragment($v),
        ];

        foreach ($mutators as $key => $apply) {
            $value = $from_url_parts[$key] ?? null;
            if (is_string($value) && $value !== '') {
                $uri = $apply($uri, $value);
            }
        }

        return $uri;
    }

    public static function hash(string $url): string
    {
        return base64_encode((string) $url);
    }

    public static function unhash(string $url): string|false
    {
        return base64_decode((string) $url);
    }

    private function enforceBucketBelongsToCurrentUser(Bucket $bucket): void
    {
        if ($bucket->getUserId() !== $this->user()->getId()) {
            $this->respondWithError(StatusCode::HTTP_FORBIDDEN, 'Forbidden');
        }
    }

    private function defaultReturnUrl(): string
    {
        return ilUtil::_getHttpPath();
    }

    private function sendSuccessResponse(Stream $stream): void
    {
        $response = $this->http()->response()
                                 ->withStatus(StatusCode::HTTP_OK, 'OK')
                                 ->withBody($stream);
        $this->http()->saveResponse($response);

        if ($this->wasInvokedAsynchronously()) {
            $this->http()->sendResponse();
            $this->http()->close();
        }
    }

    private function redirectToCallerOrClose(): never
    {
        if (!$this->wasInvokedAsynchronously()) {
            $this->ctrl()->redirectToURL((string) $this->getFromURL());
        }

        $this->http()->close();
    }

    private function respondWithError(int $error_status_code, string $message): never
    {
        $response = $this->http()->response()->withStatus($error_status_code, $message);
        if ($error_status_code === StatusCode::HTTP_FORBIDDEN && !$this->wasInvokedAsynchronously()) {
            $this->tpl()->setOnScreenMessage(
                $this->tpl()::MESSAGE_TYPE_FAILURE,
                $this->lng()->txt('permission_denied'),
                true
            );
            $response = $response
                ->withStatus(StatusCode::HTTP_FOUND, 'Found')
                ->withHeader(ResponseHeader::LOCATION, $this->defaultReturnUrl());
        }

        $this->http()->saveResponse($response);
        $this->http()->sendResponse();
        $this->http()->close();
    }

    private function retrieveSelectedInteractionOption(): ?string
    {
        return $this->http()->wrapper()->query()->retrieve(
            self::SELECTED_OPTION,
            $this->dic()->refinery()->byTrying([
                $this->dic()->refinery()->kindlyTo()->string(),
                $this->dic()->refinery()->always(null)
            ])
        );
    }

    private function wasInvokedAsynchronously(): bool
    {
        return $this->http()->wrapper()->query()->retrieve(
            self::IS_ASYNC,
            $this->dic()->refinery()->byTrying([
                $this->dic()->refinery()->kindlyTo()->bool(),
                $this->dic()->refinery()->always(false)
            ])
        );
    }

    private function retrieveObserverIdFromRequest(): ?int
    {
        return $this->http()->wrapper()->query()->retrieve(
            self::OBSERVER_ID,
            $this->dic()->refinery()->byTrying([
                $this->dic()->refinery()->kindlyTo()->int(),
                $this->dic()->refinery()->always(null)
            ])
        );
    }

    private function retrieveFromUrlFromRequest(): ?string
    {
        return $this->http()->wrapper()->query()->retrieve(
            self::FROM_URL,
            $this->dic()->refinery()->byTrying([
                $this->dic()->refinery()->kindlyTo()->string(),
                $this->dic()->refinery()->always(null)
            ])
        );
    }
}
