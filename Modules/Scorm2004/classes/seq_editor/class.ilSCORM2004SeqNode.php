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
	function ilSCORM2004SeqNode()
	{
		global $ilDB;
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
		$sql = "INSERT INTO sahs_sc13_seq_node (tree_node_id,nodeName)".
				" values(".$this->db->quote($this->treenodeId).",".$this->db->quote($this->nodeName).");";
		$result = $this->db->query($sql);
		return $this->db->getLastInsertId();
	}
	
	public function update($a_seq_node_id)
	{
		$sql = "UPDATE sahs_sc13_seq_node SET ".
				"tree_node_id=".$this->db->quote($this->treenodeId).",nodeName=".$this->db->quote($this->nodeName).
				" WHERE seqNodeId=".$this->db->quote($a_seq_node_id);
		$result = $this->db->query($sql);
		return $this->db->getLastInsertId();
	    ;
	}
	
	
}
?>
