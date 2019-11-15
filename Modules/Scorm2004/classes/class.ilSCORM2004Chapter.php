<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

/**
* Class ilSCORM2004Chapter
*
* Chapter class for SCORM 2004 Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Chapter extends ilSCORM2004Node
{
	var $tree;

	/**
	* Constructor
	* @access	public
	*/
	function __construct($a_slm_object, $a_id = 0)
	{
		parent::__construct($a_slm_object, $a_id);
		$this->setType("chap");
	}

	/**
	* Delete a chapter
	*/
	function delete($a_delete_meta_data = true)
	{
		$node_data = $this->tree->getNodeData($this->getId());
		$this->delete_rec($a_delete_meta_data);
		$this->tree->deleteTree($node_data);
		parent::deleteSeqInfo();
	}

	/**
	* Delete data records of chapter (and nested objects)
	*/
	private function delete_rec($a_delete_meta_data = true)
	{
		$childs = $this->tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			$obj = ilSCORM2004NodeFactory::getInstance($this->slm_object, $child["obj_id"], false);
			if (is_object($obj))
			{
				if ($obj->getType() == "chap")
				{
					$obj->delete_rec($a_delete_meta_data);
				}
				if ($obj->getType() == "sco")
				{
					$obj->delete($a_delete_meta_data);
				}
			}
			unset($obj);
		}
		parent::delete($a_delete_meta_data);
	}

	/**
	 * Create asset
	 */
	function create($a_upload = false, $a_template = false)
	{
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
		parent::create($a_upload);
		if (!$a_template) {
			$this->insertDefaultSequencingItem();
		}
	}

	/**
	 * Insert default sequencing item
	 *
	 * @param
	 * @return
	 */
	function insertDefaultSequencingItem()
	{
		$seq_item = new ilSCORM2004Item($this->getId());
		$seq_item->setDefaultXml(true);
		$seq_item->insert();
	}

	/**
	* Copy chapter
	*/
	function copy($a_target_slm)
	{
		$chap = new ilSCORM2004Chapter($a_target_slm);
		$chap->setTitle($this->getTitle());
		if ($this->getSLMId() != $a_target_slm->getId())
		{
			$chap->setImportId("il__chap_".$this->getId());
		}
		$chap->setSLMId($a_target_slm->getId());
		$chap->setType($this->getType());
		$chap->setDescription($this->getDescription());
		$chap->create(true);
		$a_copied_nodes[$this->getId()] = $chap->getId();
		
		// copy meta data
		include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$new_md = $md->cloneMD($a_target_slm->getId(), $chap->getId(), $this->getType());
		
		return $chap;
	}

	/**
	* Export object to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXML(&$a_xml_writer, $a_inst, &$expLog)
	{
// @todo
	}


	/**
	* export structure objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getSLMId(), $this->getId(), $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}
}
?>
