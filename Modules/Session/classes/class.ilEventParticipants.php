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
 ********************************************************************
 */

/**
* class ilEventParticipants
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilEventParticipants.php 15697 2008-01-08 20:04:33Z hschottm $
*
*/
class ilEventParticipants
{
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected int $contact = 0;
    protected bool $registered = false;
    protected bool $participated = false;
    protected bool $excused = false;
    protected int $event_id = 0;
    protected bool $notificationEnabled = false;
    protected int $user_id = 0;
    protected string $mark = "";
    protected string $comment = "";
    protected array $participants = [];
    protected array $participants_registered = [];
    protected array $participants_participated = [];

    public function __construct(int $a_event_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->event_id = $a_event_id;
        $this->__read();
    }

    public function setUserId(int $a_usr_id) : void
    {
        $this->user_id = $a_usr_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function setMark(string $a_mark) : void
    {
        $this->mark = $a_mark;
    }

    public function getMark() : string
    {
        return $this->mark;
    }

    public function setComment(string $a_comment) : void
    {
        $this->comment = $a_comment;
    }

    public function getComment() : string
    {
        return $this->comment;
    }

    public function setParticipated(bool $a_status)
    {
        $this->participated = $a_status;
    }

    public function getParticipated() : bool
    {
        return $this->participated;
    }

    public function setRegistered(bool $a_status) : void
    {
        $this->registered = $a_status;
    }

    public function getRegistered() : bool
    {
        return $this->registered;
    }

    public function setExcused(bool $a_stat) : void
    {
        $this->excused = $a_stat;
    }

    public function getExcused() : bool
    {
        return $this->excused;
    }

    public function getEventId() : int
    {
        return $this->event_id;
    }

    public function setEventId(int $a_event_id) : void
    {
        $this->event_id = $a_event_id;
    }

    public function setContact(bool $a_status) : void
    {
        $this->contact = (int) $a_status;
    }

    public function getContact() : int
    {
        return $this->contact;
    }

    public function isNotificationEnabled() : bool
    {
        return $this->notificationEnabled;
    }

    public function setNotificationEnabled(bool $value) : void
    {
        $this->notificationEnabled = $value;
    }

    public function setParticipatedParticipants(array $participants_participated) : void
    {
        $this->participants_participated = $participants_participated;
    }
    public function getParticipatedParticipants() : array
    {
        return $this->participants_participated;
    }
    public function setRegisteredParticipants(array $registered_participants) : void
    {
        $this->participants_registered = $registered_participants;
    }
    public function getRegisteredParticipants() : array
    {
        return $this->participants_registered;
    }

    public function updateExcusedForUser(int $a_usr_id, bool $a_status) : void
    {
        if (!array_key_exists($a_usr_id, $this->participants)) {
            $event_part = new \ilEventParticipants($this->event_id);
            $event_part->setUserId($a_usr_id);
            $event_part->setMark('');
            $event_part->setComment('');
            $event_part->setNotificationEnabled(false);
            $event_part->setParticipated(false);
            $event_part->setRegistered(false);
            $event_part->setContact(false);
            $event_part->setExcused($a_status);
            $event_part->updateUser();
            return;
        }

        $query = 'update event_participants set excused = ' . $this->db->quote($a_status, \ilDBConstants::T_INTEGER) . ' ' .
            'where event_id = ' . $this->db->quote($this->event_id, \ilDBConstants::T_INTEGER) . ' and ' .
            'usr_id = ' . $this->db->quote($a_usr_id, \ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public function updateUser() : bool
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($this->getEventId(), 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $query = "INSERT INTO event_participants (event_id,usr_id,registered,participated,contact,notification_enabled, excused " .
            ") VALUES( " .
            $ilDB->quote($this->getEventId(), 'integer') . ", " .
            $ilDB->quote($this->getUserId(), 'integer') . ", " .
            $ilDB->quote((int) $this->getRegistered(), 'integer') . ", " .
            $ilDB->quote((int) $this->getParticipated(), 'integer') . ', ' .
            $ilDB->quote($this->getContact(), 'integer') . ', ' .
            $ilDB->quote((int) $this->isNotificationEnabled(), 'integer') . ', ' .
            $ilDB->quote((int) $this->getExcused(), 'integer') .
            ")";
        $res = $ilDB->manipulate($query);

        $lp_mark = new ilLPMarks($this->getEventId(), $this->getUserId());
        $lp_mark->setComment($this->getComment());
        $lp_mark->setMark($this->getMark());
        $lp_mark->update();
        
        // refresh learning progress status after updating participant
        ilLPStatusWrapper::_updateStatus($this->getEventId(), $this->getUserId());
        
        if (!$this->getRegistered()) {
            self::handleAutoFill($this->getEventId());
        }

        return true;
    }

    public function getUser(int $a_usr_id) : array
    {
        return $this->participants[$a_usr_id] ?? [];
    }

    public function getParticipants() : array
    {
        return $this->participants;
    }

    public function isRegistered(int $a_usr_id) : bool
    {
        return (bool) ($this->participants[$a_usr_id]['registered'] ?? false);
    }

    public function hasParticipated(int $a_usr_id) : bool
    {
        return (bool) ($this->participants[$a_usr_id]['participated'] ?? false);
    }

    public function isExcused(int $a_usr_id) : bool
    {
        return (bool) ($this->participants[$a_usr_id]['excused'] ?? false);
    }

    public function isContact(int $a_usr_id) : bool
    {
        return (bool) ($this->participants[$a_usr_id]['contact'] ?? false);
    }


    public function updateParticipation(int $a_usr_id, bool $a_status) : bool
    {
        return self::_updateParticipation($a_usr_id, $this->getEventId(), $a_status);
    }

    public static function _updateParticipation(int $a_usr_id, int $a_event_id, bool $a_status) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE event_participants " .
                "SET participated = " . $ilDB->quote((int) $a_status, 'integer') . " " .
                "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        } else {
            $query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) " .
                "VALUES( " .
                $ilDB->quote(0, 'integer') . ", " .
                $ilDB->quote((int) $a_status, 'integer') . ", " .
                $ilDB->quote($a_event_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . " " .
                ")";
        }
        $res = $ilDB->manipulate($query);

        // refresh learning progress status after updating participant
        ilLPStatusWrapper::_updateStatus($a_event_id, $a_usr_id);

        return true;
    }

    public static function _getRegistered(int $a_event_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND registered = " . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);
        $user_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[] = $row->usr_id;
        }
        return $user_ids;
    }

