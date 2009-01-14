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
require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");

/** @defgroup ModulesGlossary Modules/Glossary
 */

/**
* Class ilObjGlossary
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesGlossary
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
	}

	/**
	* create glossary object
	*/
	function create($a_upload = false)
	{
		global $ilDB;

		parent::create();
		
		// meta data will be created by
		// import parser
		if (!$a_upload)
		{
			$this->createMetaData();
		}
		
		$q = "INSERT INTO glossary (id, online, virtual) VALUES ".
			" (".$ilDB->quote($this->getId()).",".$ilDB->quote("n").",".$ilDB->quote($this->getVirtualMode()).")";
		$ilDB->query($q);

	}

	/**
	* read data of content object
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();
#		echo "Glossary<br>\n";

		$q = "SELECT * FROM glossary WHERE id = ".$ilDB->quote($this->getId());
		$gl_set = $this->ilias->db->query($q);
		$gl_rec = $gl_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setOnline(ilUtil::yn2tf($gl_rec["online"]));
		$this->setVirtualMode($gl_rec["virtual"]);
		$this->setPublicExportFile("xml", $gl_rec["public_xml_file"]);
		$this->setPublicExportFile("html", $gl_rec["public_html_file"]);
		$this->setActiveGlossaryMenu(ilUtil::yn2tf($gl_rec["glo_menu_active"]));
		$this->setActiveDownloads(ilUtil::yn2tf($gl_rec["downloads_active"]));
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
	}

	
	/**
	* set glossary type (virtual: fixed/level/subtree, normal:none)
	*/
	function setVirtualMode($a_mode)
	{
		switch ($a_mode)
		{
			case "level":
			case "subtree":
			// case "fixed":
				$this->virtual_mode = $a_mode;
				$this->virtual = true;
				break;
				
			default:
				$this->virtual_mode = "none";
				$this->virtual = false;
				break;
		}
	}
	
	/**
	* get glossary type (normal or virtual)
	*/
	function getVirtualMode()
	{
		return $this->virtual_mode;
	}
	
	/**
	 * returns true if glossary type is virtual (any mode)
	 */
	function isVirtual()
	{
		return $this->virtual;
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
//		$this->meta_data->setTitle($a_title);
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

		$q = "SELECT * FROM glossary WHERE id = ".$ilDB->quote($a_id);
		$lm_set = $ilDB->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);

		return ilUtil::yn2tf($lm_rec["online"]);
	}
	
	function setActiveGlossaryMenu($a_act_glo_menu)
	{
		$this->glo_menu_active = $a_act_glo_menu;
	}

	function isActiveGlossaryMenu()
	{
		return $this->glo_menu_active;
	}

	function setActiveDownloads($a_down)
	{
		$this->downloads_active = $a_down;
	}

	function isActiveDownloads()
	{
		return $this->downloads_active;
	}

	/**
	* assign a meta data object to glossary object
	*
	* @param	object		$a_meta_data	meta data object
	*/
/*
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}
*/

	/**
	* get meta data object of glossary object
	*
	* @return	object		meta data object
	*/
