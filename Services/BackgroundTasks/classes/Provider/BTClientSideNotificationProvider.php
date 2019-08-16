<?php namespace ILIAS\BackgroundTasks\Provider;

use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractClientSideNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\ClientSideNotificationProvider;

/**
 * Class BTClientSideNotificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BTClientSideNotificationProvider extends AbstractClientSideNotificationProvider implements ClientSideNotificationProvider
{

    /**
     * @inheritDoc
     */
    public function getClientSideProviderName() : string
    {
        return "BTClientSideNotificationProvider";
    }


    /**
     * @inheritDoc
     */
    public function enrichItem(isItem $notification) : isItem
    {
        // here you can "fill" your notification server side

        return $notification;
    }
}
