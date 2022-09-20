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

namespace ILIAS\Notifications\Repository;

use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\Notifications\ilNotificationSetupHelper;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationObject;
use ILIAS\Notifications\Model\OSD\ilOSDNotificationObject;
use ilLanguage;
use ilDBConstants;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationOSDRepository implements ilNotificationOSDRepositoryInterface
{
    private const UNIQUE_TYPES = [
        'who_is_online'
    ];
    private ilDBInterface $database;

    public function __construct(?ilDBInterface $database = null)
    {
        if ($database === null) {
            global $DIC;
            $database = $DIC->database();
        }
        $this->database = $database;
    }

    public function createOSDNotification(int $user_id, ilNotificationObject $object): ?ilOSDNotificationObject
    {
        $id = $this->database->nextId(ilNotificationSetupHelper::$tbl_notification_osd_handler);
        $base = $object->baseNotification;
        $now = time();

        $notification = new ilOSDNotificationObject(
            $id,
            $user_id,
            $object,
            $now,
            $base->getValidForSeconds() ? $base->getValidForSeconds() + $now : 0,
            $base->getVisibleForSeconds(),
            $base->getType()
        );

        if (in_array($notification->getType(), self::UNIQUE_TYPES)) {
            $this->deleteOSDNotificationByUserAndType($user_id, $notification->getType());
        }

        $affected = $this->database->insert(
            ilNotificationSetupHelper::$tbl_notification_osd_handler,
            [
                'notification_osd_id' => [ilDBConstants::T_INTEGER, $notification->getId()],
                'usr_id' => [ilDBConstants::T_INTEGER, $notification->getUser()],
                'serialized' => [ilDBConstants::T_TEXT, serialize($notification->getObject())],
                'valid_until' => [ilDBConstants::T_INTEGER, $notification->getValidUntil()],
                'visible_for' => [ilDBConstants::T_INTEGER, $notification->getVisibleFor()],
                'type' => [ilDBConstants::T_TEXT, $notification->getType()],
                'time_added' => [ilDBConstants::T_INTEGER, $notification->getTimeAdded()],
            ]
        );

        return ($affected === 1) ? $notification : null;
    }

    public function ifOSDNotificationExistsById(int $id): bool
    {
        $query = 'SELECT count(*) AS count FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE notification_osd_id = %s';
        $result = $this->database->queryF($query, [ilDBConstants::T_INTEGER], [$id]);
        $row = $this->database->fetchAssoc($result);
        return ((int) ($row['count'] ?? 0)) === 1;
    }

    /**
     * @return ilOSDNotificationObject[]
     */
    public function getOSDNotificationsByUser(int $user_id, int $max_age_seconds = 0, string $type = ''): array
    {
        $now = time();
        if ($max_age_seconds === 0) {
            $max_age_seconds = $now;
        }
        $query =
            'SELECT * FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler .
            ' WHERE usr_id = %s AND (valid_until = 0 OR valid_until > %s) AND time_added > %s';

        $types = [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER];
        $values = [$user_id, $now, $now - $max_age_seconds];

        if ($type !== '') {
            $query .= ' AND type = %s';
            $types[] = ilDBConstants::T_TEXT;
            $values[] = $type;
        }

        $rset = $this->database->queryF($query, $types, $values);
        $notifications = [];

        while ($row = $this->database->fetchAssoc($rset)) {
            $object = unserialize($row['serialized'], ['allowed_classes' => [ilNotificationObject::class, ilNotificationLink::class]]);
            if (isset($object->handlerParams[''], $object->handlerParams['osd'])) {
                $object->handlerParams = ['general' => $object->handlerParams[''], 'osd' => $object->handlerParams['osd']];
            }
            $notification = new ilOSDNotificationObject(
                (int) $row['notification_osd_id'],
                (int) $row['usr_id'],
                $object,
                (int) $row['time_added'],
                (int) $row['valid_until'],
                (int) $row['visible_for'],
                $row['type']
            );

            $notifications[] = $notification;
        }

        return $notifications;
    }

    public function deleteOSDNotificationById(int $id): bool
    {
        if ($this->ifOSDNotificationExistsById($id)) {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE notification_osd_id = %s';
            return 1 === $this->database->manipulateF($query, [ilDBConstants::T_INTEGER], [$id]);
        }
        return false;
    }

    private function deleteOSDNotificationByUserAndType(int $user_id, string $type): void
    {
        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE usr_id = %s AND type = %s';
        $this->database->manipulateF(
            $query,
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT],
            [$user_id, $type]
        );
    }

    public function deleteStaleNotificationsForUserAndType(int $user_id, string $type, int $until_timestamp): void
    {
        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE usr_id = %s AND type = %s AND time_added < %s';
        $this->database->manipulateF(
            $query,
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
            [$user_id, $type, $until_timestamp]
        );
    }
}
