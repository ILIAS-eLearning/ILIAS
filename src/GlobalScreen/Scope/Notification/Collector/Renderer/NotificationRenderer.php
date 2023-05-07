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

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Notification as NotificationSlate;
use ILIAS\UI\Component\Item\Notification as NotificationItem;
use ILIAS\UI\Implementation\Component\MainControls\SystemInfo;

/**
 * Interface NotificationRenderer
 * Every Notification should have a renderer, if you won't provide on in your
 * TypeInformation, a StandardNotificationRenderer is used.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface NotificationRenderer
{
    /**
     * Returns the UI Component for the past item
     * @param isItem $item
     * @return NotificationItem|NotificationSlate|SystemInfo
     */
    public function getNotificationComponentForItem(isItem $item) : Component;
}
