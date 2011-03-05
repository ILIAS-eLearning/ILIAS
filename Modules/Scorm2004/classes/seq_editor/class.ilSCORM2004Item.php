<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqNode.php");

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
class ilSCORM2004Item
{
	
		
	//db fields
	private $id = null;
	private $seqNodeId = null;
	private $treeNodeId = null;
	private $sequencingId = null;
	private $nocopy = false;
	private $nodelete = false;
	private $nomove = false;
	private $importId = null;
	private $seqXml = null;
	private $rootLevel = false;
		
	protected $dom = null;

	/**
	 * Constructor
	 * @access	public
	 */
	function ilSCORM2004Item($a_treeid = null , $a_rootlevel = false)
	{
		//different handling for organization level
		$this->rootLevel = $a_rootlevel;
		
		if ($a_treeid !=null) {
			$this->treeNodeId = $a_treeid;
			$this->loadItem();
			$this->dom = new DOMDocument();
			if ($this->getSeqXml()!="") {
				$this->dom->loadXML($this->getSeqXml());
			} else {
				$element = $this->dom->createElement('sequencing');
				$this->dom->appendChild($element);
				$this->setSeqXml($this->dom->saveXML());
			}
		}
	
	}
	
	
	// **********************
	// GETTER METHODS
	// **********************
	
	public function getSeqNodeId()
	{
		return $this->seqNodeId;
	}
	
	public function getTreeNodeId()
	{
		return $this->treeNodeId;
	}
	
	
	public function getSequencingId()
	{
		return $this->sequencingId;
	}
	
	public function getImportId()
	{
		return $this->importId;
	}
	public function getNocopy()
	{
		return $this->nocopy;
	}
	
	public function getNodelete()
	{
		return $this->nodelete;
	}
	
	public function getNomove()
	{
		return $this->nomove;
	}
	
	public function getSeqXml()
	{
		return $this->seqXml;
	}
	
	public function getRoolLevel()
	{
		return $this->rootLevel;
	}
	
	
	// **********************
	// Setter METHODS
	// **********************

	public function setSeqNodeId($a_seqnodeid)
	{
		$this->seqNodeId = $a_seqnodeid;
	}
	
	public function setTreeNodeId($a_tree_node)
	{
		$this->treeNodeId = $a_tree_node;
	}
	
	public function setSequencingId($a_seq_id)
	{
		$this->sequencingId = $a_seq_id;
	}
	
	public function setNocopy($a_nocopy)
	{
		$this->nocopy = $a_nocopy;
	}
	
	public function setNodelete($a_nodelete)
	{
		$this->nodelete = $a_nodelete ;
	}
	
	public function setNomove($a_nomove)
	{
		$this->nomove = $a_nomove;
	}
	
	public function setImportId($a_importid)
	{
		$this->importid = $a_importid;
	}
	
	public function setSeqXml($a_seqxml)
	{
		$this->seqXml = $a_seqxml;
	}
	
	public function setDom($a_dom)
	{
		$this->dom = $a_dom;
	}
	
	public function setRootLevel($a_rootlevel)
	{
		$this->rootLevel = $a_rootlevel;
	}
	
	public static function getAllowedActions($a_node_id)
	{
		global $ilDB,$ilLog;
		$query = "SELECT * FROM sahs_sc13_seq_item WHERE sahs_sc13_tree_node_id = ".
			$ilDB->quote($a_node_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		return array("copy"=>!$obj_rec['nocopy'],"move"=>!$obj_rec['nomove'],"delete"=>!$obj_rec['nodelete']);
	}
	
	// **********************
	// Scorm2004 Sequencing Export
	// **********************	
	
	public function exportAsXML() {
		
		//remove titles
		$xpath_obj = new DOMXPath($this->dom);
		$obj_node_list = $xpath_obj->query('//objective | //primaryObjective');
		for ($i=0;$i<$obj_node_list->length;$i++) {
			$obj_node_list->item($i)->removeAttribute("title");
		}
		$output = $this->dom->saveXML();
		$output = preg_replace('/\<\?xml version="1.0"\?\>/','',$output);
		$output = preg_replace('/(<)([a-z]+|[A-Z]+)/','<imsss:$2',$output);
		$output = preg_replace('/(<\/)([a-z]+|[A-Z]+)/','</imsss:$2',$output);
		$output = preg_replace('/\n/','',$output);

		return $output; 
	}
	
	// **********************
	// Standard DB Operations for Object
	// **********************
	public function loadItem()
	{
		global $ilDB;
		$query = "SELECT * FROM sahs_sc13_seq_item WHERE (sahs_sc13_tree_node_id = ".$ilDB->quote($this->treeNodeId, "integer").
				  " AND rootlevel =".$ilDB->quote($this->rootLevel, "integer").")";
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		$this->seqXml = $obj_rec['seqxml'];
		$this->importId = $obj_rec['importid'];
		$this->nocopy =  $obj_rec['nocopy'];
		$this->nomove = $obj_rec['nomove'];
		$this->nodelete = $obj_rec['nodelete'];
	}
	
	
	public function update($a_insert_node = false)
	{
		$this->insert();
		/*
		global $ilDB;
		$query = "UPDATE sahs_sc13_seq_item SET seqxml=".$ilDB->quote($this->dom->saveXML())." WHERE sahs_sc13_tree_node_id = ".$ilDB->quote($this->treeNodeId);
		$obj_set = $ilDB->query($query);	
		*/
	}
	
	public function delete($a_insert_node = false)
	{
		global $ilDB;
		$query = "DELETE FROM sahs_sc13_seq_item"." WHERE (sahs_sc13_tree_node_id = ".$ilDB->quote($this->treeNodeId, "integer").
				  " AND rootlevel=".$ilDB->quote($this->rootLevel, "integer").")";
		$obj_set = $ilDB->manipulate($query);	
	}
	
	public function insert($import = false)
	{

		global $ilDB;
		$ilDB->replace("sahs_sc13_seq_item",
			array("sahs_sc13_tree_node_id" => array("integer", $this->treeNodeId)),
			array(
				"importid" => array("text", $this->importId),
				"seqnodeid" => array("integer", (int) $this->seqNodeId),
				"sequencingid" => array("text", $this->sequencingId),
				"nocopy" => array("integer", $this->nocopy),
				"nodelete" => array("integer", $this->nodelete),
				"nomove" => array("integer", $this->nomove),
				"seqxml" => array("text", $this->dom->saveXML()),
				"rootlevel" => array("integer", $this->rootLevel)
				));
/*		$sql = "REPLACE INTO sahs_sc13_seq_item (`importid`,`seqnodeid`, `sahs_sc13_tree_node_id`".
		 		", `sequencingid` ,`nocopy` ,`nodelete` ,`nomove`,`seqxml`,`rootlevel` )".
				 " values(".$ilDB->quote($this->importId).",".$ilDB->quote($this->seqNodeId).",".$ilDB->quote($this->treeNodeId).",".
						   $ilDB->quote($this->sequencingId).",".$ilDB->quote($this->nocopy).",".
						   $ilDB->quote($this->nodelete).",".$ilDB->quote($this->nomove).",".
						   $ilDB->quote($this->dom->saveXML()). ",".$ilDB->quote($this->rootLevel).");";
		$result = $ilDB->query($sql);*/
		return true;
	}
	
	
	
}
?>
