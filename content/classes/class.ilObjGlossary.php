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

require_once("classes/class.ilObject.php");
require_once("classes/class.ilMetaData.php");
require_once("./content/classes/class.ilGlossaryTerm.php");

/**
* Class ilObjGlossary
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilObjGlossary extends ilObject
{

	/**
	* Constructor
	* @access	public
	*/
	function ilObjGlossary($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "glo";
		$this->ilObject($a_id,$a_call_by_reference);
		if ($a_id == 0)
		{
			$this->initMeta();
		}

	}

	/**
	* init meta data object if needed
	*/
	function initMeta()
	{
		if (!is_object($this->meta_data))
		{
			if ($this->getId())
			{
				$new_meta =& new ilMetaData($this->getType(), $this->getId());
			}	
			else
			{
				$new_meta =& new ilMetaData();
			}
			$this->assignMetaData($new_meta);
		}
	}

	/**
	* create glossary object
	*/
	function create($a_upload = false)
	{
		global $ilDB;

		parent::create();
		if (!$a_upload)
		{
			$this->initMeta();
			$this->meta_data->setId($this->getId());
			$this->meta_data->setType($this->getType());
			$this->meta_data->setTitle($this->getTitle());
			$this->meta_data->setDescription($this->getDescription());
			$this->meta_data->setObject($this);
			$this->meta_data->create();
		}

		$q = "INSERT INTO glossary (id, online) VALUES ".
			" (".$ilDB->quote($this->getId()).",".$ilDB->quote("n").")";
		$ilDB->query($q);

	}

	/**
	* read data of content object
	*/
	function read()
	{
		parent::read();
#		echo "Glossary<br>\n";

		$q = "SELECT * FROM glossary WHERE id = '".$this->getId()."'";
		$gl_set = $this->ilias->db->query($q);
		$gl_rec = $gl_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setOnline(ilUtil::yn2tf($gl_rec["online"]));

	}

	/**
	* get description of glossary object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return parent::getDescription();
	}

	/**
	* set description of glossary object
	*/
	function setDescription($a_description)
	{
		parent::setDescription($a_description);
		$this->meta_data->setDescription($a_description);
	}

	/**
	* get title of glossary object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return parent::getTitle();
	}

	/**
	* set title of glossary object
	*/
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
		$this->meta_data->setTitle($a_title);
	}

	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	function getOnline()
	{
		return $this->online;
	}

	/**
	* check wether content object is online
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM glossary WHERE id = '".$a_id."'";
		$lm_set = $ilDB->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);

		return ilUtil::yn2tf($lm_rec["online"]);
	}

	/**
	* assign a meta data object to glossary object
	*
	* @param	object		$a_meta_data	meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object of glossary object
	*
	* @return	object		meta data object
	*/
	function &getMetaData()
	{
		$this->initMeta();
		return $this->meta_data;
	}

	/**
	* update meta data only
	*/
	function updateMetaData()
	{
		$this->initMeta();
		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();
	}

	/**
	* update complete object
	*/
	function update()
	{
		$this->updateMetaData();

		$q = "UPDATE glossary SET ".
			" online = '".ilUtil::tf2yn($this->getOnline())."'".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($q);

	}

	function getImportId()
	{
		$this->initMeta();
		return $this->meta_data->getImportIdentifierEntryID();
	}

	function setImportId($a_id)
	{
		$this->initMeta();
		$this->meta_data->setImportIdentifierEntryID($a_id);
	}


	/**
	* get term list
	*/
	function getTermList($searchterm="")
	{
		$list = ilGlossaryTerm::getTermList($this->getId(),$searchterm);
		return $list;
	}

	/**
	* creates data directory for import files
	* (data_dir/glo_data/glo_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createImportDirectory()
	{
		$glo_data_dir = ilUtil::getDataDir()."/glo_data";
		ilUtil::makeDir($glo_data_dir);
		if(!is_writable($glo_data_dir))
		{
			$this->ilias->raiseError("Glossary Data Directory (".$glo_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create glossary directory (data_dir/glo_data/glo_<id>)
		$glo_dir = $glo_data_dir."/glo_".$this->getId();
		ilUtil::makeDir($glo_dir);
		if(!@is_dir($glo_dir))
		{
			$this->ilias->raiseError("Creation of Glossary Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Import subdirectory (data_dir/glo_data/glo_<id>/import)
		$import_dir = $glo_dir."/import";
		ilUtil::makeDir($import_dir);
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get import directory of glossary
	*/
	function getImportDirectory()
	{
		$export_dir = ilUtil::getDataDir()."/glo_data"."/glo_".$this->getId()."/import";

		return $export_dir;
	}

	/**
	* creates data directory for export files
	* (data_dir/glo_data/glo_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		$glo_data_dir = ilUtil::getDataDir()."/glo_data";
		ilUtil::makeDir($glo_data_dir);
		if(!is_writable($glo_data_dir))
		{
			$this->ilias->raiseError("Glossary Data Directory (".$glo_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		// create glossary directory (data_dir/glo_data/glo_<id>)
		$glo_dir = $glo_data_dir."/glo_".$this->getId();
		ilUtil::makeDir($glo_dir);
		if(!@is_dir($glo_dir))
		{
			$this->ilias->raiseError("Creation of Glossary Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/glo_data/glo_<id>/export)
		$export_dir = $glo_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get export directory of glossary
	*/
	function getExportDirectory()
	{
		$export_dir = ilUtil::getDataDir()."/glo_data"."/glo_".$this->getId()."/export";

		return $export_dir;
	}

	/**
	* get export files
	*/
	function getExportFiles($dir)
	{
		// quit if import dir not available
		if (!@is_dir($dir) or
			!is_writeable($dir))
		{
			return array();
		}

		// open directory
		$dir = dir($dir);

		// initialize array
		$file = array();

		// get files and save the in the array
		while ($entry = $dir->read())
		{
			if ($entry != "." and
				$entry != ".." and
				substr($entry, -4) == ".zip" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(glo_)*[0-9]+\.zip\$", $entry))
			{
				$file[] = $entry;
			}
		}

		// close import directory
		$dir->close();

		// sort files
		sort ($file);
		reset ($file);

		return $file;
	}

	/**
	* export object to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		global $ilBench;

		// export glossary
		$attrs = array();
		$attrs["Type"] = "Glossary";
		$a_xml_writer->xmlStartTag("ContentObject", $attrs);

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		// collect media objects
		$terms = $this->getTermList();
		$this->mob_ids = array();
		$this->file_ids = array();
		foreach ($terms as $term)
		{
			include_once "./content/classes/class.ilGlossaryDefinition.php";
			
			$defs = ilGlossaryDefinition::getDefinitionList($term[id]);

			foreach($defs as $def)
			{
				$this->page_object =& new ilPageObject("gdf",
					$def["id"], $this->halt_on_error);
				$this->page_object->buildDom();
				$this->page_object->insertInstIntoIDs(IL_INST_ID);
				$mob_ids = $this->page_object->collectMediaObjects(false);
				$file_ids = $this->page_object->collectFileItems();
				foreach($mob_ids as $mob_id)
				{
					$this->mob_ids[$mob_id] = $mob_id;
				}
				foreach($file_ids as $file_id)
				{
					$this->file_ids[$file_id] = $file_id;
				}
			}
		}

		// export media objects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Media Objects");
		$ilBench->start("GlossaryExport", "exportMediaObjects");
		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
		$ilBench->stop("GlossaryExport", "exportMediaObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Media Objects");

		// FileItems
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export File Items");
		$ilBench->start("ContentObjectExport", "exportFileItems");
		$this->exportFileItems($a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportFileItems");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export File Items");

		// Glossary
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Glossary Items");
		$ilBench->start("GlossaryExport", "exportGlossaryItems");
		$this->exportXMLGlossaryItems($a_xml_writer, $a_inst, $expLog);
		$ilBench->stop("GlossaryExport", "exportGlossaryItems");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Glossary Items");

		$a_xml_writer->xmlEndTag("ContentObject");
	}

	/**
	* export page objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLGlossaryItems(&$a_xml_writer, $a_inst, &$expLog)
	{
		global $ilBench;

		$attrs = array();
		$a_xml_writer->xmlStartTag("Glossary", $attrs);

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		$terms = $this->getTermList();

		// export glossary terms
		reset($terms);
		foreach ($terms as $term)
		{
			$ilBench->start("GlossaryExport", "exportGlossaryItem");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$page["obj_id"]);

			// export xml to writer object
			$ilBench->start("GlossaryExport", "exportGlossaryItem_getGlossaryTerm");
			$glo_term = new ilGlossaryTerm($term["id"]);
			$ilBench->stop("GlossaryExport", "exportGlossaryItem_getGlossaryTerm");
			$ilBench->start("GlossaryExport", "exportGlossaryItem_XML");
			$glo_term->exportXML($a_xml_writer, $a_inst);
			$ilBench->stop("GlossaryExport", "exportGlossaryItem_XML");

			// collect all file items
			/*
			$ilBench->start("GlossaryExport", "exportGlossaryItem_CollectFileItems");
			$file_ids = $page_obj->getFileItemIds();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("GlossaryExport", "exportGlossaryItem_CollectFileItems");
			*/

			unset($glo_term);

			$ilBench->stop("GlossaryExport", "exportGlossaryItem");
		}

		$a_xml_writer->xmlEndTag("Glossary");
	}

	/**
	* export content objects meta data to xml (see ilias_co.dtd)
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

	/**
	* export media objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		include_once("content/classes/Media/class.ilObjMediaObject.php");

		foreach ($this->mob_ids as $mob_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
			$media_obj = new ilObjMediaObject($mob_id);
			$media_obj->exportXML($a_xml_writer, $a_inst);
			$media_obj->exportFiles($a_target_dir);
			unset($media_obj);
		}
	}

	/**
	* export files of file itmes
	*
	*/
	function exportFileItems($a_target_dir, &$expLog)
	{
		include_once("classes/class.ilObjFile.php");

		foreach ($this->file_ids as $file_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
			$file_obj = new ilObjFile($file_id, false);
			$file_obj->export($a_target_dir);
			unset($file_obj);
		}
	}



	/**
	*
	*/
	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = "il_".IL_INST_ID."_glo_".$this->getId();
		}

		return $a_value;
	}




	/**
	* copy all properties and subobjects of a glossary
	*
	* @access	public
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);

		// todo: put here glossary specific stuff

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete glossary and all related data
	*
	* this method has been tested on may 9th 2004
	* meta data, terms, definitions, definition meta data
	* and definition pages have been deleted correctly as desired
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete terms
		$terms = $this->getTermList();
		foreach ($terms as $term)
		{
			$term_obj =& new ilGlossaryTerm($term["id"]);
			$term_obj->delete();
		}

		// delete meta data
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();

		return true;
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional paramters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Glossary ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				
				//echo "Glossary ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Glossary ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				
				//echo "Glossary ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "Glossary ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}


	function getXMLZip()
	{
		include_once("content/classes/class.ilGlossaryExport.php");

		$glo_exp = new ilGlossaryExport($this);

		return $glo_exp->buildExportFile();
	}


} // END class.ilObjGlossary

?>
