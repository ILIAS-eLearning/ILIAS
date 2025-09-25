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

namespace ILIAS\Notifications\Provider;

use ILIAS\Notifications\ilNotificationPushHandler;
use ILIAS\Notifications\Interfaces\PushProviderInterface;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationObject;
use ILIAS\Notifications\Model\Push\PushQueueResult;
use ilLanguage;
use ilObjUser;

abstract class NotificationsPushProvider implements PushProviderInterface
{
    protected const int TTL = 60;
    protected ilNotificationPushHandler $handler;
    protected ilNotificationConfig $config;

    final public function __construct(?ilNotificationPushHandler $handler = null)
    {
        $this->handler = $handler ?? new ilNotificationPushHandler();
        $this->config = new ilNotificationConfig('push');
        $this->config->setHandlerParam('setting.user_pref', get_class($this));
        $this->config->setHandlerParam('setting.ttl', (string) $this::TTL);
    }

    abstract public function getName(ilLanguage $lng): string;
    abstract public function getDescription(ilLanguage $lng): string;

    public function push(ilObjUser $user, string $title, string $description = '', ilNotificationLink $link = null): bool
    {
        $notification = new ilNotificationObject($this->config, $user);
        $notification->title = $title;
        $notification->shortDescription = $description;
        $notification->links = $link ? [$link] : [];
        $this->handler->notify($notification);
        return $this->handler->getLastQueueResult() === PushQueueResult::SUCCEEDED;
    }

}
