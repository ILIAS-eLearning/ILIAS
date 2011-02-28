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

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
require_once("./Services/COPage/classes/class.ilPageObject.php");

// unclear whether we need this somehow...
//define ("IL_CHAPTER_TITLE", "st_title");
//define ("IL_PAGE_TITLE", "pg_title");
//define ("IL_NO_HEADER", "none");

/**
 * Class ilSCORM2004PageNode
 *
 * Handles Pages for SCORM 2004 Editing
 *
 * Note: This class has a member variable that contains an instance
 * of class ilPageObject (Services/COPage) and provides the method
 * getPageObject() to access this instance. ilPageObject handles page objects
 * and their content. Page objects can be assigned to different container like
 * ILIAS learning modules, glossaries definitions. This class, ilSCORM2004PageNode,
 * provides additional methods for the handling of page objects in
 * the SCORM 2004 Editor.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004PageNode extends ilSCORM2004Node
{
	var $id;
	var $page_object;

	/**
	 * Constructor
	 * @access	public
	 */
	function ilSCORM2004PageNode($a_slm_object, $a_id = 0)
	{
		parent::ilSCORM2004Node($a_slm_object, $a_id);
		$this->setType("page");
		$this->id = $a_id;

		$this->mobs_contained  = array();
		$this->files_contained  = array();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	 * Destructor
	 */
	function __descruct()
	{
		if(is_object($this->page_object))
		{
			unset($this->page_object);
		}
	}

	/**
	 * Read data from database
	 */
	function read()
	{
		parent::read();

		$this->page_object = new ilPageObject($this->slm_object->getType(),
			$this->id, 0, false);
	}

	/**
	 * Create Scorm Page
	 *
	 * @param	boolean		Upload Mode
	 */
	function create($a_upload = false,$a_layout_id = 0)
	{
		parent::create($a_upload);

		// create scorm2004 page
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Page.php");
		if(!is_object($this->page_object))
		{
			$this->page_object =& new ilSCORM2004Page($this->slm_object->getType());
		}
		$this->page_object->setId($this->getId());
		$this->page_object->setParentId($this->getSLMId());
		if ($a_layout_id == 0) {
			$this->page_object->create($a_upload);
		} else{
			$this->page_object->createWithLayoutId($a_layout_id);
		}	
	}

	/**
	 * Delete Scorm Page
	 *
	 * @param	boolean		Delete also metadata.
	 */
	function delete($a_delete_meta_data = true)
	{
		parent::delete($a_delete_meta_data);
		$this->page_object->delete();
	}


	/**
	* copy page node
	 */
	function copy($a_target_slm)
	{
		 // copy page
		$slm_page = new ilSCORM2004PageNode($a_target_slm);
		$slm_page->setTitle($this->getTitle());
		$slm_page->setSLMId($a_target_slm->getId());
		$slm_page->setType($this->getType());
		$slm_page->setDescription($this->getDescription());
		$slm_page->setImportId("il__page_".$this->getId());
		$slm_page->create(true);		// setting "upload" flag to true prevents creating of meta data

		 // copy meta data
		 include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$new_md = $md->cloneMD($a_target_slm->getId(), $slm_page->getId(), $this->getType());

		 // copy page content
		$page = $slm_page->getPageObject();
		$page->setXMLContent($this->page_object->copyXMLContent());
		$page->buildDom();
		$page->update();

		return $slm_page;
	}

	/**
	 * copy a page to another content object (learning module / dlib book)
	 */
	function &copyToOtherContObject(&$a_cont_obj)
	{
		// @todo
		/*
		 // copy page
		 $lm_page =& new ilLMPageObject($a_cont_obj);
		 $lm_page->setTitle($this->getTitle());
		 $lm_page->setLMId($a_cont_obj->getId());
		 $lm_page->setType($this->getType());
		 $lm_page->setDescription($this->getDescription());
		 $lm_page->create(true);		// setting "upload" flag to true prevents creating of meta data

		 // copy meta data
		 include_once("Services/MetaData/classes/class.ilMD.php");
		 $md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
		 $new_md =& $md->cloneMD($a_cont_obj->getId(), $lm_page->getId(), $this->getType());

		 // copy page content
		 $page =& $lm_page->getPageObject();
		 $page->setXMLContent($this->page_object->getXMLContent());
		 $page->buildDom();
		 $page->update();

		 return $lm_page;
		 */
	}

	/**
	 * split page at hierarchical id
	 *
	 * the main reason for this method being static is that a lm page
	 * object is not available within ilPageContentGUI where this method
	 * is called
	 */
	function _splitPage($a_page_id, $a_pg_parent_type, $a_hier_id)
	{
		// @todo: This has to be checked, maybe abandoned or generalized?
		/*
		// get content object (learning module / digilib book)
		$lm_id = ilLMObject::_lookupContObjID($a_page_id);
		$type = ilObject::_lookupType($lm_id, false);
		switch ($type)
		{
		case "lm":
		include_once ("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
		$cont_obj = new ilObjLearningModule($lm_id, false);
		break;

		case "dbk":
		include_once ("./Modules/LearningModule/classes/class.ilObjDlBook.php");
		$cont_obj = new ilObjDlBook($lm_id, false);
		break;
		}

		$source_lm_page =& new ilLMPageObject($cont_obj, $a_page_id);

		// create new page
		$lm_page =& new ilLMPageObject($cont_obj);
		$lm_page->setTitle($source_lm_page->getTitle());
		$lm_page->setLMId($source_lm_page->getLMId());
		$lm_page->setType($source_lm_page->getType());
		$lm_page->setDescription($source_lm_page->getDescription());
		$lm_page->create(true);

		// copy meta data
		include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($source_lm_page->getLMId(), $a_page_id, $source_lm_page->getType());
		$new_md =& $md->cloneMD($source_lm_page->getLMId(), $lm_page->getId(), $source_lm_page->getType());

		// copy complete content of source page to new page
		$source_page =& $source_lm_page->getPageObject();
		$page =& $lm_page->getPageObject();
		$page->setXMLContent($source_page->getXMLContent());
		$page->buildDom();

		// insert new page in tree (after original page)
		$tree = new ilTree($cont_obj->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");
		if ($tree->isInTree($source_lm_page->getId()))
		{
		$parent_node = $tree->getParentNodeData($source_lm_page->getId());
		$tree->insertNode($lm_page->getId(), $parent_node["child"], $source_lm_page->getId());
		}

		// remove all nodes < hierarchical id from new page (incl. update)
		$page->addHierIds();
		$page->deleteContentBeforeHierId($a_hier_id);

		// remove all nodes >= hierarchical id from source page
		$source_page->buildDom();
		$source_page->addHierIds();
		$source_page->deleteContentFromHierId($a_hier_id);

		return $lm_page;
		*/
	}

	/**
	 * split page to next page at hierarchical id
	 *
	 * the main reason for this method being static is that a lm page
	 * object is not available within ilPageContentGUI where this method
	 * is called
	 */
	// @todo: This has to be checked, maybe abandoned or generalized?
	/*
	function _splitPageNext($a_page_id, $a_pg_parent_type, $a_hier_id)
	{
	// get content object (learning module / digilib book)
	$lm_id = ilLMObject::_lookupContObjID($a_page_id);
	$type = ilObject::_lookupType($lm_id, false);
	switch ($type)
	{
	case "lm":
	include_once ("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
	$cont_obj = new ilObjLearningModule($lm_id, false);
	break;

	case "dbk":
	include_once ("./Modules/LearningModule/classes/class.ilObjDlBook.php");
	$cont_obj = new ilObjDlBook($lm_id, false);
	break;
	}
	$tree = new ilTree($cont_obj->getId());
	$tree->setTableNames('lm_tree','lm_data');
	$tree->setTreeTablePK("lm_id");

	$source_lm_page =& new ilLMPageObject($cont_obj, $a_page_id);
	$source_page =& $source_lm_page->getPageObject();

	// get next page
	$succ = $tree->fetchSuccessorNode($a_page_id, "pg");
	if ($succ["child"] > 0)
	{
	$target_lm_page =& new ilLMPageObject($cont_obj, $succ["child"]);
	$target_page =& $target_lm_page->getPageObject();
	$target_page->buildDom();
	$target_page->addHierIds();

	// move nodes to target page
	$source_page->buildDom();
	$source_page->addHierIds();
	ilPageObject::_moveContentAfterHierId($source_page, $target_page, $a_hier_id);
	//$source_page->deleteContentFromHierId($a_hier_id);

	return $succ["child"];
	}

	}
	*/

	/**
	 * Assign page object
	 *
	 * @param	object		$a_page_obj		page object
	 */
	function assignPageObject(&$a_page_obj)
	{
		$this->page_object =& $a_page_obj;
	}


	/**
	 * Get assigned page object
	 *
	 * @return	object		page object
	 */
	function &getPageObject()
	{
		return $this->page_object;
	}


	/**
	 * Set id
	 *
	 * @param	int		Page ID
	 */
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get id
	 *
	 * @return	int		Page ID
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set wether page object is an alias
	 */
	function setAlias($a_is_alias)
	{
		$this->is_alias = $a_is_alias;
	}

	function isAlias()
	{
		return $this->is_alias;
	}

	// only for page aliases
	function setOriginID($a_id)
	{
		return $this->origin_id = $a_id;
	}

	// only for page aliases
	function getOriginID()
	{
		return $this->origin_id;
	}

	/**
	 * static
	 */
	// @todo: not sure whether we need this...
	/*
	 function getPageList($lm_id)
	 {
		return ilLMObject::getObjectList($lm_id, "pg");
		}
		*/

	/**
	 * presentation title doesn't have to be page title, it may be
	 * chapter title + page title or chapter title only, depending on settings
	 *
	 * @param	string	$a_mode		IL_CHAPTER_TITLE | IL_PAGE_TITLE | IL_NO_HEADER
	 */
	// @todo: not sure whether we need this...
	/*
	 function _getPresentationTitle($a_pg_id, $a_mode = IL_CHAPTER_TITLE,
		$a_include_numbers = false)
		{
		global $ilDB;

		// select
		$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_pg_id);
		$pg_set = $ilDB->query($query);
		$pg_rec = $pg_set->fetchRow(DB_FETCHMODE_ASSOC);

		if($a_mode == IL_NO_HEADER)
		{
		return "";
		}

		$tree = new ilTree($pg_rec["lm_id"]);
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		if($a_mode == IL_PAGE_TITLE)
		{
		$nr = "";
		return $nr.$pg_rec["title"];
		}

		if ($tree->isInTree($pg_rec["obj_id"]))
		{
		$pred_node = $tree->fetchPredecessorNode($pg_rec["obj_id"], "st");
		$childs = $tree->getChildsByType($pred_node["obj_id"], "pg");
		$cnt_str = "";
		if(count($childs) > 1)
		{
		$cnt = 0;
		foreach($childs as $child)
		{
		if ($child["type"] != "pg" || ilLMPageObject::_lookupActive($child["obj_id"]))
		{
		$cnt++;
		}
		if($child["obj_id"] == $pg_rec["obj_id"])
		{
		$cur_cnt = $cnt;
		}
		}
		if ($cnt > 1)
		{
		$cnt_str = " (".$cur_cnt."/".$cnt.")";
		}
		}
		require_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
		//$struct_obj =& new ilStructureObject($pred_node["obj_id"]);
		//return $struct_obj->getTitle();
		return ilStructureObject::_getPresentationTitle($pred_node["obj_id"],
		$a_include_numbers).$cnt_str;
		//return $pred_node["title"].$cnt_str;
		}
		else
		{
		return $pg_rec["title"];
		}
		}
		*/

	


	/**
	 * get ids of all media objects within the page
	 *
	 * note: this method must be called afer exportXMLPageContent
	 */
	function getMediaObjectIds()
	{
		return $this->mobs_contained;
	}

	/**
	 * get ids of all file items within the page
	 *
	 * note: this method must be called afer exportXMLPageContent
	 */
	function getFileItemIds()
	{
		return $this->files_contained;
	}

	/**
	 * export page object to fo
	 *
	 * @param	object		$a_xml_writer	ilXmlWriter object that receives the
	 *										xml data
	 */
	/* todo: this needs to be adopted
	 function exportFO(&$a_xml_writer)
	 {
		global $ilBench;

		//$attrs = array();
		//$a_xml_writer->xmlStartTag("PageObject", $attrs);
		$title = ilLMPageObject::_getPresentationTitle($this->getId());
		if ($title != "")
		{
		$attrs = array();
		$attrs["font-family"] = "Times";
		$attrs["font-size"] = "14pt";
		$a_xml_writer->xmlElement("fo:block", $attrs, $title);
		}

		// PageContent
		$this->page_object->buildDom();
		$fo = $this->page_object->getFO();
		$a_xml_writer->appendXML($fo);

		//$a_xml_writer->xmlEndTag("PageObject");
		}
		*/

}
?>
