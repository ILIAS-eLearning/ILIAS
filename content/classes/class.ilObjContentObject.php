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


/**
* Class ilObjContentObject
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "classes/class.ilObject.php";
require_once "classes/class.ilMetaData.php";
//require_once("content/classes/class.ilPageObject.php");

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
		if ($a_id == 0)
		{
			$new_meta =& new ilMetaData();
			$this->assignMetaData($new_meta);
		}
	}

	/**
	* create content object
	*/
	function create($a_upload = false)
	{
		global $ilUser;

		parent::create();
		$this->createProperties();
		if (!$a_upload)
		{
			if (is_object($ilUser))
			{
				//$this->meta_data->setLanguage($ilUser->getLanguage());
			}
			$this->meta_data->setId($this->getId());
			$this->meta_data->setType($this->getType());
			$this->meta_data->setTitle($this->getTitle());
			$this->meta_data->setDescription($this->getDescription());
			$this->meta_data->setObject($this);
			$this->meta_data->create();
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
		$rbacadmin->copyRolePermission($this->getAuthorRoleTemplateId(), 8, $rfoldObj->getRefId(), $roleObj->getId());

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
		$this->lm_tree = new ilTree($this->getId());
		$this->lm_tree->setTableNames('lm_tree','lm_data');
		$this->lm_tree->setTreeTablePK("lm_id");
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());

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
//		return parent::getTitle();
		return $this->meta_data->getTitle();
	}

	/**
	* set title of content object
	*/
	function setTitle($a_title)
	{
//		parent::setTitle($a_title);
		$this->meta_data->setTitle($a_title);
	}

	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
