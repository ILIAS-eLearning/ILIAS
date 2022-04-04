<?php declare(strict_types = 1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilNotificationUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }
    
    public function step_1() : void
    {
        include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
        ilDBUpdateNewObjectType::addAdminNode('nota', 'Notification Service Administration Object');
    }

    public function step_2() : void
    {
        $this->db->queryF(
            'DELETE FROM settings WHERE module = %s AND keyword = %s',
            ['text', 'text'],
            ['notifications', 'enable_mail']
        );
        $this->db->queryF(
            'UPDATE settings SET keyword = %s WHERE module = %s AND keyword = %s',
            ['text', 'text', 'text'],
            ['osd_interval', 'notifications', 'osd_polling_intervall']
        );
        $this->db->queryF(
            'UPDATE settings SET module = %s, keyword = %s WHERE module = %s AND keyword = %s',
            ['text', 'text', 'text', 'text'],
            ['notifications', 'play_sound', 'chatroom', 'play_invitation_sound']
        );
        $this->db->queryF(
            'UPDATE usr_pref SET keyword = %s WHERE keyword = %s',
            ['text', 'text'],
            ['play_sound', 'chat_play_invitation_sound']
        );
    }

    public function step_3() : void
    {
        $this->db->queryF('DELETE FROM notification_usercfg WHERE module = %s', ['text'], ['osd_main']);
        ilNotificationSetupHelper::registerType(
            'buddysystem_request',
            'buddysystem_request',
            'buddysystem_request_desc',
            'contact',
            'set_by_admin'
        );
    }

    public function step_4() : void
    {
        ilNotificationSetupHelper::registerType(
            'who_is_online',
            'who_is_online',
            'who_is_online_desc',
            'user',
            'set_by_admin'
        );
        $this->db->insert(
            'notification_usercfg',
            array(
                'usr_id' => array('integer', -1),
                'module' => array('text', 'who_is_online'),
                'channel' => array('text', 'osd')
            )
        );
        $this->db->queryF(
            'UPDATE notification_osd SET type = %s WHERE type = %s AND serialized LIKE %s',
            ['text', 'text', 'text'],
            ['who_is_online', 'osd_main', '%icon_usr.svg%']
        );
    }

    public function step_5() : void
    {
        ilNotificationSetupHelper::registerType(
            'badge_received',
            'badge_received',
            'badge_received_desc',
            'achievement',
            'set_by_admin'
        );
        $this->db->insert(
            'notification_usercfg',
            array(
                'usr_id' => array('integer', -1),
                'module' => array('text', 'badge_received'),
                'channel' => array('text', 'osd')
            )
        );
        $this->db->queryF(
            'UPDATE notification_osd SET type = %s WHERE type = %s AND serialized LIKE %s',
            ['text', 'text', 'text'],
            ['badge_received', 'osd_main', '%icon_bdga.svg%']
        );
    }

    public function step_6() : void
    {
        $this->db->insert('settings', [
            'module' => ['text', 'notifications'],
            'keyword' => ['text', 'osd_vanish'],
            'value' => ['integer', 5]
        ]);
        $this->db->insert('settings', [
            'module' => ['text', 'notifications'],
            'keyword' => ['text', 'osd_delay'],
            'value' => ['integer', 500]
        ]);
    }
}
