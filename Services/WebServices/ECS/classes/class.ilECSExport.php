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
* Storage of an ECS exported object.
* This class stores the econtent id and informations whether an object is exported or not.
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*/
class ilECSExport
{
    protected ilDBInterface $db;

    protected int $server_id = 0;
    protected int $obj_id = 0;
    protected int $econtent_id = 0;
    protected bool $exported = false;

    public function __construct(int $a_server_id, int $a_obj_id)
    {
        global $DIC;

        $this->server_id = $a_server_id;
        $this->obj_id = $a_obj_id;
        
        $this->db = $DIC->database();
        $this->read();
    }

    /**
     * Get server id
     */
    public function getServerId() : int
    {
        return $this->server_id;
    }

    /**
     * Set server id
     */
    public function setServerId(int $a_server_id) : void
    {
        $this->server_id = $a_server_id;
    }
    
    /**
     * Set exported
     *
     * @param bool $a_status export status
     *
     */
    public function setExported(bool $a_status) : void
    {
        $this->exported = $a_status;
    }
    
    /**
     * check if an object is exported or not
     */
    public function isExported() : bool
    {
        return $this->exported;
    }

    /**
     * set econtent id
     *
     * @param int $a_id econtent id (received from ECS::addResource)
     */
    public function setEContentId(int $a_id) : void
    {
        $this->econtent_id = $a_id;
    }
    
    /**
     * get econtent id
     *
     * @return int econtent id
     */
    public function getEContentId() : int
    {
        return $this->econtent_id;
    }
    
    /**
     * Save
     */
    public function save() : bool
    {
        $query = "DELETE FROM ecs_export " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            'AND server_id = ' . $this->db->quote($this->getServerId());
        $this->db->manipulate($query);
    
        if ($this->isExported()) {
            $query = "INSERT INTO ecs_export (server_id,obj_id,econtent_id) " .
                "VALUES ( " .
                $this->db->quote($this->server_id, 'integer') . ', ' .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote($this->getEContentId(), 'integer') . " " .
                ")";
            $this->db->manipulate($query);
        }
        
        return true;
    }
    
    /**
     * Read
     */
    private function read() : void
    {
        $query = "SELECT * FROM ecs_export WHERE " .
            "obj_id = " . $this->db->quote($this->obj_id, 'integer') . " AND " .
            'server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->econtent_id = $row->econtent_id;
            $this->exported = true;
        }
    }
}
