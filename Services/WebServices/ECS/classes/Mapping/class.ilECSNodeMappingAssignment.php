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
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSNodeMappingAssignment
{
    private $server_id;
    private $mid;
    private $cs_root;
    private $cs_id;
    private $ref_id;
    private $obj_id;

    private bool $title_update = false;
    private bool $position_update = false;
    private bool $tree_update = false;
    
    private bool $mapped = false;
    
    private ilDBInterface $db;

    /**
     * Constructor
     */
    public function __construct($a_server_id, $mid, $cs_root, $cs_id)
    {
        global $DIC;
        
        $this->db = $DIC->database();
        
        $this->server_id = $a_server_id;
        $this->mid = $mid;
        $this->cs_root = $cs_root;
        $this->cs_id = $cs_id;

        $this->read();
    }
    
    public function isMapped()
    {
        return $this->mapped;
    }
    
    public function getServerId()
    {
        return $this->server_id;
    }
    
    public function setServerId($a_id)
    {
        $this->server_id = $a_id;
    }

    public function setMemberId($a_member_id)
    {
        $this->mid = $a_member_id;
    }

    public function getMemberId()
    {
        return $this->mid;
    }
    
    public function getTreeId()
    {
        return $this->cs_root;
    }

    public function setTreeId($root)
    {
        $this->cs_root = $root;
    }

    public function getCSId()
    {
        return $this->cs_id;
    }

    public function setCSId($id)
    {
        $this->cs_id = $id;
    }

    public function getRefId()
    {
        return $this->ref_id;
    }

    public function setRefId($a_id)
    {
        $this->ref_id = $a_id;
    }

    public function getObjId()
    {
        return $this->obj_id;
    }

    public function setObjId($id)
    {
        $this->obj_id = $id;
    }

    public function isTitleUpdateEnabled()
    {
        return $this->title_update;
    }

    public function enableTitleUpdate($enabled)
    {
        $this->title_update = $enabled;
    }

    public function isPositionUpdateEnabled()
    {
        return $this->position_update;
    }

    public function enablePositionUpdate($enabled)
    {
        $this->position_update = $enabled;
    }

    public function isTreeUpdateEnabled()
    {
        return $this->tree_update;
    }

    public function enableTreeUpdate($enabled)
    {
        $this->tree_update = $enabled;
    }

    /**
     * Update node mapping
     */
    public function update()
    {
        $this->delete();
        $this->create();
    }

    public function create()
    {
        $query = 'INSERT INTO ecs_node_mapping_a (server_id,mid,cs_root,cs_id,ref_id,obj_id,title_update,position_update,tree_update) ' .
            'VALUES( ' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote($this->getMemberId(), 'integer') . ', ' .
            $this->db->quote($this->getTreeId(), 'integer') . ', ' .
            $this->db->quote($this->getCSId(), 'integer') . ', ' .
            $this->db->quote($this->getRefId(), 'integer') . ', ' .
            $this->db->quote($this->getObjId(), 'integer') . ', ' .
            $this->db->quote($this->isTitleUpdateEnabled(), 'integer') . ', ' .
            $this->db->quote($this->isPositionUpdateEnabled(), 'integer') . ', ' .
            $this->db->quote($this->isTreeUpdateEnabled(), 'integer') . ' ' .
            ')';
        $this->db->manipulate($query);
        return true;
    }


    /**
     * Delete entry
     */
    public function delete()
    {
        $query = 'DELETE FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND mid = ' . $this->db->quote($this->getMemberId(), 'integer') . ' ' .
            'AND cs_root = ' . $this->db->quote($this->getTreeId(), 'integer') . ' ' .
            'AND cs_id = ' . $this->db->quote($this->getCSId(), 'integer');
        $this->db->manipulate($query);
    }



    /**
     * read settings
     */
    protected function read()
    {
        $query = 'SELECT * FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND mid = ' . $this->db->quote($this->getMemberId(), 'integer') . ' ' .
            'AND cs_root = ' . $this->db->quote($this->getTreeId(), 'integer') . ' ' .
            'AND cs_id = ' . $this->db->quote($this->getCSId(), 'integer') . ' ';
        $res = $this->db->query($query);
                
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setObjId($row->obj_id);
            $this->setRefId($row->ref_id);
            $this->enableTitleUpdate($row->title_update);
            $this->enablePositionUpdate($row->position_update);
            $this->enableTreeUpdate($row->tree_update);
            $this->mapped = true;
        }
    }
    
    public static function deleteByServerId($a_server_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_node_mapping_a' .
            ' WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
}
