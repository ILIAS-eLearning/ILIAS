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

use ILIAS\Notifications\ilNotificationSetupHelper;

class ilNotificationUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        // Creation of administration node forced by \ilTreeAdminNodeAddedObjective
    }

    public function step_2(): void
    {
        $this->db->manipulateF(
            'DELETE FROM settings WHERE module = %s AND keyword = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['notifications', 'enable_mail']
        );
        $this->db->manipulateF(
            'UPDATE settings SET keyword = %s WHERE module = %s AND keyword = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['osd_interval', 'notifications', 'osd_polling_intervall']
        );
        $this->db->manipulateF(
            'UPDATE settings SET module = %s, keyword = %s WHERE module = %s AND keyword = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['notifications', 'play_sound', 'chatroom', 'play_invitation_sound']
        );
        $this->db->manipulateF(
            'UPDATE usr_pref SET keyword = %s WHERE keyword = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['play_sound', 'chat_play_invitation_sound']
        );
    }

    public function step_3(): void
    {
        $this->db->manipulateF(
            'DELETE FROM notification_usercfg WHERE module = %s',
            [ilDBConstants::T_TEXT],
            ['osd_main']
        );
        ilNotificationSetupHelper::registerType(
            $this->db,
            'buddysystem_request',
            'buddysystem_request',
            'buddysystem_request_desc',
            'contact',
            'set_by_admin'
        );
    }

    public function step_4(): void
    {
        ilNotificationSetupHelper::registerType(
            $this->db,
            'who_is_online',
            'who_is_online',
            'who_is_online_desc',
            'user',
            'set_by_admin'
        );
        $this->db->insert(
            'notification_usercfg',
            [
                'usr_id' => [ilDBConstants::T_INTEGER, -1],
                'module' => [ilDBConstants::T_TEXT, 'who_is_online'],
                'channel' => [ilDBConstants::T_TEXT, 'osd']
            ]
        );
        $this->db->manipulateF(
            'UPDATE notification_osd SET type = %s WHERE type = %s AND serialized LIKE %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['who_is_online', 'osd_main', '%icon_usr.svg%']
        );
    }

    public function step_5(): void
    {
        ilNotificationSetupHelper::registerType(
            $this->db,
            'badge_received',
            'badge_received',
            'badge_received_desc',
            'achievement',
            'set_by_admin'
        );
        $this->db->insert(
            'notification_usercfg',
            [
                'usr_id' => [ilDBConstants::T_INTEGER, -1],
                'module' => [ilDBConstants::T_TEXT, 'badge_received'],
                'channel' => [ilDBConstants::T_TEXT, 'osd']
            ]
        );
        $this->db->manipulateF(
            'UPDATE notification_osd SET type = %s WHERE type = %s AND serialized LIKE %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['badge_received', 'osd_main', '%icon_bdga.svg%']
        );
    }

    public function step_6(): void
    {
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'notifications'],
            'keyword' => [ilDBConstants::T_TEXT, 'osd_vanish'],
            'value' => [ilDBConstants::T_INTEGER, 5]
        ]);
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'notifications'],
            'keyword' => [ilDBConstants::T_TEXT, 'osd_delay'],
            'value' => [ilDBConstants::T_INTEGER, 500]
        ]);
    }

    public function step_7(): void
    {
        $this->db->insert('settings', [
            'module' => [ilDBConstants::T_TEXT, 'notifications'],
            'keyword' => [ilDBConstants::T_TEXT, 'enable_mail'],
            'value' => [ilDBConstants::T_TEXT, '1']
        ]);
    }

    public function step_8(): void
    {
        $this->db->addIndex('notification_osd', ['usr_id', 'type', 'time_added'], 'i1');
    }

    public function step_9(): void
    {
        $this->db->manipulateF(
            "UPDATE settings SET value = CONCAT(value , '000') WHERE keyword = %s",
            [ilDBConstants::T_TEXT],
            ['osd_interval']
        );
        $this->db->manipulateF(
            "UPDATE settings SET value = CONCAT(value , '000') WHERE keyword = %s",
            [ilDBConstants::T_TEXT],
            ['osd_vanish']
        );
        $this->db->manipulateF(
            'UPDATE usr_pref SET keyword = %s WHERE keyword = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['osd_play_sound', 'play_sound']
        );
    }
}
