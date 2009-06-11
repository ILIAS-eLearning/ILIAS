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
* Class ilSCORM2004MapInfo
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/ 
class ilSCORM2004MapInfo extends ilSCORM2004SeqNode
{
	
	
	
	//db fields
	private $id = null;
	private $seqNodeId = null;
	private $targetObjectiveID = null;
	private $readSatisfiedStatus = true;
	private $readNormalizedMeasure = true;
	private $writeSatisfiedStatus = false;
	private $writeNormalizedMeasure = false;
	/**
	* Constructor
	* @access	public
	*/
	function ilSCORM2004MapInfo()
	{
		parent::ilSCORM2004SeqNode();
		$this->setNodeName("mapinfo");
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
	
	public function getTargetObjectiveID()
	{
		return $this->targetObjectiveID;
	}
	
	public function getReadSatisfiedStatus()
	{
		return $this->readSatisfiedStatus;
	}
	
	public function getReadNormalizedMeasure()
	{
		return $this->readNormalizedMeasure;
	}
	
	public function getWriteSatisfiedStatus()
	{
		return $this->writeSatisfiedStatus;
	}
	
	public function getWriteNormalizedMeasure()
	{
		return $this->writeNormalizedMeasure;
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
	
	public function setTargetObjectiveID($a_id)
	{
		$this->targetObjectiveID = $a_id;
	}
	
	public function setReadSatisfiedStatus($a_status)
	{
		$this->readSatisfiedStatus = $a_status; 
	}
	
	public function setReadNormalizedMeasure($a_measure)
	{
	 	$this->readNormalizedMeasure = $a_measure;
	}
	
	public function setWriteSatisfiedStatus($a_status)
	{
		$this->writeSatisfiedStatus = $a_status ;
	}
	
	public function setWriteNormalizedMeasure($a_measure)
	{
		$this->writeNormalizedMeasure = $a_measure;
	}
	
	
	// **********************
	// Standard DB Operations for Object
	// **********************
	
	public function insert($a_insert_node = false)
	{
		if ($a_insert_node==true) {$this->setSeqNodeId(parent::insert());}
		$sql = "INSERT INTO sahs_sc13_seq_mapinfo (seqNodeId,targetObjectiveID,readSatisfiedStatus,readNormalizedMeasure,writeSatisfiedStatus,writeNormalizedMeasure)".
				" values(".$this->db->quote($this->seqNodeId).",".$this->db->quote($this->targetObjectiveID).",".
						   $this->db->quote($this->readSatisfiedStatus).",".$this->db->quote($this->readNormalizedMeasure).",".$this->db->quote($this->writeSatisfiedStatus).",".
						   $this->db->quote($this->writeNormalizedMeasure).");";
		$result = $this->db->query($sql);
		return true;
	}
	
	static function fetchmapInfo($a_seq_node_id)
	{
		global $ilDB;
		
		$sql = "SELECT *  FROM sahs_sc13_seq_mapinfo WHERE seqNodeId=".$ilDB->quote($a_seq_node_id).";";
		$result = $ilDB->query($sql);
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$obj = new ilSCORM2004MapInfo();
		foreach ($row as $key=>$value) {
			$method = "set".ucwords($key);
			if (method_exists($obj,$method)) {$obj->$method($value);}
		}
		return $obj;
	}
	
	
}
?>
