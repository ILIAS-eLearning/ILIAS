<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings for LO courses
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestAssignment
{
    private int $assignment_id = 0;
    private int $container_id = 0;
    private int $assignment_type = 0;
    private int $objective_id = 0;
    private int $test_ref_id = 0;

    protected ilDBInterface $db;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setAssignmentId($a_id);
        $this->read();
    }

    public function setAssignmentId(int $a_id) : void
    {
        $this->assignment_id = $a_id;
    }

    public function getAssignmentId() : int
    {
        return $this->assignment_id;
    }

    public function setContainerId(int $a_id) : void
    {
        $this->container_id = $a_id;
    }

    public function getContainerId() : int
    {
        return $this->container_id;
    }

    public function setAssignmentType(int $a_type) : void
    {
        $this->assignment_type = $a_type;
    }

    public function getAssignmentType() : int
    {
        return $this->assignment_type;
    }

    public function setObjectiveId(int $a_id) : void
    {
        $this->objective_id = $a_id;
    }

    public function getObjectiveId() : int
    {
        return $this->objective_id;
    }

    public function setTestRefId(int $a_id) : void
    {
        $this->test_ref_id = $a_id;
    }

    public function getTestRefId() : int
    {
        return $this->test_ref_id;
    }

    public function save() : void
    {
        if ($this->getAssignmentId()) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function create() : void
    {
        $this->setAssignmentId($this->db->nextId('loc_tst_assignments'));
        $query = 'INSERT INTO loc_tst_assignments (assignment_id, container_id, assignment_type, objective_id, tst_ref_id) ' .
            'VALUES ( ' .
            $this->db->quote($this->getAssignmentId(), 'integer') . ', ' .
            $this->db->quote($this->getContainerId(), 'integer') . ', ' .
            $this->db->quote($this->getAssignmentType(), 'integer') . ', ' .
            $this->db->quote($this->getObjectiveId(), 'integer') . ', ' .
            $this->db->quote($this->getTestRefId(), 'integer') . ' ' .
            ') ';
        $this->db->manipulate($query);
    }

    public function update() : void
    {
        $query = 'UPDATE loc_tst_assignments ' .
            'SET container_id = ' . $this->db->quote($this->getContainerId(), 'integer') . ', ' .
            'assignment_type = ' . $this->db->quote($this->getAssignmentType(), 'integer') . ', ' .
            'objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer') . ', ' .
            'tst_ref_id = ' . $this->db->quote($this->getTestRefId(), 'integer') . ' ' .
            'WHERE assignment_id = ' . $this->db->quote($this->getAssignmentId(), 'integer');
        $this->db->manipulate($query);
    }

    public function delete() : void
    {
        $query = 'DELETE FROM loc_tst_assignments ' .
            'WHERE assignment_id = ' . $this->db->quote($this->getAssignmentId(), 'integer') . ' ';
        $this->db->manipulate($query);
    }

    public function read() : void
    {
        if (!$this->getAssignmentId()) {
            return;
        }

        $query = 'SELECT * FROM loc_tst_assignments ' .
            'WHERE assignment_id = ' . $this->db->quote($this->getAssignmentId(), 'integer') . ' ';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setContainerId((int) $row->container_id);
            $this->setObjectiveId((int) $row->objective_id);
            $this->setAssignmentType((int) $row->assignment_type);
            $this->setTestRefId((int) $row->tst_ref_id);
        }
    }

    public function cloneSettings(int $a_copy_id, int $a_target_id, int $a_objective_id) : void
    {
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $options->getMappings();

        if (!array_key_exists($this->getTestRefId(), $mappings)) {
            return;
        }

        $copy = new ilLOTestAssignment();
        $copy->setContainerId($a_target_id);
        $copy->setAssignmentType($this->getAssignmentType());
        $copy->setObjectiveId($a_objective_id);
        $copy->setTestRefId($mappings[$this->getTestRefId()]);
        $copy->create();
    }
}
