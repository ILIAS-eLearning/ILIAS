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
* Class ilSCORM2004Objective
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004MapInfo.php");


class ilSCORM2004Objective extends ilSCORM2004Item
{
	
	//db fields
	
	private $node = null;
	private $id = null;  //userd as ID
	private $objectiveID = null; //used as title
	
	//not supported in GUI yet
	private $minNormalizedMeasure = 1.0;
	private $primary = true;
	private $satisfiedByMeasure = false;
	
	//mappings
	private $mappings =array();
	
	/**
	* Constructor
	* @access	public
	*/
	function ilSCORM2004Objective($a_treeid = null, $a_obj_id = null)
	{
		parent::ilSCORM2004Item($a_treeid);
		
		if ($a_obj_id !=null && $a_treeid != null) {
			$xpath_obj = new DOMXPath($this->dom);
			$obj_node_list = $xpath_obj->query('//objective[@objectiveID = "'.$a_obj_id.'"] | '.
											   '//primaryObjective[@objectiveID = "'.$a_obj_id.'"]');
			$this->setNode($obj_node_list->item(0));
		} else {
			if ($a_obj_id ==null &&  $a_treeid != null) {
				$obj_con = $this->dom->createElement("objectives");
				$obj = $this->dom->createElement("primaryObjective");
				$root = $this->dom->getElementsByTagName("sequencing")->item(0);
				$obj_con->appendChild($obj);
				$root->appendChild($obj_con);
				$this->node =$this->dom->getElementsByTagName("primaryObjective")->item(0);
			}
		}
	}
	
	
	
	// **********************
	// GETTER METHODS
	// **********************
	
	public function getId()
	{
		return $this->node->getAttribute("objectiveID");
	}
	
	public function getMinNormalizedMeasure()
	{
		return $this->node->getAttribute("minNormalizedMeasure");
	}
	
	public function getObjectiveID()
	{
		return $this->node->getAttribute("title");
	}
	
	public function getPrimary()
	{
		return $this->primary;
	}
	
	public function getSatisfiedByMeasure()
	{
		return $this->node->getAttribute("satisfiedByMeasure");
	}
	
	public function getMappings()
	{
		return $this->mappings;
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
		$this->node->setAttribute("objectiveID",$a_id);
	}
	
	
	public function setMinNormalizedMeasure($a_minmeasure)
	{
		$this->node->setAttribute("minNormalizedMeasure",$a_minmeasure);
	}
	
	public function setObjectiveID($a_objectiveid)
	{
		$this->node->setAttribute("title",$a_objectiveid);
	}
	
	public function setPrimary($a_primary)
	{
		$this->primary = $a_primary;
	}
	
	public function setSatisfiedByMeasure($a_satisfied)
	{
		$this->node->setAttribute("satisfiedByMeasure",$a_satisfied);
	}
	
	public function setMappings($a_mappings)
	{
		$this->mappings = $a_mappings;
	}
	
	public function setNode($a_node)
	{
		$this->node = $a_node;
	}
	
	public function setDom($a_dom)
	{
		$this->dom = $a_dom;
	}
	
	// **********************
	// Standard  Operations for Object
	// **********************

	
	public function updateObjective() {
		parent::update();
	}
	
	static function fetchAllObjectives($a_slm_object,$a_tree_node_id) 
	{
		global $ilDB;
		
		$objectives = array();
		$seq_item = new ilSCORM2004Item($a_tree_node_id);
		$xpath_obj = new DOMXPath($seq_item->dom);
		$obj_node_list = $xpath_obj->query('//objective | //primaryObjective');
		for ($i=0;$i<$obj_node_list->length;$i++) {
			$obj = new ilSCORM2004Objective();
			$obj->setNode($obj_node_list->item($i));
			$mapping_node_list = $xpath_obj->query('//objective | //primaryObjective');
			//check for mapping
			array_push($objectives,$obj);
		}
		return $objectives;
		
	}
	
}
?>
