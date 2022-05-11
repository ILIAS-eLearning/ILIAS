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
 * Storage of course attributes for assignment rules
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseAttribute
{
    private int $id;
    private int $server_id = 0;
    private int $mid = 0;
    private string $name = '';
    
    private ilDBInterface $db;

    /**
     * Constructor
     */
    public function __construct(int $a_id = 0)
    {
        global $DIC;
        
        $this->db = $DIC->database();
        
        $this->id = $a_id;
        
        $this->read();
    }
    
    /**
     * Get id
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    public function setServerId(int $a_server_id) : void
    {
        $this->server_id = $a_server_id;
    }
    
    public function getServerId() : int
    {
        return $this->server_id;
    }
    
    public function setMid(int $a_mid) : void
    {
        $this->mid = $a_mid;
    }
    
    public function getMid() : int
    {
        return $this->mid;
    }


    public function setName(string $a_name) : void
    {
        $this->name = $a_name;
    }
    
    /**
     * Get name
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Delete attribute
     */
    public function delete() : bool
    {
        $query = "DELETE FROM ecs_crs_mapping_atts " .
                'WHERE id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Save a new entry
     */
    public function save() : bool
    {
        $this->id = $this->db->nextId('ecs_crs_mapping_atts');
        
        $query = 'INSERT INTO ecs_crs_mapping_atts (id,sid,mid,name) ' .
                'VALUES ( ' .
                $this->db->quote($this->getId(), 'integer') . ', ' .
                $this->db->quote($this->getServerId(), 'integer') . ', ' .
                $this->db->quote($this->getMid(), 'integer') . ', ' .
                $this->db->quote($this->getName(), 'text') . ' ' .
                ') ';
        $this->db->manipulate($query);
        return true;
    }


    
    /**
     * read active attributes
     */
    protected function read() : bool
    {
        if (!$this->getId()) {
            return true;
        }
        $query = 'SELECT * FROM ecs_crs_mapping_atts ' .
            'WHERE id = ' . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setName($row->name);
        }
        return true;
    }
}
