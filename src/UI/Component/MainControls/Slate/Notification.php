<?php
/* Copyright (c) 2019 Timon Amstutz Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls\Slate;

use \ILIAS\UI\Component\Item\Notification as NotificationItem;

/**
 * Notifications Slates are Slates restricted to only containing Notification Items
 */
interface Notification extends Slate
{
    /**
     * Get a Notification Slate like this, but with one additional Notification Item entry.
     */
    public function withAdditionalEntry(NotificationItem $entry) : Notification;
}