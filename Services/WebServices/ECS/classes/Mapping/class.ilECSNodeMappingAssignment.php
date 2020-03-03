<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    private $title_update = false;
    private $position_update = false;
    private $tree_update = false;
    
    private $mapped = false;

    /**
     * Constructor
     */
    public function __construct($a_server_id, $mid, $cs_root, $cs_id)
    {
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
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->delete();
        $this->create();
    }

    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'INSERT INTO ecs_node_mapping_a (server_id,mid,cs_root,cs_id,ref_id,obj_id,title_update,position_update,tree_update) ' .
            'VALUES( ' .
            $ilDB->quote($this->getServerId(), 'integer') . ', ' .
            $ilDB->quote($this->getMemberId(), 'integer') . ', ' .
            $ilDB->quote($this->getTreeId(), 'integer') . ', ' .
            $ilDB->quote($this->getCSId(), 'integer') . ', ' .
            $ilDB->quote($this->getRefId(), 'integer') . ', ' .
            $ilDB->quote($this->getObjId(), 'integer') . ', ' .
            $ilDB->quote($this->isTitleUpdateEnabled(), 'integer') . ', ' .
            $ilDB->quote($this->isPositionUpdateEnabled(), 'integer') . ', ' .
            $ilDB->quote($this->isTreeUpdateEnabled(), 'integer') . ' ' .
            ')';
        $ilDB->manipulate($query);
        return true;
    }


    /**
     * Delete entry
     * @global ilDB $ilDB
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($this->getServerId(), 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($this->getMemberId(), 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($this->getTreeId(), 'integer') . ' ' .
            'AND cs_id = ' . $ilDB->quote($this->getCSId(), 'integer');
        $ilDB->manipulate($query);
    }



    /**
     * read settings
     * @global ilDB $ilDB
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($this->getServerId(), 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($this->getMemberId(), 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($this->getTreeId(), 'integer') . ' ' .
            'AND cs_id = ' . $ilDB->quote($this->getCSId(), 'integer') . ' ';
        $res = $ilDB->query($query);
        
        #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.$query);
        
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
