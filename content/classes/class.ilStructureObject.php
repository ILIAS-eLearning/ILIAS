<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("content/classes/class.ilLMObject.php");

/**
* Class ilStructreObject
*
* Handles StructureObjects of ILIAS Learning Modules (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
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
	*
	*/
	function delete()
	{
		$this->tree = new ilTree($this->getLmId());
		$this->tree->setTableNames('lm_tree', 'lm_data');
		$this->tree->setTreeTablePK("lm_id");
		$node_data = $this->tree->getNodeData($this->getId());
		$this->delete_rec($this->tree);
		$this->tree->deleteTree($node_data);
	}

	/**
	* private
	*/
	function delete_rec(&$a_tree)
	{
		$childs = $a_tree->getChilds($this->getId());
		foreach ($childs as $child)
		{
			$obj =& ilLMObjectFactory::getInstance($this->content_object, $child["obj_id"]);
			if (is_object($obj))
			{
				if($obj->getType() == "st")
				{
					$obj->delete_rec($a_tree);
				}
				if($obj->getType() == "pg")
				{
					$obj->delete();
				}
			}
			unset($obj);
		}
		parent::delete();
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
		$nested = new ilNestedSetXML();
		$nested->setParameterModifier($this, "modifyExportIdentifier");
		$a_xml_writer->appendXML($nested->export($this->getId(),
			$this->getType()));
	}

	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = "il_".IL_INST_ID."_st_".$this->getId();
			//$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}



	/**
	* export page objects of structure object (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst = 0)
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


}
?>
