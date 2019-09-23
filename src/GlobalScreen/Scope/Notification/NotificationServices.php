<?php namespace ILIAS\GlobalScreen\Scope\Notification;

use ILIAS\GlobalScreen\Scope\Notification\Factory\NotificationFactory;
use ILIAS\GlobalScreen\SingletonTrait;

/**
 * Class NotificationServices
 *
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
