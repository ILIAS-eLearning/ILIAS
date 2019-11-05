<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Interface NotificationRenderer
 *
 * Every Notification should have a renderer, if you won't provide on in your
 * TypeInformation, a StandardNotificationRenderer is used.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface NotificationRenderer
{

    /**
     * @param isItem $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component;
}

