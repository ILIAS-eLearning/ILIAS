<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderObjectSetting
{
    /**
     * @var ilLogger
     */
    private $log = null;
    
    /**
     * @var ilDBInterface
     */
    private $db = null;
    
    private $ref_id = 0;
    private $consumer_id = 0;
    private $admin = false;
    private $tutor = false;
    private $member = false;
    
    /**
     * Constructor
     * @global type $DIC
     */
    public function __construct($a_ref_id, $a_ext_consumer_id)
    {
        global $DIC;
        
        $this->log = $DIC->logger()->lti();
        $this->db = $DIC->database();
        
        $this->ref_id = $a_ref_id;
        $this->consumer_id = $a_ext_consumer_id;
        
        $this->read();
    }
    
    
    public function setAdminRole($a_role)
    {
        $this->admin = $a_role;
    }
    
    public function getAdminRole()
    {
        return $this->admin;
    }
    
    public function setTutorRole($a_role)
    {
        $this->tutor = $a_role;
    }
    
    public function getTutorRole()
    {
        return $this->tutor;
    }
    
    public function setMemberRole($a_role)
    {
        $this->member = $a_role;
    }
    
    public function getMemberRole()
    {
        return $this->member;
    }
    
    /**
     * Set consumer id
     * @param int $a_consumer_id
     */
    public function setConsumerId($a_consumer_id)
    {
        $this->consumer_id = $a_consumer_id;
    }
    
    public function getConsumerId()
    {
        return $this->consumer_id;
    }


    /**
     * Delete obj setting
     */
    public function delete()
    {
        $query = 'DELETE FROM lti_int_provider_obj ' .
            'WHERE ref_id = ' . $this->db->quote($this->ref_id, 'integer') . ' ' .
            'AND ext_consumer_id = ' . $this->db->quote($this->getConsumerId(), 'integer');
        $this->db->manipulate($query);
    }
    
    public function save()
    {
        $this->delete();
        
        $query = 'INSERT INTO lti_int_provider_obj ' .
            '(ref_id,ext_consumer_id,admin,tutor,member) VALUES( ' .
            $this->db->quote($this->ref_id, 'integer') . ', ' .
            $this->db->quote($this->getConsumerId(), 'integer') . ', ' .
            $this->db->quote($this->getAdminRole(), 'integer') . ', ' .
            $this->db->quote($this->getTutorRole(), 'integer') . ', ' .
            $this->db->quote($this->getMemberRole(), 'integer') .
            ' )';
        $this->db->manipulate($query);
    }

    /**
     * Read object settings
     * @return boolean
     */
    protected function read()
    {
        if (!$this->ref_id) {
            return false;
        }
        
        $query = 'SELECT * FROM lti_int_provider_obj ' .
            'WHERE ref_id = ' . $this->db->quote($this->ref_id, 'integer') . ' ' .
            'AND ext_consumer_id = ' . $this->db->quote($this->getConsumerId(), 'integer');
        
        $res = $this->db->query($query);
        while ($row = $res->fetchObject()) {
            $this->ref_id = $row->ref_id;
            $this->consumer_id = $row->ext_consumer_id;
            $this->admin = $row->admin;
            $this->tutor = $row->tutor;
            $this->member = $row->member;
        }
    }
}
