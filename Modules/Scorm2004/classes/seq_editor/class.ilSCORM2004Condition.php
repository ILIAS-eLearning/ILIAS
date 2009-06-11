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
class ilSCORM2004Condition extends ilSCORM2004SeqNode
{
	
		
	//db fields
	private $id = null;
	private $seqNodeId = null;
	private $referencedObjective = null;
	private $condition = null;
	private $measureThreshold = 0.0;
	private $operator = null;
	
	/**
	* Constructor
	* @access	public
	*/
	function ilSCORM2004Condition()
	{
		parent::ilSCORM2004SeqNode();
		$this->setNodeName("condition");
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
	
	
	public function getReferencedObjective()
	{
		return $this->referencedObjective;
	}
	
	public function getCondition()
	{
		return $this->condition;
	}
	
	public function getMeasureThreshold()
	{
		return $this->measureThreshold;
	}
	
	public function getOperator()
	{
		return $this->operator;
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
	
	public function setReferencedObjective($a_objective)
	{
		$this->referencedObjective = $a_objective;
	}
	
	public function setCondition($a_condition)
	{
		$this->condition = $a_condition;
	}
	
	public function setMeasureThreshold($a_measure)
	{
		$this->measureThreshold = $a_measure;
	}
	
	public function setOperator($a_operator)
	{
		$this->operator = $a_operator;
	}
	
	// **********************
	// Standard DB Operations for Object
	// **********************
	
	public function insert($a_insert_node = false)
	{
		if ($a_insert_node==true) {$this->setSeqNodeId(parent::insert());}
		$sql = "INSERT INTO sahs_sc13_seq_condition (seqNodeId,referencedObjective,`condition`,measureThreshold,operator)".
				" values(".$this->db->quote($this->seqNodeId).",".$this->db->quote($this->referencedObjective).",".
						   $this->db->quote($this->condition).",".$this->db->quote($this->measureThreshold).",".
						   $this->db->quote($this->operator).");";
		$result = $this->db->query($sql);
		return true;
	}
	
	
}
?>
