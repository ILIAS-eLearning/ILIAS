<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourGroup
{
    private int $group_id = 0;
    private int $usr_id = 0;
    private int $num_assignments = 1;
    private string $title = '';

    protected ilDBInterface $db;

    public function __construct(int $a_group_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->group_id = $a_group_id;
        $this->read();
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function setUserId(int $a_id): void
    {
        $this->usr_id = $a_id;
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function setMaxAssignments(int $a_num): void
    {
        $this->num_assignments = $a_num;
    }

    public function getMaxAssignments(): int
    {
        return $this->num_assignments;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function save(): int
    {
        $this->group_id = $this->db->nextId('cal_ch_group');
        $query = 'INSERT INTO cal_ch_group (grp_id,usr_id,multiple_assignments,title) ' .
            'VALUES ( ' .
            $this->db->quote($this->getGroupId(), 'integer') . ', ' .
            $this->db->quote($this->getUserId(), 'integer') . ', ' .
            $this->db->quote($this->getMaxAssignments(), 'integer') . ', ' .
            $this->db->quote($this->getTitle(), 'text') .
            ')';
        $this->db->manipulate($query);
        return $this->getGroupId();
    }

    public function update(): void
    {
        $query = 'UPDATE cal_ch_group SET ' .
            'usr_id = ' . $this->db->quote($this->getUserId(), 'integer') . ', ' .
            'multiple_assignments = ' . $this->db->quote($this->getMaxAssignments(), 'integer') . ', ' .
            'title = ' . $this->db->quote($this->getTitle(), 'text') . ' ' .
            'WHERE grp_id = ' . $this->db->quote($this->getGroupId(), 'integer');
        $this->db->manipulate($query);
    }

    public function delete(): void
    {
        $query = 'DELETE FROM cal_ch_group ' .
            'WHERE grp_id = ' . $this->db->quote($this->getGroupId(), 'integer');
        $this->db->manipulate($query);
        ilBookingEntry::resetGroup($this->getGroupId());
    }

    protected function read(): void
    {
        if (!$this->getGroupId()) {
            return;
        }
        $query = 'SELECT * FROM cal_ch_group ' .
            'WHERE grp_id = ' . $this->db->quote($this->getGroupId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setUserId((int) $row->usr_id);
            $this->setTitle($row->title);
            $this->setMaxAssignments((int) $row->multiple_assignments);
        }
    }
}
