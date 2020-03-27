<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\Notification\Factory\canHaveSymbol;
use ILIAS\GlobalScreen\Scope\Notification\Factory\hasActions;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\UI\Component\MainControls\Slate\Notification;

/**
 * Class StandardNotificationGroupRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationGroupRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{

    /**
     * @param isItem|canHaveSymbol $item
     *
     * @return Notification
     */
    public function getNotificationComponentForItem(isItem $item)
    {
        if (!$item instanceof StandardNotificationGroup) {
            throw new \LogicException("item is not a StandardNotificationGroup");
        }

        $slate = $this->ui_factory->mainControls()->slate()->notification($item->getTitle(), []);
        foreach ($item->getNotifications() as $standard_notification) {
            $slate = $slate->withAdditionalEntry($standard_notification->getRenderer($this->ui_factory)
                                                                       ->getNotificationComponentForItem($standard_notification));
        }

        return $slate;
    }
}
