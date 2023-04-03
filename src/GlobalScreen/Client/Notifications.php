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

namespace ILIAS\GlobalScreen\Client;

use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use JsonException;

/**
 * Class Notifications
 * Handles Async Calls for the Notification Center
 * @package ILIAS\GlobalScreen\Client
 */
class Notifications
{
    use Hasher;

    public const ADDITIONAL_ACTION = 'additional_action';
    /**
     * @var mixed|null
     */
    private $additional_action;
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;
    /**
     * Collected set of collected notifications
     * @var mixed[]
     */
    protected $notification_groups;
    /**
     * Name of the GET param used in the async calls
     */
    public const MODE = "mode";
    /**
     * Value of the MODE GET param, if the Notification Center has been opened
     */
    public const MODE_OPENED = "opened";
    /**
     * Value of the MODE GET param, if the Notification Center has been closed
     */
    public const MODE_CLOSED = "closed";
    /**
     * Value of the MODE GET param, if the Notification Center should be rerendered
     */
    public const MODE_RERENDER = "rerender";
    /**
     * NAME of the GET param, to indicate the item ID of the closed item
     */
    public const ITEM_ID = "item_id";
    /**
     * Used to read the identifiers out of the GET param later
     */
    public const NOTIFICATION_IDENTIFIERS = "notification_identifiers";
    /**
     * Location of the endpoint handling async notification requests
     */
    public const NOTIFY_ENDPOINT = "src/GlobalScreen/Client/notify.php";
    /**
     * @var mixed[]
     */
    protected $identifiers_to_handle = [];
    /**
     * @var string|null
     */
    protected $single_identifier_to_handle;
    /**
     * @var mixed[]
     */
    protected $administrative_notifications = [];

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }

    public function run() : void
    {
        $this->notification_groups = $this->dic->globalScreen()->collector()->notifications()->getNotifications();
        $this->administrative_notifications = $this->dic->globalScreen()->collector()->notifications(
        )->getAdministrativeNotifications();
        $this->identifiers_to_handle = $this->dic->http()->request()->getQueryParams()[self::NOTIFICATION_IDENTIFIERS] ?? [];
        $this->single_identifier_to_handle = $this->dic->http()->request()->getQueryParams()[self::ITEM_ID] ?? null;

        switch ($this->dic->http()->request()->getQueryParams()[self::MODE] ?? 'none') {
            case self::MODE_OPENED:
                $this->handleOpened();
                break;
            case self::MODE_CLOSED:
                $this->handleClosed();
                break;
            case self::MODE_RERENDER:
                $this->handleRerender();
                break;

        }
    }

    /**
     * Loops through all available open callable provided by the notification
     * providers
     */
    private function handleOpened() : void
    {
        foreach ($this->notification_groups as $notification_group) {
            foreach ($notification_group->getNotifications() as $notification) {
                if (in_array(
                    $this->hash($notification->getProviderIdentification()->serialize()),
                    $this->identifiers_to_handle,
                    true
                )) {
                    $notification->getOpenedCallable()();
                }
            }
            if (in_array(
                $this->hash($notification_group->getProviderIdentification()->serialize()),
                $this->identifiers_to_handle,
                true
            )) {
                $notification_group->getOpenedCallable()();
            }
        }
    }

    /**
     * Runs the closed callable if such a callable is provided
     */
    private function handleClosed() : void
    {
        foreach ($this->notification_groups as $notification_group) {
            foreach ($notification_group->getNotifications() as $notification) {
                if ($this->single_identifier_to_handle !== $this->hash(
                    $notification->getProviderIdentification()->serialize()
                )) {
                    continue;
                }
                if (!$notification->hasClosedCallable()) {
                    continue;
                }
                $notification->getClosedCallable()();
            }
        }
        foreach ($this->administrative_notifications as $administrative_notification) {
            if ($this->single_identifier_to_handle !== $this->hash(
                $administrative_notification->getProviderIdentification()->serialize()
            )) {
                continue;
            }
            if (!$administrative_notification->hasClosedCallable()) {
                continue;
            }
            $administrative_notification->getClosedCallable()();
        }
    }

    /**
     * @throws ResponseSendingException
     * @throws JsonException
     */
    private function handleRerender() : void
    {
        $notifications = [];
        $amount = 0;
        foreach ($this->notification_groups as $group) {
            $notifications[] = $group->getRenderer($this->dic->ui()->factory())->getNotificationComponentForItem(
                $group
            );
            if ($group->getNewNotificationsCount() > 0) {
                $amount++;
            }
        }
        $this->dic->http()->saveResponse(
            $this->dic->http()->response()
                      ->withBody(
                          Streams::ofString(
                              json_encode([
                                  'html' => $this->dic->ui()->renderer()->renderAsync($notifications),
                                  'symbol' => $this->dic->ui()->renderer()->render(
                                      $this->dic->ui()->factory()->symbol()->glyph()->notification()->withCounter(
                                          $this->dic->ui()->factory()->counter()->novelty($amount)
                                      )
                                  )
                              ])
                          )
                      )
                      ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
        );
        $this->dic->http()->sendResponse();
        $this->dic->http()->close();
    }
}
