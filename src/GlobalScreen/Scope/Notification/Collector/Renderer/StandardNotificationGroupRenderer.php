<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\UI\Component\Component;

/**
 * Class StandardNotificationGroupRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationGroupRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{

    use isSupportedTrait;


    /**
     * @param isItem $item
     *
     * @return Component
     * @throws \Exception
     */
    public function getComponentForItem(isItem $item) : Component
    {
        if (!$item instanceof StandardNotificationGroup) {
            throw new \LogicException("item is not a StandardNotificationGroup");
        }
        /**
         * @var $item StandardNotificationGroup
         */

        $slate = $this->ui_factory->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));

        foreach ($item->getNotifications() as $standard_notification) {
            $component = $standard_notification->getRenderer()->getComponentForItem($standard_notification);
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $slate = $slate->withAdditionalEntry($component);
            }
        }

        return $slate;
    }
}
