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

use ILIAS\GlobalScreen\Scope\Notification\Factory\canHaveSymbol;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\UI\Component\MainControls\Slate\Notification;

/**
 * Class StandardNotificationGroupRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationGroupRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{
    /**
     * @param isItem|canHaveSymbol $item
     * @return Notification
     */
    public function getNotificationComponentForItem(isItem $item) : \ILIAS\UI\Component\Component
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
