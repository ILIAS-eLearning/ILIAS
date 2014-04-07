<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/LearningModule/classes/class.ilLMObject.php");

/**
* Class ilStructreObject
*
* Handles StructureObjects of ILIAS Learning Modules (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilStructureObject extends ilLMObject
{
	var $is_alias;
	var $origin_id;
	var $tree;

	/**
	* Constructor
	* @access	public
	*/
	function ilStructureObject(&$a_content_obj, $a_id = 0)
	{
		$this->setType("st");
		parent::ilLMObject($a_content_obj, $a_id);
	}

	function create($a_upload = false)
	{
		parent::create($a_upload);
	}

	/**
	* Delete Chapter
	*/
	function delete($a_delete_meta_data = true)
	{
		// only relevant for online help authoring
		include_once("./Services/Help/classes/class.ilHelpMapping.php");
		ilHelpMapping::removeScreenIdsOfChapter($this->getId());

		$this->tree = new ilTree($this->getLmId());
		$this->tree->setTableNames('lm_tree', 'lm_data');
		$this->tree->setTreeTablePK("lm_id");
		$node_data = $this->tree->getNodeData($this->getId());
		$this->delete_rec($this->tree, $a_delete_meta_data);
		$this->tree->deleteTree($node_data);
	}

	/**
	* Delete sub tree
	*/
	private function delete_rec(&$a_tree, $a_delete_meta_data = true)
	{
		$childs = $a_tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			$obj =& ilLMObjectFactory::getInstance($this->content_object, $child["obj_id"], false);
			if (is_object($obj))
			{
				if($obj->getType() == "st")
				{
					$obj->delete_rec($a_tree, $a_delete_meta_data);
				}
				if($obj->getType() == "pg")
				{
					$obj->delete($a_delete_meta_data);
				}
			}
			unset($obj);
		}
		parent::delete($a_delete_meta_data);
	}

	/**
	* copy chapter
	*/
	function copy($a_target_lm)
	{
		$chap = new ilStructureObject($a_target_lm);
		$chap->setTitle($this->getTitle());
		if ($this->getLMId() != $a_target_lm->getId())
		{
			$chap->setImportId("il__st_".$this->getId());
		}
		$chap->setLMId($a_target_lm->getId());
		$chap->setType($this->getType());
		$chap->setDescription($this->getDescription());
		$chap->create(true);
		$a_copied_nodes[$this->getId()] = $chap->getId();
		
		// copy meta data
		include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
		$new_md =& $md->cloneMD($a_target_lm->getId(), $chap->getId(), $this->getType());

		// copy translations
		include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
		ilLMObjTranslation::copy($this->getId(), $chap->getId());


		return $chap;
	}

	/**
	* export object to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXML(&$a_xml_writer, $a_inst, &$expLog)
	{
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
		$md2xml = new ilMD2XML($this->getLMId(), $this->getId(), $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = "il_".IL_INST_ID."_st_".$this->getId();
		}

		return $a_value;
	}

	/**
	* get presentation title
	*
	*/
	static function _getPresentationTitle($a_st_id, $a_include_numbers = false,
		$a_time_scheduled_activation = false, $a_lm_id = 0, $a_lang = "-")
	{
		global $ilDB;

		if ($a_lm_id == 0)
		{
			$a_lm_id = ilLMObject::_lookupContObjID($a_st_id);
		}

		if ($a_lm_id == 0)
		{
			return "";
		}

		// this is optimized when ilLMObject::preloadDataByLM is invoked (e.g. done in ilLMExplorerGUI)
		$title = ilLMObject::_lookupTitle($a_st_id);

		// this is also optimized since ilObjectTranslation re-uses instances for one lm
		include_once("./Services/Object/classes/class.ilObjectTranslation.php");
		$ot = ilObjectTranslation::getInstance($a_lm_id);
		$languages = $ot->getLanguages();

		if ($a_lang != "-" && $ot->getContentActivated() && isset($languages[$a_lang]))
		{
			include_once("./Modules/LearningModule/classes/class.ilLMObjTranslation.php");
			$lmobjtrans = new ilLMObjTranslation($a_st_id, $a_lang);
			if ($lmobjtrans->getTitle() != "")
			{
				$title = $lmobjtrans->getTitle();
			}
		}

		include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
		$tree = ilLMTree::getInstance($a_lm_id);

		if ($a_include_numbers)
		{
			// this is optimized, since isInTree is cached
			if ($tree->isInTree($a_st_id))
			{
				// optimization needed from here

				// get chapter tree node
				$query = "SELECT * FROM lm_tree WHERE child = ".
					$ilDB->quote($a_st_id, "integer")." AND lm_id = ".
					$ilDB->quote($a_lm_id, "integer");
				$tree_set = $ilDB->query($query);
				$tree_node = $tree_set->fetchRow(DB_FETCHMODE_ASSOC);
				$depth = $tree_node["depth"];

				$nr = $tree->getChildSequenceNumber($tree_node, "st")." ";
				for ($i = $depth - 1; $i > 1; $i --)
				{
					// get next parent tree node
					$query = "SELECT * FROM lm_tree WHERE child = ".
						$ilDB->quote($tree_node["parent"], "integer")." AND lm_id = ".
						$ilDB->quote($a_lm_id, "integer");
					$tree_set = $ilDB->query($query);
					$tree_node = $tree_set->fetchRow(DB_FETCHMODE_ASSOC);
					$seq = $tree->getChildSequenceNumber($tree_node, "st");

					$nr = $seq.".".$nr;
				}
			}
		}

		return $nr.$title;
	}



	/**
	* export page objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst = 0)
	{
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
	}


	/**
	* export (sub)structure objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLStructureObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
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
	}

	/**
	* export object to fo
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
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

	/**
	* export page objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportFOPageObjects(&$a_xml_writer)
	{
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
	}

	/**
	 * export (sub)structure objects of structure object (see ilias_co.dtd)
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	static function getChapterList($a_lm_id)
	{
		$tree = new ilTree($a_lm_id);
		$tree->setTableNames('lm_tree', 'lm_data');
		$tree->setTreeTablePK("lm_id");

		$chapters = array();
		$ndata = $tree->getNodeData($tree->readRootId());
		$childs = $tree->getSubtree($ndata);
		foreach ($childs as $child)
		{
			if($child["type"] == "st")
			{
				$chapters[] = $child;
			}

		}
		return $chapters;
	}

}
?>