/*
	function &getMetaData()
	{
		$this->initMeta();
		return $this->meta_data;
	}
*/

	/**
	* update object
	*/
	function update()
	{
		global $ilDB;
		
		$this->updateMetaData();

		$q = "UPDATE glossary SET ".
			" online = ".$ilDB->quote(ilUtil::tf2yn($this->getOnline())).",".
			" virtual = ".$ilDB->quote($this->getVirtualMode()).",".
			" public_xml_file = ".$ilDB->quote($this->getPublicExportFile("xml")).",".
			" public_html_file = ".$ilDB->quote($this->getPublicExportFile("html")).",".
			" glo_menu_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveGlossaryMenu())).",".
			" downloads_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveDownloads()))." ".
			" WHERE id = ".$ilDB->quote($this->getId());
			
		$ilDB->query($q);
		
		parent::update();
	}


	/**
	* get term list
	*/
	function getTermList($searchterm="")
	{
		if ($this->isVirtual())
		{
			global $tree;
			
			$glo_ids = array();
			
			switch ($this->getVirtualMode())
			{
				case "level":
					$glo_arr = $tree->getChildsByType($tree->getParentId($this->getRefId()),"glo");
					
					foreach ($glo_arr as $glo)
					{
						{
							$glo_ids[] = $glo['obj_id'];
						}
					}
					break;

				case "subtree":
					$subtree_nodes = $tree->getSubTree($tree->getNodeData($tree->getParentId($this->getRefId())));

					foreach ($subtree_nodes as $node)
					{
						if ($node['type'] == 'glo')
						{
							$glo_ids[] = $node['obj_id'];
						}
					}
					break;
				
/* for futere enhancements
				case "fixed":
					
					break;
*/
				// fallback to none virtual mode in case of error
				default:
					$glo_ids[] = $this->getId();
					break;
			}
		}
		else
		{
			$glo_ids = $this->getId();
		}
		
		$list = ilGlossaryTerm::getTermList($glo_ids,$searchterm);
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
	* Creates export directory
	*/
	function createExportDirectory($a_type = "xml")
	{
		include_once("./Services/Export/classes/class.ilExport.php");
		return ilExport::_createExportDirectory($this->getId(), $a_type, $this->getType());
	}

	/**
	* Get export directory of glossary
	*/
	function getExportDirectory($a_type = "xml")
	{
		include_once("./Services/Export/classes/class.ilExport.php");
		return ilExport::_getExportDirectory($this->getId(), $a_type, $this->getType());
	}

	/**
	* Get export files
	*/
	function getExportFiles()
	{
		include_once("./Services/Export/classes/class.ilExport.php");
		return ilExport::_getExportFiles($this->getId(), array("xml", "html"), $this->getType());
	}
	
	/**
	* specify public export file for type
	*
	* @param	string		$a_type		type ("xml" / "html")
	* @param	string		$a_file		file name
	*/
	function setPublicExportFile($a_type, $a_file)
	{
		$this->public_export_file[$a_type] = $a_file;
	}

	/**
	* get public export file
	*
	* @param	string		$a_type		type ("xml" / "html")
	*
	* @return	string		$a_file		file name	
	*/
	function getPublicExportFile($a_type)
	{
		return $this->public_export_file[$a_type];
	}

	/**
	* export html package
	*/
	function exportHTML($a_target_dir, $log)
	{
		global $ilias, $tpl;

		// initialize temporary target directory
		ilUtil::delDir($a_target_dir);
		ilUtil::makeDir($a_target_dir);
		$mob_dir = $a_target_dir."/mobs";
		ilUtil::makeDir($mob_dir);
		$file_dir = $a_target_dir."/files";
		ilUtil::makeDir($file_dir);
		$tex_dir = $a_target_dir."/teximg";
		ilUtil::makeDir($tex_dir);

		// export system style sheet
		$location_stylesheet = ilUtil::getStyleSheetLocation("filesystem");
		$style_name = $ilias->account->prefs["style"].".css";
		copy($location_stylesheet, $a_target_dir."/".$style_name);
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		
		$cont_stylesheet = "Services/COPage/css/content.css";
		copy($cont_stylesheet, $a_target_dir."/content.css");
		
		// export syntax highlighting style
		$syn_stylesheet = ilObjStyleSheet::getSyntaxStylePath();
		copy($syn_stylesheet, $a_target_dir."/syntaxhighlight.css");

		// get glossary presentation gui class
		include_once("./Modules/Glossary/classes/class.ilGlossaryPresentationGUI.php");
		$_GET["cmd"] = "nop";
		$glo_gui =& new ilGlossaryPresentationGUI();
		$glo_gui->setOfflineMode(true);
		$glo_gui->setOfflineDirectory($a_target_dir);
		
		// could be implemented in the future if other export
		// formats are supported (e.g. scorm)
		//$glo_gui->setExportFormat($a_export_format);

		// export terms
		$this->exportHTMLGlossaryTerms($glo_gui, $a_target_dir);
				
		// export all media objects
		foreach ($this->offline_mobs as $mob)
		{
			$this->exportHTMLMOB($a_target_dir, $glo_gui, $mob, "_blank");
		}
		$_GET["obj_type"]  = "MediaObject";
		$_GET["obj_id"]  = $a_mob_id;
		$_GET["cmd"] = "";
		
		// export all file objects
		foreach ($this->offline_files as $file)
		{
			$this->exportHTMLFile($a_target_dir, $file);
		}
		
		// export images
		$image_dir = $a_target_dir."/images";
		ilUtil::makeDir($image_dir);
		ilUtil::makeDir($image_dir."/browser");
		copy(ilUtil::getImagePath("enlarge.gif", false, "filesystem"),
			$image_dir."/enlarge.gif");
		copy(ilUtil::getImagePath("browser/blank.gif", false, "filesystem"),
			$image_dir."/browser/plus.gif");
		copy(ilUtil::getImagePath("browser/blank.gif", false, "filesystem"),
			$image_dir."/browser/minus.gif");
		copy(ilUtil::getImagePath("browser/blank.gif", false, "filesystem"),
			$image_dir."/browser/blank.gif");
		copy(ilUtil::getImagePath("icon_st.gif", false, "filesystem"),
			$image_dir."/icon_st.gif");
		copy(ilUtil::getImagePath("icon_pg.gif", false, "filesystem"),
			$image_dir."/icon_pg.gif");
		copy(ilUtil::getImagePath("nav_arr_L.gif", false, "filesystem"),
			$image_dir."/nav_arr_L.gif");
		copy(ilUtil::getImagePath("nav_arr_R.gif", false, "filesystem"),
			$image_dir."/nav_arr_R.gif");
			
		// template workaround: reset of template 
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		
		// zip everything
		if (true)
		{
			// zip it all
			$date = time();
			$zip_file = $this->getExportDirectory("html")."/".$date."__".IL_INST_ID."__".
				$this->getType()."_".$this->getId().".zip";
//echo "zip-".$a_target_dir."-to-".$zip_file;
			ilUtil::zip($a_target_dir, $zip_file);
			ilUtil::delDir($a_target_dir);
		}
	}
	

	/**
	* export glossary terms
	*/
	function exportHTMLGlossaryTerms(&$a_glo_gui, $a_target_dir)
	{
		global $ilUser;
		
		// index.html file
		$a_glo_gui->tpl = new ilTemplate("tpl.main.html", true, true);
		$style_name = $ilUser->prefs["style"].".css";;
		$a_glo_gui->tpl->setVariable("LOCATION_STYLESHEET","./".$style_name);
		$a_glo_gui->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$a_glo_gui->tpl->setVariable("HEADER", $this->getTitle());

		$content = $a_glo_gui->listTerms();
		$file = $a_target_dir."/index.html";
						
		// open file
		if (!($fp = @fopen($file,"w+")))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
				" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}
		chmod($file, 0770);
		fwrite($fp, $content);
		fclose($fp);
		
		$terms = $this->getTermList();
		
		$this->offline_mobs = array();
		$this->offline_files = array();
		
		foreach($terms as $term)
		{
			$a_glo_gui->tpl = new ilTemplate("tpl.main.html", true, true);
			//$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			
			// set style
			$style_name = $ilUser->prefs["style"].".css";;
			$a_glo_gui->tpl->setVariable("LOCATION_STYLESHEET","./".$style_name);

			$_GET["term_id"] = $term["id"];
			$_GET["frame"] = "_blank";
			$content =& $a_glo_gui->listDefinitions();
			$file = $a_target_dir."/term_".$term["id"].".html";
							
			// open file
			if (!($fp = @fopen($file,"w+")))
			{
				die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
			}
			chmod($file, 0770);
			fwrite($fp, $content);
			fclose($fp);

			// store linked/embedded media objects of glosssary term
			include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
			$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
			foreach($defs as $def)
			{
				$def_mobs = ilObjMediaObject::_getMobsOfObject("gdf:pg", $def["id"]);
				foreach($def_mobs as $def_mob)
				{
					$this->offline_mobs[$def_mob] = $def_mob;
				}
				
				// get all files of page
				include_once("./Modules/File/classes/class.ilObjFile.php");
				$def_files = ilObjFile::_getFilesOfObject("gdf:pg", $def["id"]);
				$this->offline_files = array_merge($this->offline_files, $def_files);

			}
		}
	}
	
	/**
	* export media object to html
	*/
	function exportHTMLMOB($a_target_dir, &$a_glo_gui, $a_mob_id)
	{
		global $tpl;

		$mob_dir = $a_target_dir."/mobs";

		$source_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob_id;
		if (@is_dir($source_dir))
		{
			ilUtil::makeDir($mob_dir."/mm_".$a_mob_id);
			ilUtil::rCopy($source_dir, $mob_dir."/mm_".$a_mob_id);
		}
		
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$_GET["obj_type"]  = "MediaObject";
		$_GET["mob_id"]  = $a_mob_id;
		$_GET["cmd"] = "";
		$content =& $a_glo_gui->media();
		$file = $a_target_dir."/media_".$a_mob_id.".html";

		// open file
		if (!($fp = @fopen($file,"w+")))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
				" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}
		chmod($file, 0770);
		fwrite($fp, $content);
		fclose($fp);
		
		// fullscreen
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob_obj = new ilObjMediaObject($a_mob_id);
		if ($mob_obj->hasFullscreenItem())
		{
			$tpl = new ilTemplate("tpl.main.html", true, true);
			$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$_GET["mob_id"]  = $a_mob_id;
			$_GET["cmd"] = "fullscreen";
			$content = $a_glo_gui->fullscreen();
			$file = $a_target_dir."/fullscreen_".$a_mob_id.".html";
	
			// open file
			if (!($fp = @fopen($file,"w+")))
			{
				die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
			}
			chmod($file, 0770);
			fwrite($fp, $content);
			fclose($fp);
		}
	}

	/**
	* export file object
	*/
	function exportHTMLFile($a_target_dir, $a_file_id)
	{
		$file_dir = $a_target_dir."/files/file_".$a_file_id;
		ilUtil::makeDir($file_dir);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$file_obj = new ilObjFile($a_file_id, false);
		$source_file = $file_obj->getDirectory($file_obj->getVersion())."/".$file_obj->getFileName();
		if (!is_file($source_file))
		{
			$source_file = $file_obj->getDirectory()."/".$file_obj->getFileName();
		}
		copy($source_file, $file_dir."/".$file_obj->getFileName());
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
			include_once "./Modules/Glossary/classes/class.ilGlossaryDefinition.php";
			
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
		include_once("Services/MetaData/classes/class.ilMD2XML.php");
		$md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

	/**
	* export media objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

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
		include_once("./Modules/File/classes/class.ilObjFile.php");

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
		global $ilDB;
		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete terms
		if (!$this->isVirtual())
		{
			$terms = $this->getTermList();
			foreach ($terms as $term)
			{
				$term_obj =& new ilGlossaryTerm($term["id"]);
				$term_obj->delete();
			}
		}
		
		// delete glossary data entry
		$q = "DELETE FROM glossary WHERE id = ".$ilDB->quote($this->getId());
		$ilDB->query($q);

		// delete meta data
		$this->deleteMetaData();
/*
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();
*/

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


	/**
	* Get zipped xml file for glossary.
	*/
	function getXMLZip()
	{
		include_once("./Modules/Glossary/classes/class.ilGlossaryExport.php");
		$glo_exp = new ilGlossaryExport($this);
		return $glo_exp->buildExportFile();
	}


} // END class.ilObjGlossary

?>
