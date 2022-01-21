<?php /** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Scope\Notification;

use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use ILIAS\GlobalScreen\SingletonTrait;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class NotificationServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationServices
{
    use SingletonTrait;
    
    /**
     * @return NotificationFactory
     */
    public function factory() : NotificationFactory
    {
        return $this->get(NotificationFactory::class);
    }
}
