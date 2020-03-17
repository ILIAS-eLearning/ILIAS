<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* class ilEventMembers
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilEventParticipants.php 15697 2008-01-08 20:04:33Z hschottm $
*
*/
class ilEventParticipants
{
    public $ilErr;
    public $ilDB;
    public $tree;
    public $lng;

    protected $contact = 0;

    /**
     * @var int[]
     */
    protected $registered = [];

    /**
     * @var int[]
     */
    protected $participated = [];

    /**
     * @var bool
     */
    protected $excused = [];

    /**
     * @var int[]
     */
    protected $contacts = [];

    public $event_id = null;

    /**
     * @var boolean
     */
    private $notificationEnabled;

    /**
     * Constructor
     * @param int $a_event_id
     */
    public function __construct($a_event_id)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();

        $this->ilErr = $ilErr;
        $this->db = $ilDB;
        $this->lng = $lng;

        $this->event_id = $a_event_id;
        $this->__read();
    }

    public function setUserId($a_usr_id)
    {
        $this->user_id = $a_usr_id;
    }
    public function getUserId()
    {
        return $this->user_id;
    }
    public function setMark($a_mark)
    {
        $this->mark = $a_mark;
    }
    public function getMark()
    {
        return $this->mark;
    }
    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    public function getComment()
    {
        return $this->comment;
    }
    public function setParticipated($a_status)
    {
        $this->participated = $a_status;
    }
    public function getParticipated()
    {
        return $this->participated;
    }
    public function setRegistered($a_status)
    {
        $this->registered = $a_status;
    }
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * @param bool $a_stat
     */
    public function setExcused(bool $a_stat)
    {
        $this->excused = $a_stat;
    }

    /**
     * @return bool
     */
    public function getExcused() : bool
    {
        return $this->excused;
    }


    /**
     * Update excused status
     * @param int $a_usr_id
     * @param bool $a_status
     */
    public function updateExcusedForUser(int $a_usr_id, bool $a_status)
    {
        if (!is_array($this->participants) || !array_key_exists($a_usr_id, $this->participants)) {
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
        return;
    }

    /**
     * @param bool $a_status
     */
    public function setContact($a_status)
    {
        $this->contact = (int) $a_status;
    }

    /**
     * @return int
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return boolean
     */
    public function isNotificationEnabled()
    {
        return (bool) $this->notificationEnabled;
    }

    /**
     * @param boolean $value
     */
    public function setNotificationEnabled($value)
    {
        $this->notificationEnabled = (bool) $value;
    }

    public function updateUser()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($this->getEventId(), 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $query = "INSERT INTO event_participants (event_id,usr_id,registered,participated,contact,notification_enabled, excused " .
            ") VALUES( " .
            $ilDB->quote($this->getEventId(), 'integer') . ", " .
            $ilDB->quote($this->getUserId(), 'integer') . ", " .
            $ilDB->quote($this->getRegistered(), 'integer') . ", " .
            $ilDB->quote($this->getParticipated(), 'integer') . ', ' .
            $ilDB->quote($this->getContact(), 'integer') . ', ' .
            $ilDB->quote($this->isNotificationEnabled(), 'integer') . ', ' .
            $ilDB->quote((int) $this->getExcused(), 'integer') .
            ")";
        $res = $ilDB->manipulate($query);

        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        $lp_mark = new ilLPMarks($this->getEventId(), $this->getUserId());
        $lp_mark->setComment($this->getComment());
        $lp_mark->setMark($this->getMark());
        $lp_mark->update();
        
        // refresh learning progress status after updating participant
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_updateStatus($this->getEventId(), $this->getUserId());
        
        if (!$this->getRegistered()) {
            self::handleAutoFill($this->getEventId());
        }

        return true;
    }

    public function getUser($a_usr_id)
    {
        return $this->participants[$a_usr_id] ? $this->participants[$a_usr_id] : array();
    }

    public function getParticipants()
    {
        return $this->participants ? $this->participants : array();
    }

    public function isRegistered($a_usr_id)
    {
        return $this->participants[$a_usr_id]['registered'] ? true : false;
    }

    public function hasParticipated($a_usr_id)
    {
        return $this->participants[$a_usr_id]['participated'] ? true : false;
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function isExcused(int $a_usr_id) : bool
    {
        return $this->participants[$a_usr_id]['excused'] ? true : false;
    }

    /**
     * Check if user is contact
     *
     * @param $a_usr_id
     * @return bool
     */
    public function isContact($a_usr_id)
    {
        return $this->participants[$a_usr_id]['contact'] ? true : false;
    }


    public function updateParticipation($a_usr_id, $a_status)
    {
        ilEventParticipants::_updateParticipation($a_usr_id, $this->getEventId(), $a_status);
    }

    public static function _updateParticipation($a_usr_id, $a_event_id, $a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE event_participants " .
                "SET participated = " . $ilDB->quote($a_status, 'integer') . " " .
                "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        } else {
            $query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) " .
                "VALUES( " .
                $ilDB->quote(0, 'integer') . ", " .
                $ilDB->quote($a_status, 'integer') . ", " .
                $ilDB->quote($a_event_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }
        
        // refresh learning progress status after updating participant
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_updateStatus($a_event_id, $a_usr_id);

        return true;
    }

    public static function _getRegistered($a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND registered = " . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[] = $row->usr_id;
        }
        return $user_ids ? $user_ids : array();
    }

    public static function _getParticipated($a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND participated = 1";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_ids[] = $row->usr_id;
        }
        return $user_ids ? $user_ids : array();
    }
    
    public static function _hasParticipated($a_usr_id, $a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT participated FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($rec = $ilDB->fetchAssoc($res)) {
            return (bool) $rec["participated"];
        }
        return false;
    }

    public static function _isRegistered($a_usr_id, $a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->registered;
        }
        return false;
    }

    public static function _register($a_usr_id, $a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE event_participants " .
                "SET registered = '1' " .
                "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        } else {
            $query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) " .
                "VALUES( " .
                "1, " .
                "0, " .
                $ilDB->quote($a_event_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }
        
        // refresh learning progress status after updating participant
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_updateStatus($a_event_id, $a_usr_id);
        
        return true;
    }
    public function register($a_usr_id)
    {
        return ilEventParticipants::_register($a_usr_id, $this->getEventId());
    }
            
    public static function _unregister($a_usr_id, $a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE event_participants " .
                "SET registered = 0 " .
                "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        } else {
            $query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) " .
                "VALUES( " .
                "0, " .
                "0, " .
                $ilDB->quote($a_event_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
        }
        
        // refresh learning progress status after updating participant
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_updateStatus($a_event_id, $a_usr_id);
        
        self::handleAutoFill($a_event_id);
        
        return true;
    }
    public function unregister($a_usr_id)
    {
        return ilEventParticipants::_unregister($a_usr_id, $this->getEventId());
    }

    public static function _lookupMark($a_event_id, $a_usr_id)
    {
        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        $lp_mark = new ilLPMarks($a_event_id, $a_usr_id);
        return $lp_mark->getMark();
    }
    
    public function _lookupComment($a_event_id, $a_usr_id)
    {
        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        $lp_mark = new ilLPMarks($a_event_id, $a_usr_id);
        return $lp_mark->getComment();
    }


    public function getEventId()
    {
        return $this->event_id;
    }
    public function setEventId($a_event_id)
    {
        $this->event_id = $a_event_id;
    }

    public static function _deleteByEvent($a_event_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        ilLPMarks::deleteObject($a_event_id);

        return true;
    }
    public static function _deleteByUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM event_participants " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }


    // Private
    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM event_participants " .
            "WHERE event_id = " . $ilDB->quote($this->getEventId()) . " ";
        $res = $this->db->query($query);

        global $DIC;
        $tree = $DIC->repositoryTree();

        $parentRecipients = [];
        /** @var ilObject $session */
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
                }
            }
        }


        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->participants[$row->usr_id]['usr_id'] = $row->usr_id;
            $this->participants[$row->usr_id]['registered'] = $row->registered;
            $this->participants[$row->usr_id]['participated'] = $row->participated;
            $this->participants[$row->usr_id]['excused'] = $row->excused;
            $this->participants[$row->usr_id]['contact'] = $row->contact;

            $lp_mark = new ilLPMarks($this->getEventId(), $row->usr_id);
            $this->participants[$row->usr_id]['mark'] = $lp_mark->getMark();
            $this->participants[$row->usr_id]['comment'] = $lp_mark->getComment();
            $this->participants[$row->usr_id]['notification_enabled'] = false;

            /** @var ilObjSession $session */
            if (true === $session->isRegistrationNotificationEnabled()) {
                if (ilSessionConstants::NOTIFICATION_MANUAL_OPTION === $session->getRegistrationNotificationOption()) {
                    $this->participants[$row->usr_id]['notification_enabled'] = (bool) $row->notification_enabled;
                } else {
                    foreach ($parentRecipients as $parentRecipientUserId) {
                        if ($parentRecipientUserId == $row->usr_id) {
                            $this->participants[$row->usr_id]['notification_enabled'] = true;
                        }
                    }
                }
            }

            if ($row->registered) {
                $this->registered[] = $row->usr_id;
            }
            if ($row->participated) {
                $this->participated[] = $row->usr_id;
            }
        }
    }
    
    /**
     * Trigger auto-fill from waiting list
     *
     * @param int $a_obj_id
     */
    protected static function handleAutoFill($a_obj_id)
    {
        $sess = new ilObjSession($a_obj_id, false);
        $sess->handleAutoFill();
    }
}
