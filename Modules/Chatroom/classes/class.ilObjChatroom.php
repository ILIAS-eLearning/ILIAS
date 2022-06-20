<?php declare(strict_types=1);

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
    protected ?int $access_type = null;
    protected ?int $access_begin = null;
    protected ?int $access_end = null;
    protected ?int $access_visibility = null;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);

        $this->type = 'chtr';
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function setAccessVisibility(int $a_value) : void
    {
        $this->access_visibility = $a_value;
    }

    public function getAccessVisibility() : ?int
    {
        return $this->access_visibility;
    }

    public function getAccessType() : ?int
    {
        return $this->access_type;
    }

    public function setAccessType(int $access_type) : void
    {
        $this->access_type = $access_type;
    }

    public function getAccessBegin() : ?int
    {
        return $this->access_begin;
    }

    public function setAccessBegin(?int $access_begin) : void
    {
        $this->access_begin = $access_begin;
    }

    public function getAccessEnd() : ?int
    {
        return $this->access_end;
    }

    public function setAccessEnd(?int $access_end) : void
    {
        $this->access_end = $access_end;
    }

    public function update() : bool
    {
        if ($this->referenced && $this->ref_id) {
            $activation = new ilObjectActivation();
            $activation->setTimingType($this->getAccessType());
            $activation->setTimingStart($this->getAccessBegin());
            $activation->setTimingEnd($this->getAccessEnd());
            $activation->toggleVisible((bool) $this->getAccessVisibility());
            $activation->setSuggestionStart(0);
            $activation->setSuggestionStartRelative(0);
            $activation->setSuggestionEnd(0);
            $activation->setSuggestionEndRelative(0);
            $activation->setEarliestStart(0);
            $activation->setEarliestStartRelative(0);
            $activation->toggleChangeable(true);
            $activation->update($this->ref_id);
        }

        return parent::update();
    }

    public function read() : void
    {
        if ($this->referenced && $this->ref_id) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            $this->setAccessType((int) $activation['timing_type']);
            if ($this->getAccessType() === ilObjectActivation::TIMINGS_ACTIVATION) {
                $this->setAccessBegin((int) $activation['timing_start']);
                $this->setAccessEnd((int) $activation['timing_end']);
                $this->setAccessVisibility((int) $activation['visible']);
            }
        }

        parent::read();
    }

    public static function _getPublicRefId() : int
    {
        $settings = new ilSetting('chatroom');

        return (int) $settings->get('public_room_ref', '0');
    }

    public static function _getPublicObjId() : int
    {
        global $DIC;

        $rset = $DIC->database()->query(
            'SELECT object_id FROM chatroom_settings WHERE room_type = ' . $DIC->database()->quote('default', 'text')
        );
        if ($row = $DIC->database()->fetchAssoc($rset)) {
            return (int) $row['object_id'];
        }

        return 0;
    }

    public function getPersonalInformation(ilChatroomUser $user) : stdClass
    {
        $userInfo = new stdClass();
        $userInfo->username = $user->getUsername();
        $userInfo->id = $user->getUserId();

        return $userInfo;
    }

    public function initDefaultRoles() : void
    {
        $this->createDefaultRole();
    }

    protected function createDefaultRole() : ilObjRole
    {
        return ilObjRole::createDefaultRole(
            'il_chat_moderator_' . $this->getRefId(),
            'Moderator of chat obj_no.' . $this->getId(),
            'il_chat_moderator',
            $this->getRefId()
        );
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false) : ?ilObject
    {
        $original_room = ilChatroom::byObjectId($this->getId());

        $newObj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        $objId = $newObj->getId();

        $original_settings = $original_room->getSettings();
        $room = new ilChatroom();

        $original_settings['object_id'] = $objId;

        $room->saveSettings($original_settings);

        $rbac_log_roles = $this->rbac_review->getParentRoleIds($newObj->getRefId(), false);
        $rbac_log = ilRbacLog::gatherFaPa($newObj->getRefId(), array_keys($rbac_log_roles), true);
        ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $newObj->getRefId(), $rbac_log);

        $settings = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
        $connector = new ilChatroomServerConnector($settings);

        $connector->sendCreatePrivateRoom($room->getRoomId(), 0, $newObj->getOwner(), $newObj->getTitle());

        return $newObj;
    }

    public function delete() : bool
    {
        $this->db->manipulateF(
            'DELETE FROM chatroom_users WHERE chatroom_users.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
            'DELETE FROM chatroom_history WHERE chatroom_history.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
            'DELETE FROM chatroom_bans WHERE chatroom_bans.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
            'DELETE FROM chatroom_sessions WHERE chatroom_sessions.room_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
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
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
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
            ['integer'],
            [$this->getId()]
        );

        $this->db->manipulateF(
            'DELETE FROM chatroom_prooms WHERE chatroom_prooms.parent_id IN (SELECT chatroom_settings.room_id FROM chatroom_settings WHERE chatroom_settings.object_id = %s)',
            ['integer'],
            [$this->getId()]
        );

        // Finally delete rooms
        $this->db->manipulateF(
            'DELETE FROM chatroom_settings WHERE object_id = %s',
            ['integer'],
            [$this->getId()]
        );

        if ($this->ref_id && $this->getId()) {
            ilObjectActivation::deleteAllEntries($this->ref_id);
        }

        return parent::delete();
    }
}
