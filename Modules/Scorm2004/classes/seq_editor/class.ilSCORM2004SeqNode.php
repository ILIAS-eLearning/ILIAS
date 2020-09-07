<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Class ilSCORM2004Condition
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004SeqNode
{
    protected $db = null;
    
    //db fields
    private $nodeName = null;
    private $treenodeId = null;
    
    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC->database();
        $this->db = $ilDB;
    }
    
    
    // **********************
    // GETTER METHODS
    // **********************
    
    public function getNodeName()
    {
        return $this->nodeName;
    }
    
    public function getTreenodeId()
    {
        return $this->treenodeId;
    }
    
        
    // **********************
    // Setter METHODS
    // **********************

    public function setNodeName($a_nodeName)
    {
        $this->nodeName = $a_nodeName;
    }
    
    public function setTreenodeId($a_treenodeId)
    {
        $this->treenodeId = $a_treenodeId;
    }
    
    
    // **********************
    // Standard DB Operations for Object
    // **********************
    
    public function insert()
    {
        $next_id = $this->db->nextId("sahs_sc13_seq_node");
        $sql = "INSERT INTO sahs_sc13_seq_node (seqnodeid, tree_node_id,nodename)" .
                " values(" .
                $this->db->quote($next_id, "integer") . "," .
                $this->db->quote($this->treenodeId, "integer") . "," .
                $this->db->quote($this->nodeName, "text") . ");";
        $result = $this->db->manipulate($sql);
        return $next_id();
    }
    
    public function update($a_seq_node_id)
    {
        $sql = "UPDATE sahs_sc13_seq_node SET " .
                "tree_node_id = " . $this->db->quote($this->treenodeId, "integer") . "," .
                "nodename=" . $this->db->quote($this->nodeName, "text") .
                " WHERE seqnodeid=" . $this->db->quote($a_seq_node_id, "integer");
        $result = $this->db->manipulate($sql);
        return;
    }
}
