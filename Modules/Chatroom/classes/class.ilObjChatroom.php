<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject.php';
require_once 'Services/Object/classes/class.ilObjectActivation.php';

/**
 * Class ilObjChatroom
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroom extends ilObject
{
    /**
     * @var int
     */
    protected $access_type;

    /**
     * @var int
     */
    protected $access_begin;

    /**
     * @var int
     */
    protected $access_end;

    /**
     * @var int
     */
    protected $access_visibility;

    /**
     * {@inheritdoc}
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);

        $this->type = 'chtr';
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @param int $a_value
     */
    public function setAccessVisibility($a_value)
    {
        $this->access_visibility = (bool) $a_value;
    }

    /**
     * @return int
     */
    public function getAccessVisibility()
    {
        return $this->access_visibility;
    }

    /**
     * @return int
     */
    public function getAccessType()
    {
        return $this->access_type;
    }

    /**
     * @param int $access_type
     */
    public function setAccessType($access_type)
    {
        $this->access_type = $access_type;
    }

    /**
     * @return int
     */
    public function getAccessBegin()
    {
        return $this->access_begin;
    }

    /**
     * @param int $access_begin
     */
    public function setAccessBegin($access_begin)
    {
        $this->access_begin = $access_begin;
    }

    /**
     * @return int
     */
    public function getAccessEnd()
    {
        return $this->access_end;
    }

    /**
     * @param int $access_end
     */
    public function setAccessEnd($access_end)
    {
        $this->access_end = $access_end;
    }

    /**
     * @inheritdoc
     */
    public function update()
    {
        if ($this->ref_id) {
            $activation = new ilObjectActivation();
            $activation->setTimingType($this->getAccessType());
            $activation->setTimingStart($this->getAccessBegin());
            $activation->setTimingEnd($this->getAccessEnd());
            $activation->toggleVisible($this->getAccessVisibility());
            $activation->update($this->ref_id);
        }

        return parent::update();
    }

    /**
     * @inheritdoc
     */
    public function read()
    {
        if ($this->ref_id) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            $this->setAccessType($activation['timing_type']);
            if ($this->getAccessType() == ilObjectActivation::TIMINGS_ACTIVATION) {
                $this->setAccessBegin($activation['timing_start']);
                $this->setAccessEnd($activation['timing_end']);
                $this->setAccessVisibility($activation['visible']);
            }
        }

        parent::read();
    }

    public static function _getPublicRefId()
    {
        $settings = new ilSetting('chatroom');
        return $settings->get('public_room_ref', 0);
    }

    public static function _getPublicObjId()
    {
        global $DIC;

        $rset = $DIC->database()->query('SELECT object_id FROM chatroom_settings WHERE room_type=' . $DIC->database()->quote('default', 'text'));
        if ($row = $DIC->database()->fetchAssoc($rset)) {
            return $row['object_id'];
        }
        return 0;
    }

    /**
     * Prepares and returns $userInfo using given $user object.
     * @param ilChatroomUser $user
     * @return stdClass
     */
    public function getPersonalInformation(ilChatroomUser $user)
    {
        $userInfo = new stdClass();
        $userInfo->username = $user->getUsername();
        $userInfo->id = $user->getUserId();

        return $userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function initDefaultRoles()
    {
        include_once './Services/AccessControl/classes/class.ilObjRole.php';

        $role = $this->createDefaultRole();

        return array();
    }

    /**
     * @return ilObjRole
     */
    protected function createDefaultRole()
    {
        return ilObjRole::createDefaultRole(
            'il_chat_moderator_' . $this->getRefId(),
            "Moderator of chat obj_no." . $this->getId(),
            'il_chat_moderator',
            $this->getRefId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        global $DIC;

        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
        $original_room = ilChatroom::byObjectId($this->getId());

        $newObj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        $objId = $newObj->getId();

        $original_settings = $original_room->getSettings();
        $room = new ilChatroom();

        $original_settings['object_id'] = $objId;

        $room->saveSettings($original_settings);

        include_once "Services/AccessControl/classes/class.ilRbacLog.php";
        $rbac_log_roles = $DIC->rbac()->review()->getParentRoleIds($newObj->getRefId(), false);
        $rbac_log = ilRbacLog::gatherFaPa($newObj->getRefId(), array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $newObj->getRefId(), $rbac_log);

        require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';

        $settings = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
        $connector = new ilChatroomServerConnector($settings);

        $connector->sendCreatePrivateRoom($room->getRoomId(), 0, $newObj->getOwner(), $newObj->getTitle());

        return $newObj;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        global $DIC;

        $DIC->database()->manipulateF(
            'DELETE FROM chatroom_users WHERE chatroom_users.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            array('integer'),
            array($this->getId())
        );

        $DIC->database()->manipulateF(
            'DELETE FROM chatroom_history WHERE chatroom_history.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            array('integer'),
            array($this->getId())
        );

        $DIC->database()->manipulateF(
            'DELETE FROM chatroom_bans WHERE chatroom_bans.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            array('integer'),
            array($this->getId())
        );

        $DIC->database()->manipulateF(
            'DELETE FROM chatroom_sessions WHERE chatroom_sessions.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            array('integer'),
            array($this->getId())
        );

        $DIC->database()->manipulateF(
            '
			DELETE FROM chatroom_proomaccess
			WHERE chatroom_proomaccess.proom_id IN (
				SELECT chatroom_prooms.proom_id
				FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (
					SELECT chatroom_settings.room_id
					FROM chatroom_settings
					WHERE chatroom_settings.object_id = %s
				)
			)',
            array('integer'),
            array($this->getId())
        );

        $DIC->database()->manipulateF(
            '
			DELETE FROM chatroom_psessions
			WHERE chatroom_psessions.proom_id IN (
				SELECT chatroom_prooms.proom_id
				FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (
					SELECT chatroom_settings.room_id
					FROM chatroom_settings
					WHERE chatroom_settings.object_id = %s
				)
			)',
            array('integer'),
            array($this->getId())
        );

        $DIC->database()->manipulateF(
            'DELETE FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            array('integer'),
            array($this->getId())
        );

        // Finally delete rooms
        $DIC->database()->manipulateF(
            'DELETE FROM chatroom_settings WHERE object_id = %s',
            array('integer'),
            array($this->getId())
        );

        if ($this->getId()) {
            if ($this->ref_id) {
                ilObjectActivation::deleteAllEntries($this->ref_id);
            }
        }

        return parent::delete();
    }
}