    public static function _getParticipated(int $a_event_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND participated = 1";
        $res = $ilDB->query($query);
        $user_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[$row->usr_id] = $row->usr_id;
        }
        return $user_ids;
    }
    
    public static function _hasParticipated(int $a_usr_id, int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT participated FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($res)) {
            return (bool) $rec["participated"];
        }
        return false;
    }

    public static function _isRegistered(int $a_usr_id, int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->registered;
        }
        return false;
    }

    public static function _register(int $a_usr_id, int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE event_participants " .
                "SET registered = '1' " .
                "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        } else {
            $query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) " .
                "VALUES( " .
                "1, " .
                "0, " .
                $ilDB->quote($a_event_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . " " .
                ")";
        }
        $res = $ilDB->manipulate($query);

        // refresh learning progress status after updating participant
        ilLPStatusWrapper::_updateStatus($a_event_id, $a_usr_id);
        
        return true;
    }

    public function register(int $a_usr_id) : bool
    {
        return ilEventParticipants::_register($a_usr_id, $this->getEventId());
    }
            
    public static function _unregister(int $a_usr_id, int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE event_participants " .
                "SET registered = 0 " .
                "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        } else {
            $query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) " .
                "VALUES( " .
                "0, " .
                "0, " .
                $ilDB->quote($a_event_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . " " .
                ")";
        }
        $res = $ilDB->manipulate($query);

        // refresh learning progress status after updating participant
        ilLPStatusWrapper::_updateStatus($a_event_id, $a_usr_id);
        
        self::handleAutoFill($a_event_id);
        
        return true;
    }

    public function unregister(int $a_usr_id) : bool
    {
        return self::_unregister($a_usr_id, $this->getEventId());
    }

    public static function _lookupMark(int $a_event_id, int $a_usr_id) : string
    {
        $lp_mark = new ilLPMarks($a_event_id, $a_usr_id);
        return $lp_mark->getMark();
    }
    
    public function _lookupComment(int $a_event_id, int $a_usr_id) : string
    {
        $lp_mark = new ilLPMarks($a_event_id, $a_usr_id);
        return $lp_mark->getComment();
    }

    public static function _deleteByEvent(int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        ilLPMarks::deleteObject($a_event_id);

        return true;
    }

    public static function _deleteByUser(int $a_usr_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "DELETE FROM event_participants " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    protected function __read() : void
    {
        global $DIC;

        $ilDB = $this->db;
        $tree = $this->tree;

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($this->getEventId(), 'integer') . " ";
        $res = $this->db->query($query);

        $parentRecipients = [];
        $parentParticipants = [];
        $session = ilObjectFactory::getInstanceByObjId($this->event_id);
        $refIdArray = array_values(ilObject::_getAllReferences($this->event_id));
        if (true === $session->isRegistrationNotificationEnabled()) {
            if (ilSessionConstants::NOTIFICATION_INHERIT_OPTION === $session->getRegistrationNotificationOption()) {
                $parentRefId = $tree->checkForParentType($refIdArray[0], 'grp');
                if (!$parentRefId) {
                    $parentRefId = $tree->checkForParentType($refIdArray[0], 'crs');
                }
                if ($parentRefId) {
                    $participants = \ilParticipants::getInstance($parentRefId);
                    $parentRecipients = $participants->getNotificationRecipients();
                    $parentParticipants = $participants->getParticipants();
                }
            }
        }
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->participants[(int) $row->usr_id]['usr_id'] = (int) $row->usr_id;
            $this->participants[(int) $row->usr_id]['registered'] = (bool) $row->registered;
            $this->participants[(int) $row->usr_id]['participated'] = (bool) $row->participated;
            $this->participants[(int) $row->usr_id]['excused'] = (bool) $row->excused;
            $this->participants[(int) $row->usr_id]['contact'] = (bool) $row->contact;

            $lp_mark = new ilLPMarks($this->getEventId(), (int) $row->usr_id);
            $this->participants[(int) $row->usr_id]['mark'] = $lp_mark->getMark();
            $this->participants[(int) $row->usr_id]['comment'] = $lp_mark->getComment();
            $this->participants[(int) $row->usr_id]['notification_enabled'] = false;
            if (in_array((int) $row->usr_id, $parentRecipients)) {
                $this->participants[(int) $row->usr_id]['notification_enabled'] = true;
            }

            if ($row->registered) {
                $this->participants_registered[] = (int) $row->usr_id;
            }
            if ($row->participated) {
                $this->participants_participated[] = (int) $row->usr_id;
            }
        }
        // add defaults for parent participants
        foreach ($parentParticipants as $usr_id) {
            if (isset($this->participants[$usr_id])) {
                continue;
            }
            $this->participants[$usr_id]['usr_id'] = (int) $usr_id;
            $this->participants[$usr_id]['registered'] = false;
            $this->participants[$usr_id]['participated'] = false;
            $this->participants[$usr_id]['excused'] = false;
            $this->participants[$usr_id]['contact'] = false;
            $lp_mark = new ilLPMarks($this->getEventId(), $usr_id);
            $this->participants[$usr_id]['mark'] = $lp_mark->getMark();
            $this->participants[$usr_id]['comment'] = $lp_mark->getComment();
            $this->participants[$usr_id]['notification_enabled'] = false;
            if (in_array($usr_id, $parentRecipients)) {
                $this->participants[$usr_id]['notification_enabled'] = true;
            }
        }
    }
    
    /**
     * Trigger auto-fill from waiting list
     */
    protected static function handleAutoFill(int $a_obj_id) : void
    {
        $sess = new ilObjSession($a_obj_id, false);
        $sess->handleAutoFill();
    }
}
