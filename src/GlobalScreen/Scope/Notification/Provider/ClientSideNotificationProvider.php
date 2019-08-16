<?php namespace ILIAS\GlobalScreen\Scope\Notification\Provider;

use ILIAS\GlobalScreen\Client\ClientSideProvider;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;

/**
 * Interface ClientSideNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ClientSideNotificationProvider extends ClientSideProvider
{

    /**
     * @param isItem $notification
     *
     * @return isItem
     */
    public function enrichItem(isItem $notification) : isItem;
}
