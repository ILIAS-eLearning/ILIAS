<?php

declare(strict_types=1);

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

namespace ILIAS\Notifications;

use ILIAS\Notifications\Model\ilNotificationObject;
use ILIAS\Notifications\Model\OSD\ilOSDNotificationObject;
use ILIAS\Notifications\Repository\ilNotificationOSDRepository;
use ILIAS\Data\Clock\ClockInterface;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationOSDHandler extends ilNotificationHandler
{
    private ilNotificationOSDRepository $repo;
    private ClockInterface $clock;

    public function __construct(?ilNotificationOSDRepository $repo = null, ?ClockInterface $clock = null)
    {
        if ($repo === null) {
            $repo = new ilNotificationOSDRepository();
        }
        $this->repo = $repo;

        if ($clock === null) {
            $clock = (new \ILIAS\Data\Factory())->clock()->utc();
        }
        $this->clock = $clock;
    }

    public function notify(ilNotificationObject $notification): void
    {
        $this->repo->createOSDNotification($notification->user->getId(), $notification);
    }

    /**
     * @return ilOSDNotificationObject[]
     */
    public function getNotificationsForUser(int $user_id, bool $append_osd_id_to_link = true, int $max_age_seconds = 0, string $type = ''): array
    {
        $notifications = $this->repo->getOSDNotificationsByUser($user_id, $max_age_seconds, $type);

        foreach ($notifications as $notification) {
            if ($append_osd_id_to_link) {
                foreach ($notification->getObject()->links as $link) {
                    $link->setUrl($this->appendParamToLink($link->getUrl(), 'osd_id', $notification->getId()));
                }
            }
        }

        return $notifications;
    }

    public function deleteStaleNotificationsForUserAndType(int $user_id, string $type): void
    {
        $this->repo->deleteStaleNotificationsForUserAndType($user_id, $type, $this->clock->now()->getTimestamp());
    }

    public function removeNotification(int $notification_osd_id): bool
    {
        return $this->repo->deleteOSDNotificationById($notification_osd_id);
    }

    private function appendParamToLink(string $link, string $param, int $value): string
    {
        if (strpos($link, '?') !== false) {
            $link .= '&' . $param . '=' . $value;
        } else {
            $link .= '?' . $param . '=' . $value;
        }
        return $link;
    }
}
