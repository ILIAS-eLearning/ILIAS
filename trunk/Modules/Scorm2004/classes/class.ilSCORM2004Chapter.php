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
	function ilSCORM2004Chapter($a_slm_object, $a_id = 0)
	{
		parent::ilSCORM2004Node($a_slm_object, $a_id);
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
			$obj =& ilSCORM2004NodeFactory::getInstance($this->slm_object, $child["obj_id"], false);
			if (is_object($obj))
			{
				if ($obj->getType() == "chap")
				{
					$obj->delete_rec($a_tree, $a_delete_meta_data);
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
		$new_md =& $md->cloneMD($a_target_slm->getId(), $chap->getId(), $this->getType());
		
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
/*
		global $ilBench;

		$expLog->write(date("[y-m-d H:i:s] ")."Structure Object ".$this->getId());
		$attrs = array();
		$a_xml_writer->xmlStartTag("StructureObject", $attrs);

		// MetaData
		$ilBench->start("ContentObjectExport", "exportStructureObject_exportMeta");
		$this->exportXMLMetaData($a_xml_writer);
		$ilBench->stop("ContentObjectExport", "exportStructureObject_exportMeta");

		// StructureObjects
		$ilBench->start("ContentObjectExport", "exportStructureObject_exportPageObjects");
		$this->exportXMLPageObjects($a_xml_writer, $a_inst);
		$ilBench->stop("ContentObjectExport", "exportStructureObject_exportPageObjects");

		// PageObjects
		$this->exportXMLStructureObjects($a_xml_writer, $a_inst, $expLog);

		// Layout
		// not implemented

		$a_xml_writer->xmlEndTag("StructureObject");
*/
	}


	/**
	* export structure objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
// @todo
/*
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getLMId(), $this->getId(), $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
*/
	}

// @todo Check this
/*
	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{

		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = "il_".IL_INST_ID."_st_".$this->getId();
			//$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}
*/

	/**
	* get presentation title
	*
	*/
// @todo Check this
	function _getPresentationTitle($a_st_id, $a_include_numbers = false)
	{
		global $ilDB;
/*
		// get chapter data
		$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_st_id);
		$st_set = $ilDB->query($query);
		$st_rec = $st_set->fetchRow(DB_FETCHMODE_ASSOC);

		$tree = new ilTree($st_rec["lm_id"]);
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		if ($a_include_numbers)
		{
			if ($tree->isInTree($st_rec["obj_id"]))
			{
				// get chapter tree node
				$query = "SELECT * FROM lm_tree WHERE child = ".
					$ilDB->quote($a_st_id)." AND lm_id = ".
					$ilDB->quote($st_rec["lm_id"]);
				$tree_set = $ilDB->query($query);
				$tree_node = $tree_set->fetchRow(DB_FETCHMODE_ASSOC);
				$depth = $tree_node["depth"];

				$nr = $tree->getChildSequenceNumber($tree_node, "st")." ";
				for ($i = $depth - 1; $i > 1; $i --)
				{
					// get next parent tree node
					$query = "SELECT * FROM lm_tree WHERE child = ".
						$ilDB->quote($tree_node["parent"])." AND lm_id = ".
						$ilDB->quote($st_rec["lm_id"]);
					$tree_set = $ilDB->query($query);
					$tree_node = $tree_set->fetchRow(DB_FETCHMODE_ASSOC);
					$seq = $tree->getChildSequenceNumber($tree_node, "st");

					$nr = $seq.".".$nr;
				}
			}
		}

		return $nr.$st_rec["title"];
*/
	}



	/**
	* export page objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst = 0)
	{
// @todo Check this
/*
		include_once './Modules/LearningModule/classes/class.ilLMPageObject.php';

		global $ilBench;

		$this->tree = new ilTree($this->getLmId());
		$this->tree->setTableNames('lm_tree', 'lm_data');
		$this->tree->setTreeTablePK("lm_id");

		$childs = $this->tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			if($child["type"] != "pg")
			{
				continue;
			}

			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportStructureObject_exportPageObjectAlias");
			//$ilBench->start("ContentObjectExport", "exportStructureObject_getLMPageObject");
			//$page_obj = new ilLMPageObject($this->getContentObject(), $child["obj_id"]);
			//$ilBench->stop("ContentObjectExport", "exportStructureObject_getLMPageObject");
			ilLMPageObject::_exportXMLAlias($a_xml_writer, $child["obj_id"], $a_inst);
			//$page_obj->exportXML($a_xml_writer, "alias", $a_inst);
			//unset($page_obj);
			$ilBench->stop("ContentObjectExport", "exportStructureObject_exportPageObjectAlias");
		}
*/
	}


	/**
	* export (sub)structure objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLStructureObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
// @todo Check this
/*
		$this->tree = new ilTree($this->getLmId());
		$this->tree->setTableNames('lm_tree', 'lm_data');
		$this->tree->setTreeTablePK("lm_id");

		$childs = $this->tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			if($child["type"] != "st")
			{
				continue;
			}

			// export xml to writer object
			$structure_obj = new ilStructureObject($this->getContentObject(),
				$child["obj_id"]);
			$structure_obj->exportXML($a_xml_writer, $a_inst, $expLog);
			unset($structure_obj);
		}
*/
	}

	/**
	* export object to fo
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
// @todo Check this
/*
	function exportFO(&$a_xml_writer)
	{
		global $ilBench;

		//$expLog->write(date("[y-m-d H:i:s] ")."Structure Object ".$this->getId());

		// fo:block (complete)
		$attrs = array();
		$attrs["font-family"] = "Times";
		$attrs["font-size"] = "14pt";
		$a_xml_writer->xmlElement("fo:block", $attrs, $this->getTitle());

		// page objects
		//$ilBench->start("ContentObjectExport", "exportStructureObject_exportPageObjects");
		$this->exportFOPageObjects($a_xml_writer);
		//$ilBench->stop("ContentObjectExport", "exportStructureObject_exportPageObjects");

		// structure objects
		//$this->exportFOStructureObjects($a_xml_writer);

	}
*/

	/**
	* export page objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportFOPageObjects(&$a_xml_writer)
	{
// @todo Check this
/*
		global $ilBench;

		$this->tree = new ilTree($this->getLmId());
		$this->tree->setTableNames('lm_tree', 'lm_data');
		$this->tree->setTreeTablePK("lm_id");

		$childs = $this->tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			if($child["type"] != "pg")
			{
				continue;
			}

			// export xml to writer object
			//$ilBench->start("ContentObjectExport", "exportStructureObject_exportPageObjectAlias");

			$page_obj = new ilLMPageObject($this->getContentObject(), $child["obj_id"]);
			$page_obj->exportFO($a_xml_writer);

			//$ilBench->stop("ContentObjectExport", "exportStructureObject_exportPageObjectAlias");
		}
*/
	}

}
?>
