<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Client\Notifications as ClientNotifications;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Notification\Factory\AdministrativeNotification;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class AdministrativeNotificationRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class AdministrativeNotificationRenderer extends AbstractBaseNotificationRenderer implements NotificationRenderer
{
    use Hasher;
    
    /**
     * @inheritDoc
     */
    public function getNotificationComponentForItem(isItem $item) : \ILIAS\UI\Component\Component
    {
        /**
         * @var $item AdministrativeNotification
         */
        $system_info = $this->ui_factory->mainControls()->systemInfo($item->getTitle(), $item->getSummary())->withDenotation($item->getDenotation());
        
        if ($item->hasClosedCallable()) {
            $url         = ClientNotifications::NOTIFY_ENDPOINT . "?" . $this->buildCloseQuery($item);
            $system_info = $system_info->withDismissAction(new URI($url));
        }
        
        return $system_info;
    }
    
}
