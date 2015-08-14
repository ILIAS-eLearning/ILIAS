<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRepositoryObjectSearchGUI
 * Repository object search 
 * 
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchGUI
{
	private $lng = null;
	private $ctrl = null;
	private $ref_id = 0;
	private $object = null;
	private $parent_obj;
	private $parent_cmd;
	
	/**
	 * Constructor
	 */
	public function __construct($a_ref_id, $a_parent_obj, $a_parent_cmd)
	{
		global $lng, $ilCtrl;
		
		$this->ref_id = $a_ref_id;
		$this->lng = $lng;
		
		$this->ctrl = $ilCtrl;
		
		
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$factory = new ilObjectFactory();
		$this->object = $factory->getInstanceByRefId($this->getRefId(),FALSE);
		
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
	}
	
	/**
	 * Get standar search block html
	 * @param type $a_title
	 * @return string
	 */
	public static function getSearchBlockHTML($a_title)
	{
		include_once './Services/Search/classes/class.ilRepositoryObjectSearchBlockGUI.php';
		$block = new ilRepositoryObjectSearchBlockGUI($a_title);
		return $block->getHTML();
	}
	
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		if(!$GLOBALS['ilAccess']->checkAccess('read','',$this->getObject()->getRefId()))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->getCtrl()->returnToParent($this->getParentGUI());
		}
		
		$next_class = $this->getCtrl()->getNextClass();
		$cmd = $this->getCtrl()->getCmd();

	
  		switch($next_class)
		{
			default:
				$this->$cmd();				
				break;
		}
	}

	/**
	 * Get language object
	 * @return ilLanguage
	 */
	public function getLang()
	{
		return $this->lng;
	}
	
	/**
	 * Get ctrl
	 * @return ilCtrl
	 */
	public function getCtrl()
	{
		return $this->ctrl;
	}


	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * 
	 * @return ilObject
	 */
	public function getObject()
	{
		return $this->object;
	}

	/**
	 * get parent gui
	 */
	public function getParentGUI()
	{
		return $this->parent_obj;
	}
	
	/**
	 * @return string
	 */
	public function getParentCmd()
	{
		return $this->parent_cmd;
	}

	/**
	 * Perform search lucene or direct search
	 */
	function performSearch()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng;
		
		include_once("./Modules/Wiki/classes/class.ilWikiSearchResultsTableGUI.php");
		
		$ilTabs->setTabActive("wiki_search_results");
		
		if(trim($_POST["search_term"]) == "")
		{
			ilUtil::sendFailure($lng->txt("wiki_please_enter_search_term"), true);
			$this->getCtrl()->returnToParent($this);
		}
		
		$search_results = ilObjWiki::_performSearch($this->object->getId(),
			ilUtil::stripSlashes($_POST["search_term"]));
		$table_gui = new ilWikiSearchResultsTableGUI($this, "performSearch",
			$this->object->getId(), $search_results, $_POST["search_term"]);
			
		$tpl->setContent($table_gui->getHTML());
	}
	
	
}
?>