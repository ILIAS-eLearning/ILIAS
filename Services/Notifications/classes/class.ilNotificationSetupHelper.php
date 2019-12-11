<?php

require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

/**
 * Helper class for initial database setup and registration of notification
 */
class ilNotificationSetupHelper
{
    public static $tbl_userconfig = 'notification_usercfg';
    public static $tbl_userlistener = 'notification_listener';
    public static $tbl_notification_data = 'notification_data';
    public static $tbl_notification_queue = 'notification_queue';
    public static $tbl_notification_osd_handler = 'notification_osd';
    public static $tbl_notification_channels = 'notification_channels';
    public static $tbl_notification_types = 'notification_types';
    
    public static function setupTables()
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$ilDB->tableExists(self::$tbl_userconfig)) {
            $fields = array(
                'usr_id' =>  array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'module' =>  array('type' => 'text'   , 'notnull' => true, 'length' => 100),
                'channel' => array('type' => 'text'   , 'notnull' => true, 'length' => 100),
            );
            $ilDB->createTable(self::$tbl_userconfig, $fields);
            $ilDB->addPrimaryKey(self::$tbl_userconfig, array('usr_id', 'module', 'channel'));
        }

        if (!$ilDB->tableExists(self::$tbl_userlistener)) {
            $fields = array(
                'usr_id' =>    array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'module' =>    array('type' => 'text'   , 'notnull' => true, 'length' => 100),
                'sender_id' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'disabled' =>  array('type' => 'integer', 'notnull' => true, 'length' => 1),
            );
            $ilDB->createTable(self::$tbl_userlistener, $fields);
            $ilDB->addPrimaryKey(self::$tbl_userlistener, array('usr_id', 'module', 'sender_id'));
        }

        if (!$ilDB->tableExists(self::$tbl_notification_data)) {
            $fields = array(
                'notification_id' =>  array('type' => 'integer', 'notnull' => true, 'length' => 4),
                'serialized' =>       array('type' => 'text'   , 'notnull' => true, 'length' => 4000),
            );
            $ilDB->createTable(self::$tbl_notification_data, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_data, array('notification_id'));

            $ilDB->createSequence(self::$tbl_notification_data);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_queue)) {
            $fields = array(
                'notification_id' =>      array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'usr_id' =>               array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                #'notification_channel' => array('type' => 'text'     , 'notnull' => true, 'length' => 100),
                'valid_until' =>          array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
            );
            $ilDB->createTable(self::$tbl_notification_queue, $fields);
            #$ilDB->addPrimaryKey(self::$tbl_notification_queue, array('notification_id', 'usr_id', 'notification_channel'));
            $ilDB->addPrimaryKey(self::$tbl_notification_queue, array('notification_id', 'usr_id'));
        }
            
        if (!$ilDB->tableExists(self::$tbl_notification_osd_handler)) {
            $fields = array(
                'notification_osd_id' => array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'usr_id' =>              array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'serialized' =>          array('type' => 'text'     , 'notnull' => true, 'length' => 4000),
                'valid_until' =>         array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'time_added' =>          array('type' => 'integer'  , 'notnull' => true, 'length' => 4),
                'type' =>                array('type' => 'text'     , 'notnull' => true, 'length' => 100),
            );
            $ilDB->createTable(self::$tbl_notification_osd_handler, $fields);

            $ilDB->addPrimaryKey(self::$tbl_notification_osd_handler, array('notification_osd_id'));
            /**
             * @todo hier wird ein fehler geworfen...
             */
            #$ilDB->addIndex(self::$tbl_notification_osd_handler, array('usr_id', 'valid_until', 'time_added'));

            $ilDB->createSequence(self::$tbl_notification_osd_handler);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_channels)) {
            $fields = array(
                'channel_name'=> array('type' => 'text', 'notnull' => true, 'length' => 100),
                'title' =>       array('type' => 'text', 'notnull' => true, 'length' => 100),
                'description'=>  array('type' => 'text', 'notnull' => true, 'length' => 4000),
                'class' =>       array('type' => 'text', 'notnull' => true, 'length' => 100),
                'include' =>     array('type' => 'text', 'notnull' => true, 'length' => 100),
                'config_type' => array('type' => 'text', 'notnull' => true, 'length' => 30),
            );
            $ilDB->createTable(self::$tbl_notification_channels, $fields);

            $ilDB->addPrimaryKey(self::$tbl_notification_channels, array('channel_name'));

            ilNotificationSetupHelper::registerChannel('mail', 'mail', 'mail_desc', 'ilNotificationMailHandler', 'Services/Notifications/classes/class.ilNotificationMailHandler.php');
            ilNotificationSetupHelper::registerChannel('osd', 'osd', 'osd_desc', 'ilNotificationOSDHandler', 'Services/Notifications/classes/class.ilNotificationOSDHandler.php');
        }

        if (!$ilDB->tableExists(self::$tbl_notification_types)) {
            $fields = array(
                'type_name'=>           array('type' => 'text', 'notnull' => true, 'length' => 100),
                'title' =>              array('type' => 'text', 'notnull' => true, 'length' => 100),
                'description' =>        array('type' => 'text', 'notnull' => true, 'length' => 100),
                'notification_group' => array('type' => 'text', 'notnull' => true, 'length' => 100),
                'config_type' =>        array('type' => 'text', 'notnull' => true, 'length' => 30),
            );
            $ilDB->createTable(self::$tbl_notification_types, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_types, array('type_name'));

            ilNotificationSetupHelper::registerType('chat_invitation', 'chat_invitation', 'chat_invitation_description', 'chat');
            //ilNotificationSetupHelper::registerType('adobe_connect_invitation', 'adobe_connect_invitation', 'adobe_connect_invitation_description', 'adobe_connect');
            ilNotificationSetupHelper::registerType('osd_maint', 'osd_maint', 'osd_maint_description', 'osd_notification');
        }
    }


    public static function registerChannel($name, $title, $description, $class, $classfile, $config_type = 'set_by_user')
    {
        ilNotificationDatabaseHandler::registerChannel($name, $title, $description, $class, $classfile, $config_type);
    }

    public static function registerType($name, $title, $description, $notification_group, $config_type = 'set_by_user')
    {
        ilNotificationDatabaseHandler::registerType($name, $title, $description, $notification_group, $config_type);
    }
}