//		return parent::getDescription();
		return $this->meta_data->getDescription();
	}

	/**
	* set description of content object
	*/
	function setDescription($a_description)
	{
//		parent::setTitle($a_title);
		$this->meta_data->setDescription($a_description);
	}

	/**
	* assign a meta data object to content object
	*
	* @param	object		$a_meta_data	meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object of content object
	*
	* @return	object		meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}


	/**
	* update meta data only
	*/
	function updateMetaData()
	{
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

	function getImportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}

	function setImportId($a_id)
	{
		$this->meta_data->setImportIdentifierEntryID($a_id);
	}

	function getTree()
	{
		return $this->lm_tree;
	}

	/**
	* update complete object (meta data and properties)
	*/
	function update()
	{
		$this->updateMetaData();
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
		if(!@is_dir($lm_dir))
		{
			@mkdir($lm_dir);
			@chmod($lm_dir,0755);
		}
		if(!@is_dir($lm_dir))
		{
			$this->ilias->raiseError("Creation of Learning Module Directory failed.",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory (data_dir/lm_data/lm_<id>/import)
		$import_dir = $lm_dir."/import";
		if(!@is_dir($import_dir))
		{
			@mkdir($import_dir);
			@chmod($import_dir,0755);
		}
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed.",$this->ilias->error_obj->FATAL);
		}
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
	function createExportDirectory($ref_id="")
	{
		$lm_data_dir = ilUtil::getDataDir()."/lm_data";
		if(!is_writable($lm_data_dir))
		{
			$this->ilias->raiseError("Content object Data Directory (".$lm_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		// create learning module directory (data_dir/lm_data/lm_<id>)
        if ($ref_id=="") $ref_id = $this->getId();
		$lm_dir = $lm_data_dir."/lm_".$ref_id;
		if(!@is_dir($lm_dir))
		{
			@mkdir($lm_dir);
			@chmod($lm_dir,0755);
		}
		if(!@is_dir($lm_dir))
		{
			$this->ilias->raiseError("Creation of Learning Module Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $lm_dir."/export";
		if(!@is_dir($export_dir))
		{
			@mkdir($export_dir);
			@chmod($export_dir,0755);
		}
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get export directory of lm
	*/
	function getExportDirectory($ref_id="")
	{
        if ($ref_id=="") $ref_id = $this->getId();
		$export_dir = ilUtil::getDataDir()."/lm_data"."/lm_".$ref_id."/export";
		if(@is_dir($export_dir))
		{
			return $export_dir;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	* copy all properties and subobjects of a learning module.
	*
	* @access	public
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);

		// todo: put here lm specific stuff

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete learning module and all related data
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
		
		// delete lm object data
		include_once("content/classes/class.ilLMObject.php");
		ilLMObject::_deleteAllObjectData($this);

		// delete meta data of content object
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();

		// delete learning module tree
		$this->lm_tree->removeTree($this->lm_tree->getTreeId());

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

	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	function getOnline()
	{
		return $this->online;
	}

	/**
	* read content object properties
	*/
	function readProperties()
	{
		$q = "SELECT * FROM content_object WHERE id = '".$this->getId()."'";
		$lm_set = $this->ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setLayout($lm_rec["default_layout"]);
		$this->setStyleSheetId($lm_rec["stylesheet"]);
		$this->setPageHeader($lm_rec["page_header"]);
		$this->setOnline(ilUtil::yn2tf($lm_rec["online"]));
	}

	/**
	* update content object properties
	*/
	function updateProperties()
	{
		$q = "UPDATE content_object SET ".
			" default_layout = '".$this->getLayout()."', ".
			" stylesheet = '".$this->getStyleSheetId()."',".
			" page_header = '".$this->getPageHeader()."',".
			" online = '".ilUtil::tf2yn($this->getOnline())."'".
			" WHERE id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}

	/**
	* create new properties record
	*/
	function createProperties()
	{
		$q = "INSERT INTO content_object (id) VALUES ('".$this->getId()."')";
		$this->ilias->db->query($q);
		$this->readProperties();		// to get db default values
	}


	/**
	* get all available lm layouts
	*
	* static
	*/
	function getAvailableLayouts()
	{
		// read sdir, copy files and copy directories recursively
		$dir = opendir("./layouts/lm");

		$layouts = array();

		while($file = readdir($dir))
		{
			if ($file != "." && $file != ".." && $file != "CVS")
			{
				// directories
				if (@is_dir("./layouts/lm/".$file))
				{
					$layouts[$file] = $file;
				}
			}
		}
		asort($layouts);
		return $layouts;
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
		switch($a_search_in)
		{
			case 'meta':
				// FILTER ALL DBK OBJECTS
				$in		= $search_obj->getInStatement("r.ref_id");
				$where	= $search_obj->getWhereCondition("fulltext",array("xv.tag_value"));

				$query = "SELECT DISTINCT(r.ref_id) FROM object_reference AS r,object_data AS o, ".
					"lm_data AS l,xmlnestedset AS xm,xmlvalue AS xv ".
					$where.
					$in.
					"AND r.obj_id=o.obj_id AND ((o.obj_id=l.lm_id AND xm.ns_book_fk=l.obj_id) OR ".
					"(o.obj_id=xm.ns_book_fk AND xm.ns_type IN ('lm','bib'))) ".
					"AND xm.ns_tag_fk=xv.tag_fk ".
					"AND o.type= 'lm'";

/*
				$query = "SELECT DISTINCT(r.ref_id) AS ref_id FROM object_reference AS r ".
					"INNER JOIN object_data AS o ON r.obj_id=o.obj_id ".
					"INNER JOIN lm_data AS l ON l.lm_id = o.obj_id ".
					"INNER JOIN xmlnestedset AS xm ON (xm.ns_book_fk = l.obj_id OR xm.ns_type= 'lm' ) ".
					"INNER JOIN xmlvalue AS xv ON xm.ns_tag_fk = xv.tag_fk ".
					$where.
					$in.
					"AND o.type = 'lm'";
*/
				$res = $search_obj->ilias->db->query($query);
				$counter = 0;
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$result[$counter]["id"]		=  $row->ref_id;
					/*
					$result[$counter]["link"]	=  "content/lm_presentation.php?ref_id=".$row->ref_id;
					$result[$counter]["target"]	=  "_top";
					*/
					++$counter;
				}
				break;

			case 'content':
				$in		= $search_obj->getInStatement("r.ref_id");
				$where	= $search_obj->getWhereCondition("fulltext",array("pg.content"));

				$query = "SELECT DISTINCT(r.ref_id) AS ref_id ,pg.page_id AS page_id FROM page_object AS pg ".
					"INNER JOIN object_reference AS r ON pg.parent_id = r.obj_id ".
					$where.
					$in.
					"AND pg.parent_type = 'lm' ";

				$res = $search_obj->ilias->db->query($query);
				$counter = 0;
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$result[$counter]["id"]		= $row->ref_id;
					$result[$counter]["page_id"] = $row->page_id;
					/*
					$result[$counter]["link"]	= "content/lm_presentation.php?ref_id=".$row->ref_id;
					$result[$counter]["target"]	= "_top";
					*/
					++$counter;
				}
				break;
		}
		return $result ? $result : array();
	}

	/**
	 * STATIC METHOD
	 * create a link to the object
	 * @param	int ref_id of content object
	 * @param	string type of search ('content' or 'meta')
	 * @param	int id of page (optional only used if it has been searched for 'content')
	 * @return array array('link','target')
	 * @static
	 * @access	public
	 */
	function _getLinkToObject($a_ref_id,$a_type,$a_obj_id = 0)
	{
		switch($a_type)
		{
			case "content":
				return array("content/lm_presentation.php?ref_id=".$a_ref_id."&obj_id=".$a_obj_id,"_top");
				
			case "meta":
				return array("content/lm_presentation.php?ref_id=".$a_ref_id,"_top");
		}
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

} // END class.ilObjContentObject
?>
