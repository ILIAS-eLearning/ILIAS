<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check task
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTask
{
    public const STATUS_NOT_ATTEMPTED = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_FAILED = 3;

    private int $id = 0;
    private int $grp_id = 0;
    private ?ilDateTime $last_update = null;
    private int $status = 0;
    private string $identifier = '';

    protected ilDBInterface $db;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = $a_id;
        $this->read();
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setGroupId(int $a_id) : void
    {
        $this->grp_id = $a_id;
    }

    public function getGroupId() : int
    {
        return $this->grp_id;
    }

    public function setIdentifier(string $a_ide) : void
    {
        $this->identifier = $a_ide;
    }

    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    public function setLastUpdate(ilDateTime $a_update) : void
    {
        $this->last_update = $a_update;
    }

    public function getLastUpdate() : ilDateTime
    {
        if (!$this->last_update) {
            return $this->last_update = new ilDateTime();
        }
        return $this->last_update;
    }

    public function setStatus(int $a_status) : void
    {
        $this->status = $a_status;
    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public function isActive() : bool
    {
        return true;
    }

    public function read() : bool
    {
        if (!$this->getId()) {
            return false;
        }

        $query = 'SELECT * FROM sysc_tasks ' .
            'WHERE id = ' . $this->db->quote($this->getId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setGroupId($row->grp_id);
            $this->setLastUpdate(new ilDateTime($row->last_update, IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setStatus($row->status);
            $this->setIdentifier($row->identifier);
        }
        return true;
    }

    public function create() : int
    {
        $this->id = $this->db->nextId('sysc_tasks');

        $query = 'INSERT INTO sysc_tasks (id,grp_id,status,identifier) ' .
            'VALUES ( ' .
            $this->db->quote($this->getId(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getGroupId(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getStatus(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getIdentifier(), ilDBConstants::T_TEXT) . ' ' .
            ')';
        $this->db->manipulate($query);
        return $this->getId();
    }

    public function update() : void
    {
        $query = 'UPDATE sysc_tasks SET ' .
            'last_update = ' . $this->db->quote($this->getLastUpdate()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC), ilDBConstants::T_TIMESTAMP) . ', ' .
            'status = ' . $this->db->quote($this->getStatus(), ilDBConstants::T_INTEGER) . ', ' .
            'identifier = ' . $this->db->quote($this->getIdentifier(), ilDBConstants::T_TEXT) . ' ' .
            'WHERE id = ' . $this->db->quote($this->getId(), ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }
}
