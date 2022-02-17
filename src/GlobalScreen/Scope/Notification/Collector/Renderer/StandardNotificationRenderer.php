<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotification;
use ILIAS\UI\Component\Item\Notification;

/**
 * Class StandardNotificationGroupRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{
    use Hasher;


    /**
     * @param StandardNotification
     *
     * @return Notification
     */
    public function getNotificationComponentForItem(isItem $item)
    {
        $ui_notification_item = $item->getNotificationItem();

        if ($item->hasClosedCallable()) {
            return $this->attachJSCloseEvent($ui_notification_item, $item);
        }

        return $ui_notification_item;
    }


    /**
     * Attaches on load code for communicating back, that the notification has
     * been closed.
     *
     * @param Notification $ui_notification_item
     * @param isItem       $item
     *
     * @return Notification
     */
    protected function attachJSCloseEvent(Notification $ui_notification_item, isItem $item) : Notification
    {
        $url = ClientNotifications::NOTIFY_ENDPOINT . "?" . $this->buildCloseQuery($item);

        return $ui_notification_item->withCloseAction($url);
    }
}
