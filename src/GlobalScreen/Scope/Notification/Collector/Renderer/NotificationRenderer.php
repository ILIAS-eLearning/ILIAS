<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\UI\Component\MainControls\Slate\Notification as NotificationSlate;
use ILIAS\UI\Component\Item\Notification as NotificationItem;
use ILIAS\UI\Implementation\Component\MainControls\SystemInfo;

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
     * Returns the UI Component for the past item
     *
     * @param isItem $item
     * @return NotificationItem|NotificationSlate|SystemInfo
     */
    public function getNotificationComponentForItem(isItem $item);
}
