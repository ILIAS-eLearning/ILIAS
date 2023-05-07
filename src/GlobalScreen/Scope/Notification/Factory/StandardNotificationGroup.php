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
namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\NotificationRenderer;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationGroupRenderer;
use ILIAS\UI\Factory as UIFactory;

/**
 * Class StandardNotificationGroup
 * Groups a set of Notification.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardNotificationGroup extends AbstractBaseNotification implements isStandardItem
{
    /**
     * @var StandardNotification[]
     */
    private $notifications = [];

    /**
     * @var string
     */
    protected $title = "";

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    public function addNotification(StandardNotification $notification) : self
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * @return StandardNotification[]
     */
    public function getNotifications() : array
    {
        return $this->notifications;
    }

    /**
     * @return int
     */
    public function getNotificationsCount() : int
    {
        return count($this->notifications);
    }

    /**
     * @return int
     */
    public function getOldNotificationsCount() : int
    {
        $count = 0;
        foreach ($this->notifications as $notification) {
            $count += $notification->getOldAmount();
        }
        return $count;
    }

    /**
     * @return int
     */
    public function getNewNotificationsCount() : int
    {
        $count = 0;
        foreach ($this->notifications as $notification) {
            $count += $notification->getNewAmount();
        }
        return $count;
    }

    /**
     * @inheritDoc
     */
    public function getRenderer(UIFactory $factory) : NotificationRenderer
    {
        return new StandardNotificationGroupRenderer($factory);
    }
}
