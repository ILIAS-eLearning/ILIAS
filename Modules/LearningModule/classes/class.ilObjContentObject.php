<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


require_once "classes/class.ilObject.php";
require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";
require_once("classes/class.ilNestedSetXML.php");

/** @defgroup ModulesIliasLearningModule Modules/IliasLearningModule
 */

/**
* Class ilObjContentObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilObjContentObject extends ilObject
{
	var $lm_tree;
	var $meta_data;
	var $layout;
	var $style_id;
	var $pg_header;
	var $online;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjContentObject($a_id = 0,$a_call_by_reference = true)
	{
		// this also calls read() method! (if $a_id is set)
		$this->ilObject($a_id,$a_call_by_reference);

		$this->mob_ids = array();
		$this->file_ids = array();
	}

	/**
	* create content object
	*/
	function create($a_upload = false)
	{
		global $ilUser;

		parent::create();
		
		// meta data will be created by
		// import parser
		if (!$a_upload)
		{
			$this->createMetaData();
		}

		$this->createProperties();
		if (!$a_upload)
		{
			if (is_object($ilUser))
			{
				//$this->meta_data->setLanguage($ilUser->getLanguage());
			}
		}
	}

	/**
	* init default roles settings
	* OBSOLETE. DON'T USE, READ TEXT BELOW
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		return array();

		global $rbacadmin, $rbacreview;

		// create a local role folder
		$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of content object ".$this->getId());

		// note: we don't need any roles here, local "author" roles must
		// be created manually. subscription roles have been abandoned.
		/*
		// create author role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("author object ".$this->getRefId(),"author of content object ref id ".$this->getRefId());
		$roles[] = $roleObj->getId();

		// copy permissions from author template to new role
		$rbacadmin->copyRolePermissions($this->getAuthorRoleTemplateId(), 8, $rfoldObj->getRefId(), $roleObj->getId());

		// grant all allowed operations of role to current learning module
		$rbacadmin->grantPermission($roleObj->getId(),
			$rbacreview->getOperationsOfRole($roleObj->getId(), "lm", $rfoldObj->getRefId()),
			$this->getRefId());*/

		unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
	}


	/**
	* read data of content object
	*/
	function read()
	{
		parent::read();
#		echo "Content<br>\n";

		$this->lm_tree = new ilTree($this->getId());
		$this->lm_tree->setTableNames('lm_tree','lm_data');
		$this->lm_tree->setTreeTablePK("lm_id");

		$this->readProperties();
		//parent::read();
	}

	/**
	* get title of content object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return parent::getTitle();
	}

	/**
	* set title of content object
	*/
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
//		$this->meta_data->setTitle($a_title);
	}

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
		return parent::getDescription();
	}

	/**
	* set description of content object
	*/
	function setDescription($a_description)
	{
		parent::setDescription($a_description);
//		$this->meta_data->setDescription($a_description);
	}


	function getImportId()
	{
		return $this->import_id;
	}

	function setImportId($a_id)
	{
		$this->import_id = $a_id;
	}

	function &getTree()
	{
		return $this->lm_tree;
	}

	/**
	* update complete object (meta data and properties)
	*/
	function update()
	{
		$this->updateMetaData();
		parent::update();
		$this->updateProperties();
	}


	/**
	* if implemented, this function should be called from an Out/GUI-Object
	*/
	function import()
	{
		// nothing to do. just display the dialogue in Out
		return;
	}


	/**
	* put content object in main tree
	*
	*/
	function putInTree($a_parent)
	{
		global $tree;

		// put this object in tree under $a_parent
		parent::putInTree($a_parent);

		// make new tree for this object
		//$tree->addTree($this->getId());
	}


	/**
	* create content object tree (that stores structure object hierarchie)
	*
	* todo: rename LM to ConOb
	*/
	function createLMTree()
	{
		$this->lm_tree =& new ilTree($this->getId());
		$this->lm_tree->setTreeTablePK("lm_id");
		$this->lm_tree->setTableNames('lm_tree','lm_data');
		$this->lm_tree->addTree($this->getId(), 1);
	}


	/**
	* get content object tree
	*/
	function &getLMTree()
	{
		return $this->lm_tree;
	}


	/**
	* creates data directory for import files
	* (data_dir/lm_data/lm_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createImportDirectory()
	{
		$lm_data_dir = ilUtil::getDataDir()."/lm_data";
		if(!is_writable($lm_data_dir))
		{
			$this->ilias->raiseError("Content object Data Directory (".$lm_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create learning module directory (data_dir/lm_data/lm_<id>)
		$lm_dir = $lm_data_dir."/lm_".$this->getId();
		ilUtil::makeDir($lm_dir);
		if(!@is_dir($lm_dir))
		{
			$this->ilias->raiseError("Creation of Learning Module Directory failed.",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory (data_dir/lm_data/lm_<id>/import)
		$import_dir = $lm_dir."/import";
		ilUtil::makeDir($import_dir);
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get data directory
	*/
	function getDataDirectory()
	{
		return ilUtil::getDataDir()."/lm_data".
			"/lm_".$this->getId();
	}

	/**
	* get import directory of lm
	*/
	function getImportDirectory()
	{
		$import_dir = ilUtil::getDataDir()."/lm_data".
			"/lm_".$this->getId()."/import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}


	/**
	* creates data directory for export files
	* (data_dir/lm_data/lm_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory($a_type = "xml")
	{
		$lm_data_dir = ilUtil::getDataDir()."/lm_data";
		if(!is_writable($lm_data_dir))
		{
			$this->ilias->raiseError("Content object Data Directory (".$lm_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$lm_dir = $lm_data_dir."/lm_".$this->getId();
		ilUtil::makeDir($lm_dir);
		if(!@is_dir($lm_dir))
		{
			$this->ilias->raiseError("Creation of Learning Module Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		switch ($a_type)
		{
			// html
			case "html":
				$export_dir = $lm_dir."/export_html";
				break;

			// scorm
			case "scorm":
				$export_dir = $lm_dir."/export_scorm";
				break;

			default:		// = xml
				$export_dir = $lm_dir."/export";
				break;
		}
		ilUtil::makeDir($export_dir);

		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get export directory of lm
	*/
	function getExportDirectory($a_type = "xml")
	{
		switch  ($a_type)
		{
			case "html":
				$export_dir = ilUtil::getDataDir()."/lm_data"."/lm_".$this->getId()."/export_html";
				break;

			case "scorm":
				$export_dir = ilUtil::getDataDir()."/lm_data"."/lm_".$this->getId()."/export_scorm";
				break;
				
			default:			// = xml
				$export_dir = ilUtil::getDataDir()."/lm_data"."/lm_".$this->getId()."/export";
				break;
		}
		return $export_dir;
	}


	/**
	* delete learning module and all related data
	*
	* this method has been tested on may 9th 2004
	* meta data, content object data, data directory, bib items
	* learning module tree and pages have been deleted correctly as desired
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;

		global $ilBench;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete lm object data
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		ilLMObject::_deleteAllObjectData($this);

		// delete meta data of content object
		$this->deleteMetaData();

		// delete bibitem data
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), "bib");
		$nested->deleteAllDBData();


		// delete learning module tree
		$this->lm_tree->removeTree($this->lm_tree->getTreeId());

		// delete data directory
		ilUtil::delDir($this->getDataDirectory());

		// delete content object record
		$q = "DELETE FROM content_object WHERE id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);

		// delete lm menu entries
		$q = "DELETE FROM lm_menu WHERE lm_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);

		return true;
	}


	/**
	* get default page layout of content object (see directory layouts/)
	*
	* @return	string		default layout
	*/
	function getLayout()
	{
		return $this->layout;
	}

	/**
	* set default page layout
	*
	* @param	string		$a_layout		default page layout
	*/
	function setLayout($a_layout)
	{
		$this->layout = $a_layout;
	}

	/**
	* get ID of assigned style sheet object
	*/
	function getStyleSheetId()
	{
		return $this->style_id;
	}

	/**
	* set ID of assigned style sheet object
	*/
	function setStyleSheetId($a_style_id)
	{
		$this->style_id = $a_style_id;
	}

	/**
	* write ID of assigned style sheet object to db
	*/
	function writeStyleSheetId($a_style_id)
	{
		global $ilDB;

		$q = "UPDATE content_object SET ".
			" stylesheet = ".$ilDB->quote($a_style_id).
			" WHERE id = ".$ilDB->quote($this->getId());
		$ilDB->query($q);

		$this->style_id = $a_style_id;
	}
	
	/**
	* move learning modules from one style to another
	*/
	function _moveLMStyles($a_from_style, $a_to_style)
	{
		global $ilDB, $ilias;

		if ($a_from_style < 0)	// change / delete all individual styles
		{
			$q = "SELECT stylesheet FROM content_object, style_data ".
				" WHERE content_object.stylesheet = style_data.id ".
				" AND style_data.standard = 0 ".
				" AND content_object.stylesheet > 0";
			$style_set = $ilDB->query($q);
			while($style_rec = $style_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				// assign learning modules to new style
				$q = "UPDATE content_object SET ".
					" stylesheet = ".$ilDB->quote($a_to_style).
					" WHERE stylesheet = ".$ilDB->quote($style_rec["stylesheet"]);
				$ilDB->query($q);
				
				// delete style
				$style_obj =& $ilias->obj_factory->getInstanceByObjId($style_rec["stylesheet"]);
				$style_obj->delete();
			}
		}
		else
		{
			$q = "UPDATE content_object SET ".
				" stylesheet = ".$ilDB->quote($a_to_style).
				" WHERE stylesheet = ".$ilDB->quote($a_from_style);
			$ilDB->query($q);
		}
	}

	/**
	* lookup style sheet ID
	*/
	function _lookupStyleSheetId($a_cont_obj_id)
	{
		global $ilDB;

		$q = "SELECT stylesheet FROM content_object ".
			" WHERE id = ".$ilDB->quote($a_cont_obj_id);
		$res = $ilDB->query($q);
		$sheet = $res->fetchRow(DB_FETCHMODE_ASSOC);

		return $sheet["stylesheet"];
	}
	
	/**
	* lookup style sheet ID
	*/
	function _lookupContObjIdByStyleId($a_style_id)
	{
		global $ilDB;

		$q = "SELECT id FROM content_object ".
			" WHERE stylesheet = ".$ilDB->quote($a_style_id);
		$res = $ilDB->query($q);
		$obj_ids = array();
		while($cont = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$obj_ids[] = $cont["id"];
		}
		return $obj_ids;
	}
	
	/**
	* gets the number of learning modules assigned to a content style
	*
	* @param	int		$a_style_id		style id
	*/
	function _getNrOfAssignedLMs($a_style_id)
	{
		global $ilDB;
		
		$q = "SELECT count(*) as cnt FROM content_object ".
			" WHERE stylesheet = ".$ilDB->quote($a_style_id);
		$cset = $ilDB->query($q);
		$crow = $cset->fetchRow(DB_FETCHMODE_ASSOC);

		return (int) $crow["cnt"];
	}
	
	
	/**
	* get number of learning modules with individual styles
	*/
	function _getNrLMsIndividualStyles()
	{
		global $ilDB;
		
		// joining with style table (not perfectly nice)
		$q = "SELECT count(*) as cnt FROM content_object, style_data ".
			" WHERE stylesheet = style_data.id ".
			" AND standard = 0";
		$cset = $ilDB->query($q);
		$crow = $cset->fetchRow(DB_FETCHMODE_ASSOC);

		return (int) $crow["cnt"];
	}

	/**
	* get number of learning modules assigned no style
	*/
	function _getNrLMsNoStyle()
	{
		global $ilDB;
		
		$q = "SELECT count(*) as cnt FROM content_object ".
			" WHERE stylesheet = 0";
		$cset = $ilDB->query($q);
		$crow = $cset->fetchRow(DB_FETCHMODE_ASSOC);

		return (int) $crow["cnt"];
	}

	/**
	* delete all style references to style
	*
	* @param	int		$a_style_id		style_id
	*/
	function _deleteStyleAssignments($a_style_id)
	{
		global $ilDB;
		
		$q = "UPDATE content_object SET ".
			" stylesheet = ".$ilDB->quote("0").
			" WHERE stylesheet = ".$ilDB->quote($this->getId($a_style_id));

		$ilDB->query($q);
	}

	/**
	* get page header mode (IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER)
	*/
	function getPageHeader()
	{
		return $this->pg_header;
	}

	/**
	* set page header mode
	*
	* @param string $a_pg_header		IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER
	*/
	function setPageHeader($a_pg_header = IL_CHAPTER_TITLE)
	{
		$this->pg_header = $a_pg_header;
	}

	/**
	* get toc mode ("chapters" | "pages")
	*/
	function getTOCMode()
	{
		return $this->toc_mode;
	}
	
	/**
	* get public access mode ("complete" | "selected")
	*/
	function getPublicAccessMode()
	{
		return $this->public_access_mode;
	}

	/**
	* set toc mode
	*
	* @param string $a_toc_mode		"chapters" | "pages"
	*/
	function setTOCMode($a_toc_mode = "chapters")
	{
		$this->toc_mode = $a_toc_mode;
	}

	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	function getOnline()
	{
		return $this->online;
	}

	function setActiveLMMenu($a_act_lm_menu)
	{
		$this->lm_menu_active = $a_act_lm_menu;
	}

	function isActiveLMMenu()
	{
		return $this->lm_menu_active;
	}

	function setActiveTOC($a_toc)
	{
		$this->toc_active = $a_toc;
	}

	function isActiveTOC()
	{
		return $this->toc_active;
	}

	function setActiveNumbering($a_num)
	{
		$this->numbering = $a_num;
	}

	function isActiveNumbering()
	{
		return $this->numbering;
	}

	function setActivePrintView($a_print)
	{
		$this->print_view_active = $a_print;
	}

	function isActivePrintView()
	{
		return $this->print_view_active;
	}

	function setActiveDownloads($a_down)
	{
		$this->downloads_active = $a_down;
	}
	
	function isActiveDownloads()
	{
		return $this->downloads_active;
	}
	
	function setActiveDownloadsPublic($a_down)
	{
		$this->downloads_public_active = $a_down;
	}
	
	function isActiveDownloadsPublic()
	{
		return $this->downloads_public_active;
	}

	function setPublicNotes($a_pub_notes)
	{
		$this->pub_notes = $a_pub_notes;
	}

	function publicNotes()
	{
		return $this->pub_notes;
	}
	
	function setCleanFrames($a_clean)
	{
		$this->clean_frames = $a_clean;
	}

	function cleanFrames()
	{
		return $this->clean_frames;
	}
	
	function setHistoryUserComments($a_comm)
	{
		$this->user_comments = $a_comm;
	}

	function setPublicAccessMode($a_mode)
	{
		$this->public_access_mode = $a_mode;
	}

	function isActiveHistoryUserComments()
	{
		return $this->user_comments;
	}

	function setHeaderPage($a_pg)
	{
		$this->header_page = $a_pg;
	}
	
	function getHeaderPage()
	{
		return $this->header_page;
	}
	
	function setFooterPage($a_pg)
	{
		$this->footer_page = $a_pg;
	}
	
	function getFooterPage()
	{
		return $this->footer_page;
	}

	/**
	* read content object properties
	*/
	function readProperties()
	{
		global $ilDB;
		
		$q = "SELECT * FROM content_object WHERE id = ".$ilDB->quote($this->getId());
		$lm_set = $this->ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setLayout($lm_rec["default_layout"]);
		$this->setStyleSheetId($lm_rec["stylesheet"]);
		$this->setPageHeader($lm_rec["page_header"]);
		$this->setTOCMode($lm_rec["toc_mode"]);
		$this->setOnline(ilUtil::yn2tf($lm_rec["online"]));
		$this->setActiveTOC(ilUtil::yn2tf($lm_rec["toc_active"]));
		$this->setActiveNumbering(ilUtil::yn2tf($lm_rec["numbering"]));
		$this->setActivePrintView(ilUtil::yn2tf($lm_rec["print_view_active"]));
		$this->setActiveDownloads(ilUtil::yn2tf($lm_rec["downloads_active"]));
		$this->setActiveDownloadsPublic(ilUtil::yn2tf($lm_rec["downloads_public_active"]));
		$this->setActiveLMMenu(ilUtil::yn2tf($lm_rec["lm_menu_active"]));
		$this->setCleanFrames(ilUtil::yn2tf($lm_rec["clean_frames"]));
		$this->setPublicNotes(ilUtil::yn2tf($lm_rec["pub_notes"]));
		$this->setHeaderPage($lm_rec["header_page"]);
		$this->setFooterPage($lm_rec["footer_page"]);
		$this->setHistoryUserComments(ilUtil::yn2tf($lm_rec["hist_user_comments"]));
		$this->setPublicAccessMode($lm_rec["public_access_mode"]);
		$this->setPublicExportFile("xml", $lm_rec["public_xml_file"]);
		$this->setPublicExportFile("html", $lm_rec["public_html_file"]);
	}

	/**
	* update content object properties
	*/
	function updateProperties()
	{
		global $ilDB;
		
		$q = "UPDATE content_object SET ".
			" default_layout = ".$ilDB->quote($this->getLayout()).", ".
			" stylesheet = ".$ilDB->quote($this->getStyleSheetId()).",".
			" page_header = ".$ilDB->quote($this->getPageHeader()).",".
			" toc_mode = ".$ilDB->quote($this->getTOCMode()).",".
			" online = ".$ilDB->quote(ilUtil::tf2yn($this->getOnline())).",".
			" toc_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveTOC())).",".
			" numbering = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveNumbering())).",".
			" print_view_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActivePrintView())).",".
			" downloads_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveDownloads())).",".
			" downloads_public_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveDownloadsPublic())).",".
			" clean_frames = ".$ilDB->quote(ilUtil::tf2yn($this->cleanFrames())).",".
			" pub_notes = ".$ilDB->quote(ilUtil::tf2yn($this->publicNotes())).",".
			" hist_user_comments = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveHistoryUserComments())).",".
			" public_access_mode = ".$ilDB->quote($this->getPublicAccessMode()).",".
			" public_xml_file = ".$ilDB->quote($this->getPublicExportFile("xml")).",".
			" public_html_file = ".$ilDB->quote($this->getPublicExportFile("html")).",".
			" header_page = ".$ilDB->quote($this->getHeaderPage()).",".
			" footer_page = ".$ilDB->quote($this->getFooterPage()).",".
			" lm_menu_active = ".$ilDB->quote(ilUtil::tf2yn($this->isActiveLMMenu()))." ".
			" WHERE id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
	}

	/**
	* create new properties record
	*/
	function createProperties()
	{
		global $ilDB;
		
		$q = "INSERT INTO content_object (id) VALUES (".$ilDB->quote($this->getId()).")";
		$this->ilias->db->query($q);
		$this->readProperties();		// to get db default values
	}

	/**
	* check wether content object is online
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;
		
//echo "class ilObjContentObject::_lookupOnline($a_id) called. Use Access class instead.";

		$q = "SELECT * FROM content_object WHERE id = ".$ilDB->quote($a_id);
		$lm_set = $this->ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);

		return ilUtil::yn2tf($lm_rec["online"]);
	}

	/**
	* get all available lm layouts
	*
	* static
	*/
	function getAvailableLayouts()
	{
		// read sdir, copy files and copy directories recursively
		$dir = opendir("./Modules/LearningModule/layouts/lm");

		$layouts = array();

		while($file = readdir($dir))
		{
			if ($file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
			{
				// directories
				if (@is_dir("./Modules/LearningModule/layouts/lm/".$file))
				{
					$layouts[$file] = $file;
				}
			}
		}
		asort($layouts);
		return $layouts;
	}

	/**
	* checks wether the preconditions of a page are fulfilled or not
	*/
	function _checkPreconditionsOfPage($cont_ref_id,$cont_obj_id, $page_id)
	{
		global $ilias,$ilUser,$ilErr;

		$lm_tree = new ilTree($cont_obj_id);
		$lm_tree->setTableNames('lm_tree','lm_data');
		$lm_tree->setTreeTablePK("lm_id");

		if ($lm_tree->isInTree($page_id))
		{
			$path = $lm_tree->getPathFull($page_id, $lm_tree->readRootId());
			foreach ($path as $node)
			{
				if ($node["type"] == "st")
				{
					if (!ilConditionHandler::_checkAllConditionsOfTarget($cont_ref_id,$node["child"], "st"))
					{
						return false;
					}
				}
			}
		}
		
		return true;
	}

	/**
	* gets all missing preconditions of page
	*/
	function _getMissingPreconditionsOfPage($cont_ref_id,$cont_obj_id, $page_id)
	{
		$lm_tree = new ilTree($cont_obj_id);
		$lm_tree->setTableNames('lm_tree','lm_data');
		$lm_tree->setTreeTablePK("lm_id");

		$conds = array();
		if ($lm_tree->isInTree($page_id))
		{
			// get full path of page
			$path = $lm_tree->getPathFull($page_id, $lm_tree->readRootId());
			foreach ($path as $node)
			{
				if ($node["type"] == "st")
				{
					// get all preconditions of upper chapters
					$tconds = ilConditionHandler::_getConditionsOfTarget($cont_ref_id,$node["child"], "st");
					foreach ($tconds as $tcond)
					{
						// store all missing preconditions
						if (!ilConditionHandler::_checkCondition($tcond["id"]))
						{
							$conds[] = $tcond;
						}
					}
				}
			}
		}
		
		return $conds;
	}

	/**
	* get top chapter of page for that any precondition is missing
	*/
	function _getMissingPreconditionsTopChapter($cont_obj_ref_id,$cont_obj_id, $page_id)
	{
		$lm_tree = new ilTree($cont_obj_id);
		$lm_tree->setTableNames('lm_tree','lm_data');
		$lm_tree->setTreeTablePK("lm_id");

		$conds = array();
		if ($lm_tree->isInTree($page_id))
		{
			// get full path of page
			$path = $lm_tree->getPathFull($page_id, $lm_tree->readRootId());
			foreach ($path as $node)
			{
				if ($node["type"] == "st")
				{
					// get all preconditions of upper chapters
					$tconds = ilConditionHandler::_getConditionsOfTarget($cont_obj_ref_id,$node["child"], "st");
					foreach ($tconds as $tcond)
					{
						// look for missing precondition
						if (!ilConditionHandler::_checkCondition($tcond["id"]))
						{
							return $node["child"];
						}
					}
				}
			}
		}
		
		return "";
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
				//echo "Content Object ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				
				//echo "Content Object ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Content Object ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				
				//echo "Content Object ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
				
				//echo "Content Object ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
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
	 * STATIC METHOD
	 * search for dbk data. This method is called from class.ilSearch
	 * @param	object reference on object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$search_obj,$a_search_in)
	{
		global $ilBench;

		switch($a_search_in)
		{
			case 'meta':
				// FILTER ALL DBK OBJECTS
				$in		= $search_obj->getInStatement("r.ref_id");
				$where	= $search_obj->getWhereCondition("fulltext",array("xv.tag_value"));

				/* very slow on mysql < 4.0.18 (? or everytime ?)
				$query = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"lm_data AS l,xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ((o.obj_id=l.lm_id AND xm.ns_book_fk=l.obj_id) OR ".
					"(o.obj_id=xm.ns_book_fk AND xm.ns_type IN ('lm','bib'))) ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'lm'";*/

				$query1 = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ( ".
					"(o.obj_id=xm.ns_book_fk AND xm.ns_type IN ('lm','bib'))) ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'lm'";

				// BEGINNING SELECT WITH SEARCH RESULTS IS MUCH FASTER
				$query1 = "SELECT DISTINCT(r.ref_id) as ref_id FROM xmlvalue AS xv ".
					"LEFT JOIN xmlnestedset AS xm ON xm.ns_tag_fk=xv.tag_fk ".
					"LEFT JOIN object_data AS o ON o.obj_id = xm.ns_book_fk ".
					"LEFT JOIN object_reference AS r ON o.obj_id = r.obj_id ".
					$where.
					$in.
					" AND o.type = 'lm' AND xm.ns_type IN ('lm','bib')";

				$query2 = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"lm_data AS l,xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ((o.obj_id=l.lm_id AND xm.ns_book_fk=l.obj_id)".
					") ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'lm'";

				$query2 = "SELECT DISTINCT(r.ref_id) as ref_id FROM xmlvalue AS xv ".
					"LEFT JOIN xmlnestedset AS xm ON xm.ns_tag_fk = xv.tag_fk ".
					"LEFT JOIN lm_data AS l ON l.obj_id = xm.ns_book_fk ".
					"LEFT JOIN object_data AS o ON o.obj_id = l.lm_id ".
					"LEFT JOIN object_reference AS r ON r.obj_id = o.obj_id ".
					$where.
					$in.
					"AND o.type = 'lm'";

				$ilBench->start("Search", "ilObjContentObject_search_meta");
				$res1 = $search_obj->ilias->db->query($query1);
				$res2 = $search_obj->ilias->db->query($query2);
				$ilBench->stop("Search", "ilObjContentObject_search_meta");

				$counter = 0;
				$ids = array();
				while($row = $res1->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$ids[] = $row->ref_id;
					$result[$counter]["id"]		=  $row->ref_id;
					++$counter;
				}
				while($row = $res2->fetchRow(DB_FETCHMODE_OBJECT))
				{
					if(in_array($row->ref_id,$ids))
					{
						continue;
					}
					$result[$counter]["id"]		=  $row->ref_id;
					++$counter;
				}
				break;

			case 'content':
				$in		= $search_obj->getInStatement("r.ref_id");
				$where	= $search_obj->getWhereCondition("fulltext",array("pg.content"));

				// slow on mysql < 4.0.18 (join bug)
				/*
				$query = "SELECT DISTINCT(r.ref_id) AS ref_id ,pg.page_id AS page_id FROM page_object AS pg ".
					"INNER JOIN object_reference AS r ON pg.parent_id = r.obj_id ".
					$where.
					$in.
					"AND pg.parent_type = 'lm' ";*/

				$query = "SELECT DISTINCT(r.ref_id) AS ref_id ,pg.page_id AS page_id FROM page_object AS pg ".
					", object_reference AS r ".
					$where.
					" AND pg.parent_id = r.obj_id ".
					$in.
					" AND pg.parent_type = 'lm' ";

				$query = "SELECT DISTINCT(r.ref_id) AS ref_id ,pg.page_id AS page_id FROM page_object AS pg ".
					"LEFT JOIN object_data AS o ON o.obj_id = pg.parent_id ".
					"LEFT JOIN object_reference AS r ON o.obj_id = r.obj_id ".
					$where.
					$in.
					" AND pg.parent_type = 'lm'";

				$ilBench->start("Search", "ilObjContentObject_search_content");
				$res = $search_obj->ilias->db->query($query);
				$ilBench->stop("Search", "ilObjContentObject_search_content");

				$counter = 0;
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$result[$counter]["id"]	= $row->ref_id;
					$result[$counter]["page_id"] = $row->page_id;
					++$counter;
				}
				break;
		}
		return $result ? $result : array();
	}
	
	/**
	* checks if page has a successor page
	*/
	function hasSuccessorPage($a_cont_obj_id, $a_page_id)
	{
		$tree = new ilTree($a_cont_obj_id);
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");
		if ($tree->isInTree($a_page_id))
		{
			$succ = $tree->fetchSuccessorNode($a_page_id, "pg");
			if ($succ > 0)
			{
				return true;
			}
		}
		return false;
	}

	
	function checkTree()
	{
		$tree = new ilTree($this->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");
		$tree->checkTree();
		$tree->checkTreeChilds();
//echo "checked";
	}

	/**
	* fix tree
	*/
	function fixTree()
	{
		global $ilDB;

		$tree =& $this->getLMTree();

		// delete subtrees that have no lm_data records
		$nodes = $tree->getSubtree($tree->getNodeData($tree->getRootId()));
		foreach ($nodes as $node)
		{
			$q = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($node["child"]);
			$obj_set = $ilDB->query($q);
			$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
			if (!$obj_rec)
			{
				$node_data = $tree->getNodeData($node["child"]);
				$tree->deleteTree($node_data);
			}
		}

		// delete subtrees that have pages as parent
		$nodes = $tree->getSubtree($tree->getNodeData($tree->getRootId()));
		foreach ($nodes as $node)
		{
			$q = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($node["parent"]);
			$obj_set = $ilDB->query($q);
			$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
			if ($obj_rec["type"] == "pg")
			{
				$node_data = $tree->getNodeData($node["child"]);
				if ($tree->isInTree($node["child"]))
				{
					$tree->deleteTree($node_data);
				}
			}
		}

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

		$attrs = array();
		switch($this->getType())
		{
			case "lm":
				$attrs["Type"] = "LearningModule";
				break;

			case "dbk":
				$attrs["Type"] = "LibObject";
				break;
		}
		$a_xml_writer->xmlStartTag("ContentObject", $attrs);

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		// StructureObjects
//echo "ContObj:".$a_inst.":<br>";
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Structure Objects");
		$ilBench->start("ContentObjectExport", "exportStructureObjects");
		$this->exportXMLStructureObjects($a_xml_writer, $a_inst, $expLog);
		$ilBench->stop("ContentObjectExport", "exportStructureObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Structure Objects");

		// PageObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Page Objects");
		$ilBench->start("ContentObjectExport", "exportPageObjects");
		$this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog);
		$ilBench->stop("ContentObjectExport", "exportPageObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Page Objects");

		// MediaObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Media Objects");
		$ilBench->start("ContentObjectExport", "exportMediaObjects");
		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportMediaObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Media Objects");

		// FileItems
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export File Items");
		$ilBench->start("ContentObjectExport", "exportFileItems");
		$this->exportFileItems($a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportFileItems");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export File Items");
		
		// To do: implement version selection/detection
		// Properties
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Properties");
		$this->exportXMLProperties($a_xml_writer, $expLog);
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Properties");

		// Glossary
		// not implemented

		// Bibliography
		// not implemented

		// Layout
		// not implemented

		$a_xml_writer->xmlEndTag("ContentObject");
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
	* export structure objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLStructureObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
		include_once './Modules/LearningModule/classes/class.ilStructureObject.php';

		$childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
		foreach ($childs as $child)
		{
			if($child["type"] != "st")
			{
				continue;
			}

			$structure_obj = new ilStructureObject($this, $child["obj_id"]);
			$structure_obj->exportXML($a_xml_writer, $a_inst, $expLog);
			unset($structure_obj);
		}
	}


	/**
	* export page objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
		global $ilBench;

		include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";

		$pages = ilLMPageObject::getPageList($this->getId());
		foreach ($pages as $page)
		{
			$ilBench->start("ContentObjectExport", "exportPageObject");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$page["obj_id"]);

			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportPageObject_getLMPageObject");
			$page_obj = new ilLMPageObject($this, $page["obj_id"]);
			$ilBench->stop("ContentObjectExport", "exportPageObject_getLMPageObject");
			$ilBench->start("ContentObjectExport", "exportPageObject_XML");
			$page_obj->exportXML($a_xml_writer, "normal", $a_inst);
			$ilBench->stop("ContentObjectExport", "exportPageObject_XML");

			// collect media objects
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
			$mob_ids = $page_obj->getMediaObjectIDs();
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

			// collect all file items
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
			$file_ids = $page_obj->getFileItemIds();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");

			unset($page_obj);

			$ilBench->stop("ContentObjectExport", "exportPageObject");
		}
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

		$linked_mobs = array();
		
		// mobs directly embedded into pages
		foreach ($this->mob_ids as $mob_id)
		{
			if ($mob_id > 0)
			{
				$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
				$media_obj = new ilObjMediaObject($mob_id);
				$media_obj->exportXML($a_xml_writer, $a_inst);
				$media_obj->exportFiles($a_target_dir);
				
				// get linked media objects (map areas)
				$med_items = $media_obj->getMediaItems();
				foreach($med_items as $med_item)
				{
					$int_links = ilMapArea::_getIntLinks($med_item->getId());
					foreach ($int_links as $k => $int_link)
					{
						if ($int_link["Type"] == "MediaObject")
						{
							include_once("./Services/COPage/classes/Pages/class.ilInternalLink.php");
							$l_id = ilInternalLink::_extractObjIdOfTarget($int_link["Target"]);
							if (ilObject::_exists($l_id))
							{
								if (!in_array($l_id, $linked_mobs) && 
									!in_array($l_id, $this->mob_ids))
								{
									$linked_mobs[] = $l_id;
								}
							}
						}
					}
				}

				unset($media_obj);
			}
		}
		
		// linked mobs (in map areas)
		foreach ($linked_mobs as $mob_id)
		{
			if ($mob_id > 0)
			{
				$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
				$media_obj = new ilObjMediaObject($mob_id);
				$media_obj->exportXML($a_xml_writer, $a_inst);
				$media_obj->exportFiles($a_target_dir);
				unset($media_obj);
			}
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
	* export properties of content object
	*
	*/
	function exportXMLProperties($a_xml_writer, &$expLog)
	{
		$attrs = array();
		$a_xml_writer->xmlStartTag("Properties", $attrs);

		// Layout
		$attrs = array("Name" => "Layout", "Value" => $this->getLayout());
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// Page Header
		$attrs = array("Name" => "PageHeader", "Value" => $this->getPageHeader());
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// TOC Mode
		$attrs = array("Name" => "TOCMode", "Value" => $this->getTOCMode());
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// LM Menu Activation
		$attrs = array("Name" => "ActiveLMMenu", "Value" =>
			ilUtil::tf2yn($this->isActiveLMMenu()));
		$a_xml_writer->xmlElement("Property", $attrs);

		// Numbering Activation
		$attrs = array("Name" => "ActiveNumbering", "Value" =>
			ilUtil::tf2yn($this->isActiveNumbering()));
		$a_xml_writer->xmlElement("Property", $attrs);

		// Table of contents button activation
		$attrs = array("Name" => "ActiveTOC", "Value" =>
			ilUtil::tf2yn($this->isActiveTOC()));
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// Print view button activation
		$attrs = array("Name" => "ActivePrintView", "Value" =>
			ilUtil::tf2yn($this->isActivePrintView()));
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// Note that download button is not saved, because
		// download files do not exist after import

		// Clean frames
		$attrs = array("Name" => "CleanFrames", "Value" =>
			ilUtil::tf2yn($this->cleanFrames()));
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// Public notes activation
		$attrs = array("Name" => "PublicNotes", "Value" =>
			ilUtil::tf2yn($this->publicNotes()));
		$a_xml_writer->xmlElement("Property", $attrs);
		
		// History comments for authors activation
		$attrs = array("Name" => "HistoryUserComments", "Value" =>
			ilUtil::tf2yn($this->isActiveHistoryUserComments()));
		$a_xml_writer->xmlElement("Property", $attrs);

		// Header Page
		if ($this->getHeaderPage() > 0)
		{
			$attrs = array("Name" => "HeaderPage", "Value" =>
				"il_".IL_INST_ID."_pg_".$this->getHeaderPage());
			$a_xml_writer->xmlElement("Property", $attrs);
		}

		// Footer Page
		if ($this->getFooterPage() > 0)
		{
			$attrs = array("Name" => "FooterPage", "Value" =>
				"il_".IL_INST_ID."_pg_".$this->getFooterPage());
			$a_xml_writer->xmlElement("Property", $attrs);
		}

		$a_xml_writer->xmlEndTag("Properties");
	}

	/**
	* get export files
	*/
	function getExportFiles()
	{
		$file = array();
		
		$types = array("xml", "html", "scorm");
		
		foreach ($types as $type)
		{
			$dir = $this->getExportDirectory($type);
			// quit if import dir not available
			if (!@is_dir($dir) or
				!is_writeable($dir))
			{
				continue;
			}
	
			// open directory
			$cdir = dir($dir);
	
			// initialize array
	
			// get files and save the in the array
			while ($entry = $cdir->read())
			{
				if ($entry != "." and
					$entry != ".." and
					substr($entry, -4) == ".zip" and
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(lm_)*[0-9]+\.zip\$", $entry))
				{
					$file[$entry.$type] = array("type" => $type, "file" => $entry,
						"size" => filesize($dir."/".$entry));
				}
			}
	
			// close import directory
			$cdir->close();
		}

		// sort files
		ksort ($file);
		reset ($file);
		return $file;
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
	* get offline files
	*/
	function getOfflineFiles($dir)
	{
		// quit if offline dir not available
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
				substr($entry, -4) == ".pdf" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(lm_)*[0-9]+\.pdf\$", $entry))
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
	* export scorm package
	*/
	function exportSCORM($a_target_dir, $log)
	{
		ilUtil::delDir($a_target_dir);
		ilUtil::makeDir($a_target_dir);
		//ilUtil::makeDir($a_target_dir."/res");
		
		// export everything to html
		$this->exportHTML($a_target_dir."/res", $log, false, "scorm");
		
		// build manifest file
		include("./Modules/LearningModule/classes/class.ilContObjectManifestBuilder.php");
		$man_builder = new ilContObjectManifestBuilder($this);
		$man_builder->buildManifest();
		$man_builder->dump($a_target_dir);
		
		// copy scorm 1.2 schema definitions
		copy("Modules/LearningModule/scorm_xsd/adlcp_rootv1p2.xsd", $a_target_dir."/adlcp_rootv1p2.xsd");
		copy("Modules/LearningModule/scorm_xsd/imscp_rootv1p1p2.xsd", $a_target_dir."/imscp_rootv1p1p2.xsd");
		copy("Modules/LearningModule/scorm_xsd/imsmd_rootv1p2p1.xsd", $a_target_dir."/imsmd_rootv1p2p1.xsd");
		copy("Modules/LearningModule/scorm_xsd/ims_xml.xsd", $a_target_dir."/ims_xml.xsd");

		// zip it all
		$date = time();
		$zip_file = $a_target_dir."/".$date."__".IL_INST_ID."__".
			$this->getType()."_".$this->getId().".zip";
	//echo "zip-".$a_target_dir."-to-".$zip_file;
		ilUtil::zip(array($a_target_dir."/res",
			$a_target_dir."/imsmanifest.xml",
			$a_target_dir."/adlcp_rootv1p2.xsd",
			$a_target_dir."/imscp_rootv1p1p2.xsd",
			$a_target_dir."/ims_xml.xsd",
			$a_target_dir."/imsmd_rootv1p2p1.xsd")
			, $zip_file);

		$dest_file = $this->getExportDirectory("scorm")."/".$date."__".IL_INST_ID."__".
			$this->getType()."_".$this->getId().".zip";
		
		rename($zip_file, $dest_file);
		ilUtil::delDir($a_target_dir);

	}

	
	/**
	* export html package
	*/
	function exportHTML($a_target_dir, $log, $a_zip_file = true, $a_export_format = "html")
	{
		global $ilias, $tpl, $ilBench, $ilLocator;

		// initialize temporary target directory
		ilUtil::delDir($a_target_dir);
		ilUtil::makeDir($a_target_dir);
		$mob_dir = $a_target_dir."/mobs";
		ilUtil::makeDir($mob_dir);
		$file_dir = $a_target_dir."/files";
		ilUtil::makeDir($file_dir);
		$teximg_dir = $a_target_dir."/teximg";
		ilUtil::makeDir($teximg_dir);
		$GLOBALS["teximgcnt"] = 0;

		// export system style sheet
		$location_stylesheet = ilUtil::getStyleSheetLocation("filesystem");
		$style_name = $ilias->account->prefs["style"].".css";
		copy($location_stylesheet, $a_target_dir."/".$style_name);
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		
		// export content style sheet
		$ilBench->start("ExportHTML", "exportContentStyle");
		if ($this->getStyleSheetId() < 1)
		{
			$cont_stylesheet = "./Services/COPage/css/content.css";
			copy($cont_stylesheet, $a_target_dir."/content.css");
		}
		else
		{
			$style = new ilObjStyleSheet($this->getStyleSheetId());
			$style->writeCSSFile($a_target_dir."/content.css");
		}
		$ilBench->stop("ExportHTML", "exportContentStyle");
		
		// export syntax highlighting style
		$syn_stylesheet = ilObjStyleSheet::getSyntaxStylePath();
		copy($syn_stylesheet, $a_target_dir."/syntaxhighlight.css");

		// get learning module presentation gui class
		include_once("./Modules/LearningModule/classes/class.ilLMPresentationGUI.php");
		$_GET["cmd"] = "nop";
		$lm_gui =& new ilLMPresentationGUI();
		$lm_gui->setOfflineMode(true);
		$lm_gui->setOfflineDirectory($a_target_dir);
		$lm_gui->setExportFormat($a_export_format);

		// export pages
		$ilBench->start("ExportHTML", "exportHTMLPages");
		$this->exportHTMLPages($lm_gui, $a_target_dir);
		$ilBench->stop("ExportHTML", "exportHTMLPages");

		// export glossary terms
		$ilBench->start("ExportHTML", "exportHTMLGlossaryTerms");
		$this->exportHTMLGlossaryTerms($lm_gui, $a_target_dir);
		$ilBench->stop("ExportHTML", "exportHTMLGlossaryTerms");

		// export all media objects
		$ilBench->start("ExportHTML", "exportHTMLMediaObjects");
		foreach ($this->offline_mobs as $mob)
		{
			if (ilObject::_exists($mob))
			{
				$this->exportHTMLMOB($a_target_dir, $lm_gui, $mob, "_blank");
			}
		}
		$_GET["obj_type"]  = "MediaObject";
		$_GET["obj_id"]  = $a_mob_id;
		$_GET["cmd"] = "";
		$ilBench->stop("ExportHTML", "exportHTMLMediaObjects");

		// export all file objects
		$ilBench->start("ExportHTML", "exportHTMLFileObjects");
		foreach ($this->offline_files as $file)
		{
			$this->exportHTMLFile($a_target_dir, $file);
		}
		$ilBench->stop("ExportHTML", "exportHTMLFileObjects");

		// export table of contents
		$ilBench->start("ExportHTML", "exportHTMLTOC");
		$ilLocator->clearItems();
		if ($this->isActiveTOC())
		{
			$tpl = new ilTemplate("tpl.main.html", true, true);
			//$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
			$content =& $lm_gui->showTableOfContents();
			$file = $a_target_dir."/table_of_contents.html";
				
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
		$ilBench->stop("ExportHTML", "exportHTMLTOC");

		// export images
		$ilBench->start("ExportHTML", "exportHTMLImages");
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
		copy(ilUtil::getImagePath("icon_lm.gif", false, "filesystem"),
			$image_dir."/icon_lm.gif");
		copy(ilUtil::getImagePath("nav_arr_L.gif", false, "filesystem"),
			$image_dir."/nav_arr_L.gif");
		copy(ilUtil::getImagePath("nav_arr_R.gif", false, "filesystem"),
			$image_dir."/nav_arr_R.gif");
			
		copy(ilUtil::getImagePath("download.gif", false, "filesystem"),
			$image_dir."/download.gif");
		$ilBench->stop("ExportHTML", "exportHTMLImages");
			
		// template workaround: reset of template 
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

		// zip everything
		$ilBench->start("ExportHTML", "zip");
		if (true)
		{
			if ($a_zip_file)
			{
				// zip it all
				$date = time();
				$zip_file = $this->getExportDirectory("html")."/".$date."__".IL_INST_ID."__".
					$this->getType()."_".$this->getId().".zip";
				ilUtil::zip($a_target_dir, $zip_file);
				ilUtil::delDir($a_target_dir);
			}
		}
		$ilBench->stop("ExportHTML", "zip");

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
	* export media object to html
	*/
	function exportHTMLMOB($a_target_dir, &$a_lm_gui, $a_mob_id, $a_frame = "")
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
		$_GET["frame"] = $a_frame;
		$_GET["cmd"] = "";
		$content =& $a_lm_gui->media();
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
			$_GET["obj_type"]  = "";
			$_GET["frame"]  = "";
			$_GET["mob_id"]  = $a_mob_id;
			$_GET["cmd"] = "fullscreen";
			$content =& $a_lm_gui->fullscreen();
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
	* export glossary terms
	*/
	function exportHTMLGlossaryTerms(&$a_lm_gui, $a_target_dir)
	{
		global $ilLocator;
		
		foreach($this->offline_int_links as $int_link)
		{
			$ilLocator->clearItems();
			if ($int_link["type"] == "git")
			{
				$tpl = new ilTemplate("tpl.main.html", true, true);
				$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

				$_GET["obj_id"] = $int_link["id"];
				$_GET["frame"] = "_blank";
				$content =& $a_lm_gui->glossary();
				$file = $a_target_dir."/term_".$int_link["id"].".html";
					
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
				$defs = ilGlossaryDefinition::getDefinitionList($int_link["id"]);
				foreach($defs as $def)
				{
					$def_mobs = ilObjMediaObject::_getMobsOfObject("gdf:pg", $def["id"]);
					foreach($def_mobs as $def_mob)
					{
						$this->offline_mobs[$def_mob] = $def_mob;
					}
					
					// get all files of page
					$def_files = ilObjFile::_getFilesOfObject("gdf:pg", $page["obj_id"]);
					$this->offline_files = array_merge($this->offline_files, $def_files);

				}
				
			}
		}
	}
	
	/**
	* export all pages of learning module to html file
	*/
	function exportHTMLPages(&$a_lm_gui, $a_target_dir)
	{
		global $tpl, $ilBench, $ilLocator;
				
		$pages = ilLMPageObject::getPageList($this->getId());
		
		$lm_tree =& $this->getLMTree();
		$first_page = $lm_tree->fetchSuccessorNode($lm_tree->getRootId(), "pg");
		$this->first_page_id = $first_page["child"];

		// iterate all learning module pages
		$mobs = array();
		$int_links = array();
		$this->offline_files = array();
		
		foreach ($pages as $page)
		{
			$ilLocator->clearItems();
			$ilBench->start("ExportHTML", "exportHTMLPage");
			$ilBench->start("ExportHTML", "exportPageHTML");
			$this->exportPageHTML($a_lm_gui, $a_target_dir, $page["obj_id"]);
			$ilBench->stop("ExportHTML", "exportPageHTML");
			
			// get all media objects of page
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$pg_mobs = ilObjMediaObject::_getMobsOfObject($this->getType().":pg", $page["obj_id"]);
			foreach($pg_mobs as $pg_mob)
			{
				$mobs[$pg_mob] = $pg_mob;
			}
			
			// get all internal links of page
			$pg_links = ilInternalLink::_getTargetsOfSource($this->getType().":pg", $page["obj_id"]);
			$int_links = array_merge($int_links, $pg_links);
			
			// get all files of page
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$pg_files = ilObjFile::_getFilesOfObject($this->getType().":pg", $page["obj_id"]);
			$this->offline_files = array_merge($this->offline_files, $pg_files);
			
			$ilBench->stop("ExportHTML", "exportHTMLPage");
		}
		$this->offline_mobs = $mobs;
		$this->offline_int_links = $int_links;
		
		
	}

	/**
	* export page html
	*/
	function exportPageHTML(&$a_lm_gui, $a_target_dir, $a_lm_page_id, $a_frame = "")
	{
		global $ilias, $tpl, $ilBench;
		
//echo "<br>B: export Page HTML ($a_lm_page_id)"; flush();
		// template workaround: reset of template 
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

		$_GET["obj_id"] = $a_lm_page_id;
		$_GET["frame"] = $a_frame;

		if ($a_frame == "")
		{
			if ($nid = ilLMObject::_lookupNID($a_lm_gui->lm->getId(), $a_lm_page_id, "pg"))
			{
				$file = $a_target_dir."/lm_pg_".$nid.".html";
			}
			else
			{
				$file = $a_target_dir."/lm_pg_".$a_lm_page_id.".html";
			}
		}
		else
		{
			if ($a_frame != "toc")
			{
				$file = $a_target_dir."/frame_".$a_lm_page_id."_".$a_frame.".html";
			}
			else
			{
				$file = $a_target_dir."/frame_".$a_frame.".html";
			}
		}
		
		// return if file is already existing
		if (@is_file($file))
		{
			return;
		}

		$ilBench->start("ExportHTML", "layout");
		$ilBench->start("ExportHTML", "layout_".$a_frame);
		$content =& $a_lm_gui->layout("main.xml", false);
		$ilBench->stop("ExportHTML", "layout_".$a_frame);
		$ilBench->stop("ExportHTML", "layout");

		// open file
		if (!($fp = @fopen($file,"w+")))
		{
			die ("<b>Error</b>: Could not open \"".$file."\" for writing".
					" in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
		}
	
		// set file permissions
		chmod($file, 0770);
			
		// write xml data into the file
		fwrite($fp, $content);
		
		// close file
		fclose($fp);

		if ($this->first_page_id == $a_lm_page_id && $a_frame == "")
		{
			copy($file, $a_target_dir."/index.html");
		}

		// write frames of frameset
		$ilBench->start("ExportHTML", "getCurrentFrameSet");
		$frameset = $a_lm_gui->getCurrentFrameSet();
		$ilBench->stop("ExportHTML", "getCurrentFrameSet");
		
		foreach ($frameset as $frame)
		{				
			$this->exportPageHTML($a_lm_gui, $a_target_dir, $a_lm_page_id, $frame);
		}

	}

	/**
	* export object to fo
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportFO(&$a_xml_writer, $a_target_dir)
	{
		global $ilBench;

		// fo:root (start)
		$attrs = array();
		$attrs["xmlns:fo"] = "http://www.w3.org/1999/XSL/Format";
		$a_xml_writer->xmlStartTag("fo:root", $attrs);

		// fo:layout-master-set (start)
		$attrs = array();
		$a_xml_writer->xmlStartTag("fo:layout-master-set", $attrs);

		// fo:simple-page-master (start)
		$attrs = array();
		$attrs["master-name"] = "DinA4";
		$attrs["page-height"] = "29.7cm";
		$attrs["page-width"] = "21cm";
		$attrs["margin-top"] = "4cm";
		$attrs["margin-bottom"] = "1cm";
		$attrs["margin-left"] = "2.8cm";
		$attrs["margin-right"] = "7.3cm";
		$a_xml_writer->xmlStartTag("fo:simple-page-master", $attrs);

		// fo:region-body (complete)
		$attrs = array();
		$attrs["margin-top"] = "0cm";
		$attrs["margin-bottom"] = "1.25cm";
		$a_xml_writer->xmlElement("fo:region-body", $attrs);

		// fo:region-before (complete)
		$attrs = array();
		$attrs["extent"] = "1cm";
		$a_xml_writer->xmlElement("fo:region-before", $attrs);

		// fo:region-after (complete)
		$attrs = array();
		$attrs["extent"] = "1cm";
		$a_xml_writer->xmlElement("fo:region-after", $attrs);

		// fo:simple-page-master (end)
		$a_xml_writer->xmlEndTag("fo:simple-page-master");

		// fo:layout-master-set (end)
		$a_xml_writer->xmlEndTag("fo:layout-master-set");

		// fo:page-sequence (start)
		$attrs = array();
		$attrs["master-reference"] = "DinA4";
		$a_xml_writer->xmlStartTag("fo:page-sequence", $attrs);

		// fo:flow (start)
		$attrs = array();
		$attrs["flow-name"] = "xsl-region-body";
		$a_xml_writer->xmlStartTag("fo:flow", $attrs);


		// StructureObjects
		//$expLog->write(date("[y-m-d H:i:s] ")."Start Export Structure Objects");
		$ilBench->start("ContentObjectExport", "exportFOStructureObjects");
		$this->exportFOStructureObjects($a_xml_writer, $expLog);
		$ilBench->stop("ContentObjectExport", "exportFOStructureObjects");
		//$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Structure Objects");*/

		// fo:flow (end)
		$a_xml_writer->xmlEndTag("fo:flow");

		// fo:page-sequence (end)
		$a_xml_writer->xmlEndTag("fo:page-sequence");

		// fo:root (end)
		$a_xml_writer->xmlEndTag("fo:root");
	}

	/**
	* export structure objects to fo
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportFOStructureObjects(&$a_xml_writer)
	{
		$childs = $this->lm_tree->getChilds($this->lm_tree->getRootId());
		foreach ($childs as $child)
		{
			if($child["type"] != "st")
			{
				continue;
			}

			$structure_obj = new ilStructureObject($this, $child["obj_id"]);
			$structure_obj->exportFO($a_xml_writer, $expLog);
			unset($structure_obj);
		}
	}

	function getXMLZip()
	{
		include_once("./Modules/LearningModule/classes/class.ilContObjectExport.php");

		$cont_exp = new ilContObjectExport($this,'xml');

		$export_file = $cont_exp->buildExportFile();
		return $export_file;
	}		


}
?>
