<?php
namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;

/**
 * Class Notifications
 * Handles Async Calls for the Notification Center
 *
 * @package ILIAS\GlobalScreen\Client
 */
class Notifications
{
    /**
     * Collected set of collected notifications
     *
     * @var StandardNotificationGroup[]
     */
    protected $notification_groups;

    /**
     * Name of the GET param used in the async calls
     */
    const MODE = "mode";

    /**
     * Value of the MODE GET param, if the Notification Center has been opened
     */
    const MODE_OPENED = "opened";

    /**
     * Value of the MODE GET param, if the Notification Center has been closed
     */
    const MODE_CLOSED = "closed";

    /**
     * NAME of the GET param, to indicate the item ID of the closed item
     */
    const ITEM_ID = "item_id";

    /**
     * Used to read the identifiers out of the GET param later
     */
    const NOTIFICATION_IDENTIFIERS = "notification_identifiers";

    /**
     * Location of the endpoint handling async notification requests
     */
    const NOTIFY_ENDPOINT = ILIAS_HTTP_PATH."/src/GlobalScreen/Client/notify.php";

    public function run()
    {
        /**
         * @DI $DI
         */
        global $DIC;
        $this->notification_groups = $DIC->globalScreen()->collector()->notifications()->getNotifications();

        if($_GET[self::MODE] == self::MODE_OPENED){
            $this->handleOpened();
        }else{
            $this->handleClosed();
        }
    }

    /**
     * Loops through all available open callable provided by the notification
     * providers
     */
    public function handleOpened(){
        $identifiers = $_GET[self::NOTIFICATION_IDENTIFIERS];

        foreach ($this->notification_groups as $notification_group) {
            foreach ($notification_group->getNotifications() as $notification) {
                if (in_array($notification->getProviderIdentification()->getInternalIdentifier(), $identifiers)) {
                    $notification->getOpenedCallable()();
                }
            }
            if (in_array($notification_group->getProviderIdentification()->getInternalIdentifier(), $identifiers)) {
                $notification_group->getOpenedCallable()();
            }
        }
    }

    /**
     * Runs the closed callable if such a callable is provided
     */
    public function handleClosed(){
        $id = $_GET[self::ITEM_ID];
        foreach ($this->notification_groups as $notification_group) {
            foreach ($notification_group->getNotifications() as $notification) {
                if($id == $notification->getProviderIdentification()->getInternalIdentifier()) {
                    if($notification->hasClosedCallable()){
                        $notification->getClosedCallable()();
                    }
                }
            }
        }
    }
}