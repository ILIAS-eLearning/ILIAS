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
namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotification;
use ILIAS\UI\Component\Item\Notification;

/**
 * Class StandardNotificationGroupRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{
    use Hasher;

    /**
     * @param StandardNotification
     * @return \ILIAS\UI\Component\Item\Notification|mixed
     */
    public function getNotificationComponentForItem(isItem $item) : \ILIAS\UI\Component\Component
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
     * @param Notification $ui_notification_item
     * @param isItem       $item
     * @return Notification
     */
    protected function attachJSCloseEvent(Notification $ui_notification_item, isItem $item) : Notification
    {
        $url = ClientNotifications::NOTIFY_ENDPOINT . "?" . $this->buildCloseQuery($item);

        return $ui_notification_item->withCloseAction($url);
    }
}
