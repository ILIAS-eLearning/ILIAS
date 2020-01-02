<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Scope restrictions for advanced md records
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAdvancedMDRecordScope
{
    /**
     * @var ilDBInterface
     */
    private $db;
    
    private $entry_exists = false;
    
    private $scope_id;
    private $record_id;
    private $ref_id;
    
    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    /**
     * Constructor
     * @param integer $a_scope_id
     */
    public function __construct($a_scope_id = 0)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->logger = $GLOBALS['DIC']->logger()->amet();
        
        $this->scope_id = $a_scope_id;
        $this->read();
    }
    
    public function setRecordId($a_record_id)
    {
        $this->record_id = $a_record_id;
    }
    
    public function getRecordId()
    {
        return $this->record_id;
    }
    
    public function setScopeId($a_scope_id)
    {
        $this->scope_id = $a_scope_id;
    }
    
    public function getScopeId()
    {
        return $this->scope_id;
    }
    
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    
    public function save()
    {
        $this->logger->debug('Create new entry.');
        // create
        $this->scope_id = $this->db->nextId('adv_md_record_scope');
        $query = 'INSERT INTO adv_md_record_scope (scope_id, record_id, ref_id) ' .
            'VALUES ( ' .
            $this->db->quote($this->scope_id, 'integer') . ', ' .
            $this->db->quote($this->record_id, 'integer') . ', ' .
            $this->db->quote($this->ref_id, 'integer') .
            ')';
        $this->db->manipulate($query);
        $this->entry_exists = true;
    }
    
    public function update()
    {
        $this->logger->debug('Update entry.');
        // update (update of record ids not supported)
        $query = 'UPDATE adv_md_record_scope ' .
            'SET ref_id = ' . $this->db->quote($this->ref_id, 'integer') . ' ' .
            'WHERE scope_id = ' . $this->db->quote($this->scope_id, 'integer');
        $this->db->manipulate($query);
    }
    
    
    
    /**
     * Delete one entry
     */
    public function delete()
    {
        $query = 'DELETE FROM adv_md_record_scope ' .
            'WHERE scope_id = ' . $this->db->quote($this->scope_id, 'integer');
        $this->db->manipulate($query);
        $this->entry_exists = false;
        return true;
    }
    
    /**
     * delete by record id
     * @param int $a_record_id Record id
     */
    public static function deleteByRecordI($a_record_id)
    {
        $db = $GLOBALS['DIC']->database();
        
        $query = 'DELETE FROM adv_md_record_scope ' .
            'WHERE record_id = ' . $db->quote($a_record_id, 'integer');
        $db->manipulate($query);
    }

        
    /**
     * Read from db
     */
    protected function read()
    {
        if (!$this->scope_id) {
            return;
        }
        $query = 'SELECT * FROM adv_md_record_scope ' .
            'WHERE scope_id = ' . $this->db->quote($this->scope_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->entry_exists = true;
            $this->record_id = $row->record_id;
            $this->ref_id = $row->ref_id;
        }
    }
}
