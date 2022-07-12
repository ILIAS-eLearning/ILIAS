<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Stores
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLTIProviderObjectSetting
{
    private ?\ilLogger $log = null;
    
    private ?\ilDBInterface $db = null;
    
    private int $ref_id = 0;
    private int $consumer_id = 0;
    private int $admin = 0;
    private int $tutor = 0;
    private int $member = 0;
    
    /**
     * Constructor
     */
    public function __construct(int $a_ref_id, int $a_ext_consumer_id)
    {
        global $DIC;
        
        $this->log = ilLoggerFactory::getLogger('ltis');
        $this->db = $DIC->database();
        
        $this->ref_id = $a_ref_id;
        $this->consumer_id = $a_ext_consumer_id;
        
        $ok = $this->read();
        if (!$ok) {
            $this->log->warning("no ref_id set");
        }
    }
    
    
    public function setAdminRole(int $a_role) : void
    {
        $this->admin = $a_role;
    }
    
    public function getAdminRole() : int
    {
        return $this->admin;
    }
    
    public function setTutorRole(int $a_role) : void
    {
        $this->tutor = $a_role;
    }
    
    public function getTutorRole() : int
    {
        return $this->tutor;
    }
    
    public function setMemberRole(int $a_role) : void
    {
        $this->member = $a_role;
    }
    
    public function getMemberRole() : int
    {
        return $this->member;
    }
    
    /**
     * Set consumer id
     */
    public function setConsumerId(int $a_consumer_id) : void
    {
        $this->consumer_id = $a_consumer_id;
    }
    
    public function getConsumerId() : int
    {
        return $this->consumer_id;
    }


    /**
     * Delete obj setting
     */
    public function delete() : void
    {
        $query = 'DELETE FROM lti_int_provider_obj ' .
            'WHERE ref_id = ' . $this->db->quote($this->ref_id, 'integer') . ' ' .
            'AND ext_consumer_id = ' . $this->db->quote($this->getConsumerId(), 'integer');
        $this->db->manipulate($query);
    }
    
    public function save() : void
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
     */
    protected function read() : bool
    {
        if (!$this->ref_id) {
            return false;
        }
        
        $query = 'SELECT * FROM lti_int_provider_obj ' .
            'WHERE ref_id = ' . $this->db->quote($this->ref_id, 'integer') . ' ' .
            'AND ext_consumer_id = ' . $this->db->quote($this->getConsumerId(), 'integer');
        
        $res = $this->db->query($query);
        while ($row = $res->fetchObject()) {
            $this->ref_id = (int) $row->ref_id;
            $this->consumer_id = (int) $row->ext_consumer_id;
            $this->admin = (int) $row->admin;
            $this->tutor = (int) $row->tutor;
            $this->member = (int) $row->member;
        }
        return true;
    }
}
