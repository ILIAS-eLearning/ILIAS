<?php declare(strict_types=1);

namespace ILIAS\Notifications;

use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationObject;
use ilLanguage;

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationOSDRepository
{
    private static ?self $instance = null;
    private ilDBInterface $database;

    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct(?ilDBInterface $database = null)
    {
        if ($database === null) {
            global $DIC;
            $database = $DIC->database();
        }
        $this->database = $database;
    }

    public function addNotification(ilNotificationObject $notification) : int
    {
        $id = $this->database->nextId(ilNotificationSetupHelper::$tbl_notification_osd_handler);

        $affected = $this->database->insert(
            ilNotificationSetupHelper::$tbl_notification_osd_handler,
            [
                'notification_osd_id' => ['integer', $id],
                'usr_id' => ['integer', $notification->user->getId()],
                'serialized' => ['text', serialize($notification)],
                'valid_until' => ['integer', $notification->baseNotification->getValidForSeconds() ? ($notification->baseNotification->getValidForSeconds() + time()) : 0],
                'visible_for' => ['integer', $notification->baseNotification->getVisibleForSeconds() ?? 0],
                'type' => ['text', $notification->baseNotification->getType()],
                'time_added' => ['integer', time()],
            ]
        );

        return ($affected === 1) ? $id : 0;
    }

    public function getNotifications(int $user_id, int $max_age_seconds = 0, string $type = '') : array
    {
        $now = time();
        if ($max_age_seconds === 0) {
            $max_age_seconds = $now;
        }
        $query =
            'SELECT notification_osd_id, serialized, valid_until, time_added, visible_for, type' .
            ' FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler .
            ' WHERE usr_id = %s AND (valid_until = 0 OR valid_until > %s) AND time_added > %s';

        $types = ['integer', 'integer', 'integer'];
        $values = [$user_id, $now, $now - $max_age_seconds];

        if ($type !== '') {
            $query .= ' AND type = %s';
            $types[] = 'text';
            $values[] = $type;
        }

        $rset = $this->database->queryF($query, $types, $values);
        $notifications = [];

        while ($row = $this->database->fetchAssoc($rset)) {
            $row['data'] = unserialize($row['serialized'], ['allowed_classes' => [ilNotificationObject::class, ilNotificationLink::class]]);
            unset($row['serialized']);
            if (isset($row['data']->handlerParams['']) && isset($row['data']->handlerParams['osd'])) {
                $row['data']->handlerParams = ['general' => $row['data']->handlerParams[''], 'osd' => $row['data']->handlerParams['osd']];
            }
            $row['notification_osd_id'] = (int) $row['notification_osd_id'];
            $notifications[] = $row;
        }

        return $notifications;
    }

    public function removeNotification(int $notification_osd_id) : bool
    {
        $query = 'SELECT usr_id FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE notification_osd_id = %s';
        $rset = $this->database->queryF($query, ['integer'], [$notification_osd_id]);

        if ($row = $this->database->fetchAssoc($rset)) {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE notification_osd_id = %s';
            return 1 === $this->database->manipulateF($query, ['integer'], [$notification_osd_id]);
        }

        return false;
    }
}
