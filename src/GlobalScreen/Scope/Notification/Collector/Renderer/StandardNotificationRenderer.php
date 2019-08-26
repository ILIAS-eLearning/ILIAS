<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\Notification\Factory\canHaveSymbol;
use ILIAS\GlobalScreen\Scope\Notification\Factory\hasActions;
use ILIAS\GlobalScreen\Scope\Notification\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class StandardNotificationRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{

    /**
     * @param isItem|hasActions|canHaveSymbol $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {

        $label = $item instanceof hasTitle ? $item->getTitle() : "";

        $action = $item instanceof hasActions ? $item->getAction() : "#";

        return $this->ui_factory->button()->bulky($this->getStandardSymbol($item), $label, $action);
    }
}
