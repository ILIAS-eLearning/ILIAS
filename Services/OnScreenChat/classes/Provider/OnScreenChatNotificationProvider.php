<?php declare(strict_types=1);

namespace ILIAS\OnScreenChat\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class OnScreenChatNotificationProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class OnScreenChatNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications(): array
    {
        return [];
    }
}
