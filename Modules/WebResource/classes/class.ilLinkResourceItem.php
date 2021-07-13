<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLinkResourceItem
*
* @author Marvin Barz <barz@leifos.com>
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilLinkResourceItem
{
    protected int $id;

    protected int $webr_id = 0;

    protected ilDBInterface $db;

    protected string $title = '';

    protected string $description = '';

    protected string $target = '';

    protected bool $status = false;

    protected bool $check = false;

    protected int $c_date = 0;

    protected int $m_date = 0;

    protected ?int $last_check = null;

    protected int $valid = 0;

    protected bool $internal = false;

    public function __construct($link_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $link_id;
        $this->read();
    }

    private function read()
    {
        if (!$this->getLinkId() || $this->getLinkId() == 0) {
            return false;
        }

        $query = "SELECT * FROM webr_items WHERE link_id = " . $this->db->quote($this->getLinkId(), ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) { 
            $this->setWebResourceId($row->webr_id);
            $this->setTitle($row->title);
            $this->setDescription($row->description);
            $this->setTarget($row->target);
            $this->setActiveStatus($row->active);
            $this->setDisableCheckStatus($row->disable_check);
            $this->setCreateDate($row->create_date);
            $this->setLastUpdateDate($row->last_update);
            $this->setLastCheckDate($row->last_check);
            $this->setValidStatus($row->valid);
            $this->setInternal($row->internal);
        }

        return true;
    }

    public function delete($a_update_history = true)
    {
        if (!$this->getLinkId() || $this->getLinkId() == 0) {
            return false;
        }
        
        $query = "DELETE FROM webr_items " .
            "WHERE webr_id = " . $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER) . " " .
            "AND link_id = " . $this->db->quote($this->getLinkId(), ilDBConstants::T_INTEGER);
        $res = $this->db->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getWebResourceId(),
                "delete",
                $this->getTitle()
            );
        }

        return true;
    }

    public function update($a_update_history = true)
    {

        if (!$this->getLinkId() || $this->getLinkId() == 0) {
            return false;
        }

        $this->setLastUpdateDate(time());
        $query = "UPDATE webr_items " .
            "SET title = " . $this->db->quote($this->getTitle(), ilDBConstants::T_TEXT) . ", " .
            "description = " . $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ", " .
            "target = " . $this->db->quote($this->getTarget(), ilDBConstants::T_TEXT) . ", " .
            "active = " . $this->db->quote($this->getActiveStatus(), ilDBConstants::T_INTEGER) . ", " .
            "valid = " . $this->db->quote($this->getValidStatus(), ilDBConstants::T_INTEGER) . ", " .
            "disable_check = " . $this->db->quote($this->getDisableCheckStatus(), ilDBConstants::T_INTEGER) . ", " .
            "internal = " . $this->db->quote($this->getInternal(), ilDBConstants::T_INTEGER) . ", " .
            "last_update = " . $this->db->quote($this->getLastUpdateDate(), ilDBConstants::T_INTEGER) . ", " .
            "last_check = " . $this->db->quote($this->getLastCheckDate(), ilDBConstants::T_INTEGER) . " " .
            "WHERE link_id = " . $this->db->quote($this->getLinkId(), ilDBConstants::T_INTEGER) . " " .
            "AND webr_id = " . $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER);
        $res = $this->db->manipulate($query);
        
        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getWebResourceId(),
                "update",
                $this->getTitle()
            );
        }

        return true;
    }

    public function updateValid($a_status)
    {
        $query = "UPDATE webr_items " .
            "SET valid = " . $this->db->quote($a_status, ilDBConstants::T_INTEGER) . " " .
            "WHERE link_id = " . $this->db->quote($this->getLinkId(), ilDBConstants::T_INTEGER);
        $res = $this->db->manipulate($query);

        return true;
    }

    public function updateActive($a_status)
    {
        $query = "UPDATE webr_items " .
            "SET active = " . $this->db->quote($a_status, ilDBConstants::T_INTEGER) . " " .
            "WHERE link_id = " . $this->db->quote($this->getLinkId(), ilDBConstants::T_INTEGER);

        $this->db->manipulate($query);

        return true;
    }
    public function updateDisableCheck($a_status)
    {
        $query = "UPDATE webr_items " .
            "SET disable_check = " . $this->db->quote($a_status, ilDBConstants::T_INTEGER) . " " .
            "WHERE link_id = " . $this->db->quote($this->getLinkId(), ilDBConstants::T_INTEGER);
        $res = $this->db->manipulate($query);

        return true;
    }

    public function updateValidByCheck($a_offset = 0)
    {
        if ($a_offset !== 0) {
            $time = time() - $a_offset;

            $query = "UPDATE webr_items " .
                "SET valid = '1' " .
                "WHERE disable_check = '0' " .
                "AND webr_id = " . $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER) . " " .
                "AND last_check < " . $this->db->quote($time, ilDBConstants::T_INTEGER);

        } else {
            $query = "UPDATE webr_items " .
                "SET valid = '1' " .
                "WHERE disable_check = '0' " .
                "AND webr_id = " . $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER);
        }
        $res = $this->db->manipulate($query);
        return true;
    }

    public function updateLastCheck($a_offset = 0)
    {
        if ($a_offset !== 0) {
            $time = time() - $a_offset;

            $query = "UPDATE webr_items " .
                "SET last_check = " . $this->db->quote(time(), ilDBConstants::T_INTEGER) . " " .
                "WHERE webr_id = " . $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER) . " " .
                "AND disable_check = '0' " .
                "AND last_check < " . $this->db->quote($time, ilDBConstants::T_INTEGER);
        } else {
            $query = "UPDATE webr_items " .
                "SET last_check = " . $this->db->quote(time(), ilDBConstants::T_INTEGER) . " " .
                "WHERE webr_id = " . $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER) . " " .
                "AND disable_check = '0' ";
        }
        $res = $this->db->manipulate($query);
        return true;
    }

    public function add($a_update_history = true)
    {
        $this->setLastUpdateDate(time());
        $this->setCreateDate(time());

        $next_id = $this->db->nextId('webr_items');
        $query = "INSERT INTO webr_items (link_id,title,description,target,active,disable_check," .
            "last_update,create_date,webr_id,valid,internal) " .
            "VALUES( " .
            $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($this->getTitle(), ilDBConstants::T_TEXT) . ", " .
            $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ", " .
            $this->db->quote($this->getTarget(), ilDBConstants::T_TEXT) . ", " .
            $this->db->quote($this->getActiveStatus(), ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($this->getDisableCheckStatus(), ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($this->getLastUpdateDate(), ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($this->getCreateDate(), ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($this->getWebResourceId(), ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($this->getValidStatus(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getInternal(), ilDBConstants::T_INTEGER) . ' ' .
            ")";
        $res = $this->db->manipulate($query);

        $link_id = $next_id;
        $this->id = ($link_id);
        
        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getWebResourceId(),
                "add",
                $this->getTitle()
            );
        }

        return $link_id;
    }

    public static function updateTitle($a_link_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'UPDATE webr_items SET ' .
            'title = ' . $ilDB->quote($a_title, ilDBConstants::T_TEXT) . ' ' .
            'WHERE link_id = ' . $ilDB->quote($a_link_id, ilDBConstants::T_INTEGER);
        $ilDB->manipulate($query);
        return true;
    }

    public function addToTarget(string $value) : void
    {
        $this->setTarget($this->getTarget() . $value);
    }

    public function getLinkId() : int
    {
        return $this->id;
    }
    public function setLinkId(int $id) : void
    {
        $this->id = $id;
    }
    public function validate()
    {
        return $this->getTarget() && $this->getTitle();
    }
    public function setWebResourceId(int $a_id)
    {
        $this->webr_id = $a_id;
    }
    public function getWebResourceId() : int
    {
        return $this->webr_id;
    }
    public function setTitle(string $a_title)
    {
        $this->title = $a_title;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function setDescription(string $a_description)
    {
        $this->description = $a_description;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function setTarget(string $a_target)
    {
        $this->target = $a_target;
    }
    public function getTarget() : string
    {
        return $this->target;
    }
    public function setActiveStatus(bool $a_status)
    {
        $this->status = $a_status;
    }
    public function getActiveStatus() : bool
    {
        return $this->status;
    }
    public function setDisableCheckStatus(bool $a_status)
    {
        $this->check = $a_status;
    }
    public function getDisableCheckStatus() : bool
    {
        return $this->check;
    }
    public function setCreateDate(int $a_date)
    {
        $this->c_date = $a_date;
    }
    public function getCreateDate() : int
    {
        return $this->c_date;
    }
    public function setLastUpdateDate(int $a_date)
    {
        $this->m_date = $a_date;
    }
    public function getLastUpdateDate() : int
    {
        return $this->m_date;
    }
    public function setLastCheckDate(?int $a_date)
    {
        $this->last_check = $a_date;
    }
    public function getLastCheckDate() : ?int
    {
        return $this->last_check;
    }
    public function setValidStatus(int $a_status)
    {
        $this->valid = $a_status;
    }
    public function getValidStatus() : int
    {
        return $this->valid;
    }
    public function setInternal(bool $a_status)
    {
        $this->internal = $a_status;
    }
    public function getInternal() : bool
    {
        return $this->internal;
    }
}
