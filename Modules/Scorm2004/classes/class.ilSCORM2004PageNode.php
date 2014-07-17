<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Page.php");

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
 * of class ilSCORM2004Page and provides the method
 * getPageObject() to access this instance. ilSCORM2004Page handles page objects
 * and their content.
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

		$this->page_object = new ilSCORM2004Page($this->id, 0);
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
		// clone media objects, if source and target lm are not the same
		$clone_mobs = ($this->getSLMId() == $a_target_slm->getId())
			? false
			: true;

		$this->page_object->copy($page->getId(), $page->getParentType(), $page->getParentId(), $clone_mobs);

		//$page->setXMLContent($this->page_object->copyXMLContent($clone_mobs));
		//$page->buildDom();
		//$page->update();

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

}
?>
