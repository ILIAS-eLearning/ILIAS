<?php

require_once 'Services/Notifications/classes/class.ilNotificationSetupHelper.php';
require_once 'Services/Notifications/classes/class.ilNotificationEchoHandler.php';

/**
 * Notification handler for senden a notification popup to the recipients
 * browser
 */
class ilNotificationOSDHandler extends ilNotificationEchoHandler
{
    public function notify(ilNotificationObject $notification)
    {
        global $ilDB;

        $id = $ilDB->nextId(ilNotificationSetupHelper::$tbl_notification_osd_handler);

        $ilDB->insert(
            ilNotificationSetupHelper::$tbl_notification_osd_handler,
            array(
                    'notification_osd_id' => array('integer', $id),
                    'usr_id' => array('integer', $notification->user->getId()),
                    'serialized' => array('text', serialize($notification)),
                    'valid_until' => array('integer', $notification->baseNotification->getValidForSeconds() ? ($notification->baseNotification->getValidForSeconds() + time()) : 0),
                    'visible_for' => array('integer', $notification->baseNotification->getVisibleForSeconds() ? $notification->baseNotification->getVisibleForSeconds() : 0),
                    'type' => array('text', $notification->baseNotification->getType()),
                    'time_added' => array('integer', time()),
                )
        );
    }

    public function showSettings($item)
    {
        global $lng;
        $txt = new ilTextInputGUI($lng->txt('polling_intervall'), 'osd_polling_intervall');
        $txt->setRequired(true);
        $txt->setInfo($lng->txt('polling_in_seconds'));
        $txt->setValue('300');

        $item->addSubItem($txt);

        return array('osd_polling_intervall');
    }

    public static function getNotificationsForUser($user_id, $append_osd_id_to_link = true, $max_age_seconds = 0)
    {
        global $ilDB;

        $query = 'SELECT notification_osd_id, serialized, valid_until, visible_for, type FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler
            . ' WHERE usr_id = %s AND (valid_until = 0 OR valid_until > ' . $ilDB->quote(time(), 'integer') . ') AND time_added > %s';

        $types = array('integer', 'integer');
        $values = array($user_id, $max_age_seconds ? (time() - $max_age_seconds) : 0);

        $rset = $ilDB->queryF($query, $types, $values);
        $notifications = array();

        while ($row = $ilDB->fetchAssoc($rset)) {
            $row['data'] = unserialize($row['serialized']);
            unset($row['serialized']);

            $row['data']->handlerParams = array('general' => $row['data']->handlerParams[''], 'osd' => $row['data']->handlerParams['osd']);

            if ($append_osd_id_to_link) {
                if ($row['data']->link) {
                    $row['data']->link = self::appendParamToLink($row['data']->link, 'osd_id', $row['notification_osd_id']);
                }

                $row['data']->shortDescription = self::appendOsdIdToLinks($row['data']->shortDescription, $row['notification_osd_id']);
                $row['data']->longDescription = self::appendOsdIdToLinks($row['data']->longDescription, $row['notification_osd_id']);
            }
            $notifications[] = $row;
        }

        self::cleanupOnRandom();

        return $notifications;
    }

    private static function appendOsdIdToLinks($subject, $osd_id)
    {
        $matches = array();
        preg_match_all('/href="(.*?)"/', $subject, $matches);
        if ($matches[1]) {
            foreach ($matches[1] as $match) {
                $match_appended = self::appendParamToLink($match, 'osd_id', $osd_id);
                $subject = str_replace($match, $match_appended, $subject);
            }
        }
        return $subject;
    }

    /**
     * Removes a notifcation and triggers a follow up notification to remove
     * the notification from the browser view of the original recipient
     *
     * @global ilDB $ilDB
     * @param integer $notification_osd_id
     */
    public static function removeNotification($notification_osd_id)
    {
        global $ilDB;

        $query = 'SELECT usr_id FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE notification_osd_id = %s';
        $types = array('integer');
        $values = array($notification_osd_id);

        $rset = $ilDB->queryF($query, $types, $values);

        if ($row = $ilDB->fetchAssoc($rset)) {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE notification_osd_id = %s';
            $types = array('integer');
            $values = array($notification_osd_id);

            $ilDB->manipulateF($query, $types, $values);

            // sends a "delete the given notification" notification using the
            // osd_maint channel
            /*$deletedNotification = new ilNotificationConfig('osd_maint');
            $deletedNotification->setValidForSeconds(120);
            $deletedNotification->setTitleVar('deleted');
            $deletedNotification->setShortDescriptionVar($notification_osd_id);
            $deletedNotification->setLongDescriptionVar('dummy');

            require_once 'Services/Notifications/classes/class.ilNotificationSystem.php';
            ilNotificationSystem::sendNotificationToUsers($deletedNotification, array($row['usr_id']));*/
        }
    }

    /**
     * Remove orphaned notifications
     *
     * @global ilDB $ilDB
     */
    public static function cleanup()
    {
        global $ilDB;
        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_osd_handler . ' WHERE valid_until < ' . $ilDB->quote(time(), 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Exec self::clean with a probability of 1%
     */
    public static function cleanupOnRandom()
    {
        $rnd = rand(0, 10000);
        if ($rnd == 500) {
            self::cleanup();
        }
    }

    /**
     * Helper to append an additional parameter to an existing url
     *
     * @param string $link
     * @param string $param
     * @param scalar $value
     * @return string
     */
    private static function appendParamToLink($link, $param, $value)
    {
        if (strpos($link, '?') !== false) {
            $link .= '&' . $param . '=' . $value;
        } else {
            $link .= '?' . $param . '=' . $value;
        }
        return $link;
    }
}
