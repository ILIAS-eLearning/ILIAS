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

namespace ILIAS\Notifications;

use ilDBInterface;
use ilDBConstants;

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

    public static function setupTables(): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$ilDB->tableExists(self::$tbl_userconfig)) {
            $fields = [
                'usr_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'module' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'channel' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
            ];
            $ilDB->createTable(self::$tbl_userconfig, $fields);
            $ilDB->addPrimaryKey(self::$tbl_userconfig, ['usr_id', 'module', 'channel']);
        }

        if (!$ilDB->tableExists(self::$tbl_userlistener)) {
            $fields = [
                'usr_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'module' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'sender_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'disabled' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 1],
            ];
            $ilDB->createTable(self::$tbl_userlistener, $fields);
            $ilDB->addPrimaryKey(self::$tbl_userlistener, ['usr_id', 'module', 'sender_id']);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_data)) {
            $fields = [
                'notification_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'serialized' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 4000],
            ];
            $ilDB->createTable(self::$tbl_notification_data, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_data, ['notification_id']);

            $ilDB->createSequence(self::$tbl_notification_data);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_queue)) {
            $fields = [
                'notification_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'usr_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'valid_until' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
            ];
            $ilDB->createTable(self::$tbl_notification_queue, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_queue, ['notification_id', 'usr_id']);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_osd_handler)) {
            $fields = [
                'notification_osd_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'usr_id' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'serialized' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 4000],
                'valid_until' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'time_added' => ['type' => ilDBConstants::T_INTEGER, 'notnull' => true, 'length' => 4],
                'type' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
            ];
            $ilDB->createTable(self::$tbl_notification_osd_handler, $fields);

            $ilDB->addPrimaryKey(self::$tbl_notification_osd_handler, ['notification_osd_id']);

            $ilDB->createSequence(self::$tbl_notification_osd_handler);
        }

        if (!$ilDB->tableExists(self::$tbl_notification_channels)) {
            $fields = [
                'channel_name' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'title' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'description' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 4000],
                'class' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'include' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'config_type' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 30],
            ];
            $ilDB->createTable(self::$tbl_notification_channels, $fields);

            $ilDB->addPrimaryKey(self::$tbl_notification_channels, ['channel_name']);

            self::registerChannel(
                $ilDB,
                'mail',
                'mail',
                'mail_desc',
                'ilNotificationMailHandler',
                'Services/Notifications/classes/class.ilNotificationMailHandler.php'
            );
            self::registerChannel(
                $ilDB,
                'osd',
                'osd',
                'osd_desc',
                'ilNotificationOSDHandler',
                'Services/Notifications/classes/class.ilNotificationOSDHandler.php'
            );
        }

        if (!$ilDB->tableExists(self::$tbl_notification_types)) {
            $fields = [
                'type_name' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'title' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'description' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'notification_group' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 100],
                'config_type' => ['type' => ilDBConstants::T_TEXT, 'notnull' => true, 'length' => 30],
            ];
            $ilDB->createTable(self::$tbl_notification_types, $fields);
            $ilDB->addPrimaryKey(self::$tbl_notification_types, ['type_name']);

            self::registerType($ilDB, 'chat_invitation', 'chat_invitation', 'chat_invitation_description', 'chat');
            self::registerType($ilDB, 'osd_maint', 'osd_maint', 'osd_maint_description', 'osd_notification');
        }
    }

    public static function registerChannel(
        ilDBInterface $db,
        string $name,
        string $title,
        string $description,
        string $class,
        string $classfile,
        string $config_type = 'set_by_user'
    ): void {
        ilNotificationDatabaseHandler::registerChannel(
            $db,
            $name,
            $title,
            $description,
            $class,
            $classfile,
            $config_type
        );
    }

    public static function registerType(
        ilDBInterface $db,
        string $name,
        string $title,
        string $description,
        string $notification_group,
        string $config_type = 'set_by_user'
    ): void {
        ilNotificationDatabaseHandler::registerType(
            $db,
            $name,
            $title,
            $description,
            $notification_group,
            $config_type
        );
    }
}
