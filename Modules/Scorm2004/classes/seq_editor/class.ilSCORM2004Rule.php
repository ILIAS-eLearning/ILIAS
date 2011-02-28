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
* Class ilSCORM2004Rule
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Rule extends ilSCORM2004SeqNode
{
	
	
	
	//db fields
	private $id = null;
	private $seqNodeId = null;
	private $type = null;
	private $action = true;
	/**
	* Constructor
	* @access	public
	*/
	function ilSCORM2004Rule()
	{
		parent::ilSCORM2004SeqNode();
		$this->setNodeName("rule");
	}
	
	
	// **********************
	// GETTER METHODS
	// **********************
	
	public function getSeqNodeId()
	{
		return $this->seqNodeId;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getAction()
	{
		return $this->action;
	}
	
	// **********************
	// Setter METHODS
	// **********************

	public function setSeqNodeId($a_seqnodeid)
	{
		$this->seqNodeId = $a_seqnodeid;
	}
	
	public function setId($a_id)
	{
		$this->id = $a_id;
	}
	
	public function setType($a_type)
	{
		$this->type = $a_type;
	}
	
	public function setAction($a_action)
	{
		$this->action = $a_action; 
	}
	
	
	// **********************
	// Standard DB Operations for Object
	// **********************
	
	public function insert($a_insert_node = false)
	{
		if ($a_insert_node==true) {$this->setSeqNodeId(parent::insert());}
		$sql = "INSERT INTO sahs_sc13_seq_rule (seqnodeid,type,action)".
				" values(".
				$this->db->quote($this->seqNodeId, "integer").",".
				$this->db->quote($this->type, "text").",".
				$this->db->quote($this->action, "text").");";
		$result = $this->db->manipulate($sql);
		return true;
	}
	
	
}
?>
