<?php declare(strict_types=1);

namespace ILIAS\Notifications;

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
class ilNotificationSetupHelper
{
    public static string $tbl_userconfig = 'notification_usercfg';
    public static string $tbl_userlistener = 'notification_listener';
    public static string $tbl_notification_data = 'notification_data';
    public static string $tbl_notification_queue = 'notification_queue';
    public static string $tbl_notification_osd_handler = 'notification_osd';
    public static string $tbl_notification_channels = 'notification_channels';
    public static string $tbl_notification_types = 'notification_types';
    
    public static function setupTables() : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$ilDB->tableExists(self::$tbl_userconfig)) {
            $fields = array(
                'usr_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'module' => array('type' => 'text'   , 'notnull' => true, 'length' => 100),
                'channel' => array('type' => 'text'   , 'notnull' => true, 'length' => 100),
            );
            $ilDB->createTable(self::$tbl_userconfig, $fields);
            $ilDB->addPrimaryKey(self::$tbl_userconfig, array('usr_id', 'module', 'channel'));
        }

        if (!$ilDB->tableExists(self::$tbl_userlistener)) {
            $fields = array(
                'usr_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'module' => array('type' => 'text'   , 'notnull' => true, 'length' => 100),
                'sender_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'disabled' => array('type' => 'integer', 'notnull' => true, 'length' => 1),
            );
            $ilDB->createTable(self::$tbl_userlistener, $fields);
            $ilDB->addPrimaryKey(self::$tbl_userlistener, array('usr_id', 'module', 'sender_id'));
        }

        if (!$ilDB->tableExists(self::$tbl_notification_data)) {
            $fields = array(
                'notification_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'serialized' => array('type' => 'text'   , 'notnull' => true, 'length' => 4000),
            );
            $ilDB->createTable(self::$tbl_notification_data, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_data, array('notification_id'));

            $ilDB->createSequence(self::$tbl_notification_data);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_queue)) {
            $fields = array(
                'notification_id' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'usr_id' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'valid_until' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
            );
            $ilDB->createTable(self::$tbl_notification_queue, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_queue, array('notification_id', 'usr_id'));
        }
            
        if (!$ilDB->tableExists(self::$tbl_notification_osd_handler)) {
            $fields = array(
                'notification_osd_id' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'usr_id' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'serialized' => array('type' => 'text'     , 'notnull' => true, 'length' => 4000),
                'valid_until' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'time_added' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'type' => array('type' => 'text'     , 'notnull' => true, 'length' => 100),
            );
            $ilDB->createTable(self::$tbl_notification_osd_handler, $fields);

            $ilDB->addPrimaryKey(self::$tbl_notification_osd_handler, array('notification_osd_id'));

            $ilDB->createSequence(self::$tbl_notification_osd_handler);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_channels)) {
            $fields = array(
                'channel_name' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'title' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'description' => array('type' => 'text', 'notnull' => true, 'length' => 4000),
                'class' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'include' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'config_type' => array('type' => 'text', 'notnull' => true, 'length' => 30),
            );
            $ilDB->createTable(self::$tbl_notification_channels, $fields);

            $ilDB->addPrimaryKey(self::$tbl_notification_channels, array('channel_name'));

            self::registerChannel('mail', 'mail', 'mail_desc', 'ilNotificationMailHandler', 'Services/Notifications/classes/class.ilNotificationMailHandler.php');
            self::registerChannel('osd', 'osd', 'osd_desc', 'ilNotificationOSDHandler', 'Services/Notifications/classes/class.ilNotificationOSDHandler.php');
        }

        if (!$ilDB->tableExists(self::$tbl_notification_types)) {
            $fields = array(
                'type_name' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'title' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'description' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'notification_group' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'config_type' => array('type' => 'text', 'notnull' => true, 'length' => 30),
            );
            $ilDB->createTable(self::$tbl_notification_types, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_types, array('type_name'));

            self::registerType('chat_invitation', 'chat_invitation', 'chat_invitation_description', 'chat');
            self::registerType('osd_maint', 'osd_maint', 'osd_maint_description', 'osd_notification');
        }
    }

    public static function registerChannel(string $name, string $title, string $description, string $class, string $classfile, string $config_type = 'set_by_user') : void
    {
        ilNotificationDatabaseHandler::registerChannel($name, $title, $description, $class, $classfile, $config_type);
    }

    public static function registerType(string $name, string $title, string $description, string $notification_group, string $config_type = 'set_by_user') : void
    {
        ilNotificationDatabaseHandler::registerType($name, $title, $description, $notification_group, $config_type);
    }
}
