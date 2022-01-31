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
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSExport
{
    protected ilDBInterface $db;

    protected $server_id = 0;
    protected int $obj_id = 0;
    protected $econtent_id = 0;
    protected $exported = false;

    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_server_id, $a_obj_id)
    {
        global $DIC;

        $this->server_id = $a_server_id;
        $this->obj_id = $a_obj_id;
        
        $this->db = $DIC->database();
        $this->read();
    }

    /**
     * Get server id
     * @return int
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    /**
     * Set server id
     * @param int $a_server_id
     */
    public function setServerId($a_server_id)
    {
        $this->server_id = $a_server_id;
    }
    
    /**
     * Set exported
     *
     * @access public
     * @param bool export status
     *
     */
    public function setExported($a_status)
    {
        $this->exported = $a_status;
    }
    
    /**
     * check if an object is exported or not
     *
     * @access public
     *
     */
    public function isExported()
    {
        return (bool) $this->exported;
    }

    /**
     * set econtent id
     *
     * @access public
     * @param int econtent id (received from ECS::addResource)
     *
     */
    public function setEContentId($a_id)
    {
        $this->econtent_id = $a_id;
    }
    
    /**
     * get econtent id
     *
     * @access public
     * @return int econtent id
     *
     */
    public function getEContentId()
    {
        return $this->econtent_id;
    }
    
    /**
     * Save
     *
     * @access public
     */
    public function save()
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
     * @access private
     */
    private function read()
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
