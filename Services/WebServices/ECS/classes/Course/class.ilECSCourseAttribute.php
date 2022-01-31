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
    private $id = 0;
    private int $server_id = 0;
    private int $mid = 0;
    private string $name = '';
    
    private ilLogger $logger;
    private ilDBInterface $db;

    /**
     * Constructor
     * @param int $attribute_id
     */
    public function __construct($a_id = 0)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        $this->db = $DIC->database();
        
        $this->id = $a_id;
        
        $this->read();
    }
    
    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setServerId($a_server_id)
    {
        $this->server_id = $a_server_id;
    }
    
    public function getServerId()
    {
        return $this->server_id;
    }
    
    public function setMid($a_mid)
    {
        $this->mid = $a_mid;
    }
    
    public function getMid()
    {
        return $this->mid;
    }


    public function setName($a_name)
    {
        $this->name = $a_name;
    }
    
    /**
     * Get name
     * @return type
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Delete attribute
     * @return boolean
     */
    public function delete()
    {
        $query = "DELETE FROM ecs_crs_mapping_atts " .
                'WHERE id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Save a new entry
     * @return boolean
     */
    public function save()
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
    protected function read()
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
