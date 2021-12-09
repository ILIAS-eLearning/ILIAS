<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Scope restrictions for advanced md records
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAdvancedMDRecordScope
{
    private int $scope_id;
    private int $record_id;
    private int $ref_id;

    protected ilDBInterface $db;
    protected ilLogger $logger;

    public function __construct(int $a_scope_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->amet();

        $this->scope_id = $a_scope_id;
        $this->read();
    }

    public function setRecordId(int $a_record_id) : void
    {
        $this->record_id = $a_record_id;
    }

    public function getRecordId() : int
    {
        return $this->record_id;
    }

    public function setScopeId(int $a_scope_id) : void
    {
        $this->scope_id = $a_scope_id;
    }

    public function getScopeId() : int
    {
        return $this->scope_id;
    }

    public function setRefId(int $a_ref_id) : void
    {
        $this->ref_id = $a_ref_id;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function save() : void
    {
        // create
        $this->scope_id = $this->db->nextId('adv_md_record_scope');
        $query = 'INSERT INTO adv_md_record_scope (scope_id, record_id, ref_id) ' .
            'VALUES ( ' .
            $this->db->quote($this->scope_id, 'integer') . ', ' .
            $this->db->quote($this->record_id, 'integer') . ', ' .
            $this->db->quote($this->ref_id, 'integer') .
            ')';
        $this->db->manipulate($query);
    }

    public function update() : void
    {
        $this->logger->debug('Update entry.');
        // update (update of record ids not supported)
        $query = 'UPDATE adv_md_record_scope ' .
            'SET ref_id = ' . $this->db->quote($this->ref_id, 'integer') . ' ' .
            'WHERE scope_id = ' . $this->db->quote($this->scope_id, 'integer');
        $this->db->manipulate($query);
    }

    public function delete() : void
    {
        $query = 'DELETE FROM adv_md_record_scope ' .
            'WHERE scope_id = ' . $this->db->quote($this->scope_id, 'integer');
        $this->db->manipulate($query);
    }

    public static function deleteByRecordId(int $a_record_id) : void
    {
        global $DIC;
        $db = $DIC->database();

        $query = 'DELETE FROM adv_md_record_scope ' .
            'WHERE record_id = ' . $db->quote($a_record_id, 'integer');
        $db->manipulate($query);
    }

    protected function read() : void
    {
        if (!$this->scope_id) {
            return;
        }
        $query = 'SELECT * FROM adv_md_record_scope ' .
            'WHERE scope_id = ' . $this->db->quote($this->scope_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->record_id = (int) $row->record_id;
            $this->ref_id = (int) $row->ref_id;
        }
    }
}
