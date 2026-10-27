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

namespace ILIAS\Forum\Notification;

final readonly class ForumNotificationMembershipRow
{
    public int $notification_id;

    public int $user_id;

    public int $frm_id;

    public int $thread_id;

    public bool $container_enforces_noti;

    public bool $member_may_disable_noti;

    public int $interested_events;

    public int $user_id_noti;

    public bool $user_deactivated_noti;

    /**
     * @param array<string, mixed> $row frm_notification fetchAssoc result
     */
    public function __construct(array $row)
    {
        $this->notification_id = (int) $row['notification_id'];
        $this->user_id = (int) $row['user_id'];
        $this->frm_id = (int) $row['frm_id'];
        $this->thread_id = (int) $row['thread_id'];
        $this->container_enforces_noti = (bool) (int) $row['container_enforces_noti'];
        $this->member_may_disable_noti = (bool) (int) $row['member_may_disable_noti'];
        $this->interested_events = (int) $row['interested_events'];
        $this->user_id_noti = (int) $row['user_id_noti'];
        $this->user_deactivated_noti = (bool) (int) ($row['user_deactivated_noti'] ?? 0);
    }
}
