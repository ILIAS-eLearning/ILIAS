<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilSearchSettings.php';
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';
include_once './Services/Administration/interfaces/interface.ilAdministrationCommandHandling.php';

/**
* Class ilSearchBaseGUI
*
* Base class for all search gui classes. Offers functionallities like set Locator set Header ...
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* @package ilias-search
*
* @ilCtrl_IsCalledBy ilSearchBaseGUI: ilSearchController
* 
*
*/
class ilSearchBaseGUI implements ilDesktopItemHandling, ilAdministrationCommandHandling
{
	var $settings = null;

	protected $ctrl = null;
	var $ilias = null;
	var $lng = null;
	var $tpl = null;

	/**
	* Constructor
	* @access public
	*/
	function ilSearchBaseGUI()
	{
		global $ilCtrl,$ilias,$lng,$tpl,$ilMainMenu;

		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('search');

		$ilMainMenu->setActive('search');
		$this->settings =& new ilSearchSettings();
	}

	function prepareOutput()
	{
		global $ilLocator, $lng;
		
		$this->tpl->getStandardTemplate();
		
//		$ilLocator->addItem($this->lng->txt('search'),$this->ctrl->getLinkTarget($this));
//		$this->tpl->setLocator();
		
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_src_b.gif"), $lng->txt("search"));
		$this->tpl->setTitle($lng->txt("search"));

		ilUtil::infoPanel();

	}
	
	public function handleCommand($a_cmd)
	{
		if(method_exists($this, $a_cmd))
		{
			$this->$a_cmd();
		}
		else
		{
			$a_cmd .= 'Object';
			$this->$a_cmd();	
		}
	}
	
	/**
	 * Interface methods
	 */
	public function addToDeskObject()
	{
		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::addToDesktop();
	 	$this->showSavedResults();
	}
	 
	/**
	 * Remove from dektop  
	 */
	public function removeFromDeskObject()
	{
		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::removeFromDesktop();
		$this->showSavedResults();
	}
	 
	/**
	 * Show deletion screen
	 */
	public function delete()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->delete();
	}
	 
	/**
	 * Cancel delete
	 */
	public function cancelDelete()
	{
		$this->showSavedResults();
	}
	
	/**
	 * Delete objects
	 */
	public function performDelete()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->performDelete();
	}
	
	/**
	 * Interface ilAdministrationCommandHandler
	 */
	public function cut()
	{
	

		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->cut();
	}
	 
	/**
	 * Interface ilAdministrationCommandHandler
	 */
	public function link()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->link();
	}
		 
	public function paste()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->paste();
	}
	
	public function showLinkIntoMultipleObjectsTree()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->showLinkIntoMultipleObjectsTree();
	}

	public function showMoveIntoObjectTree()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->showMoveIntoObjectTree();
	}
	
	public function performPasteIntoMultipleObjects()
	{
		include_once './Services/Administration/classes/class.ilAdministrationCommandGUI.php';
		$admin = new ilAdministrationCommandGUI($this);
		$admin->performPasteIntoMultipleObjects();
	}

	public function clear()
	{
		unset($_SESSION['clipboard']);
		$this->ctrl->redirect($this);
	}

	public function enableAdministrationPanel()
	{
		$_SESSION["il_cont_admin_panel"] = true;
		$this->ctrl->redirect($this);
	}
	
	public function disableAdministrationPanel()
	{
		$_SESSION["il_cont_admin_panel"] = false;
		$this->ctrl->redirect($this);
	}
	
	
	/**
	 * Add Locator
	 */
	public function addLocator()
	{
		$ilLocator->addItem($this->lng->txt('search'),$this->ctrl->getLinkTarget($this));
		$this->tpl->setLocator();
	}
	
	/**
	 * Add Pager
	 *
	 * @access public
	 * @param
	 * 
	 */
	protected function addPager($result,$a_session_key)
	{
	 	global $tpl;
	 	
	 	$_SESSION["$a_session_key"] = max($_SESSION["$a_session_key"],$this->search_cache->getResultPageNumber());
	 	
	 	if($_SESSION["$a_session_key"] == 1 and 
	 		(count($result->getResults()) < $result->getMaxHits()))
	 	{
	 		return true;
	 	}
	 	
		if($this->search_cache->getResultPageNumber() > 1)
		{
			$this->ctrl->setParameter($this,'page_number',$this->search_cache->getResultPageNumber() - 1);
/*			$this->tpl->setCurrentBlock('prev');
			$this->tpl->setVariable('PREV_LINK',$this->ctrl->getLinkTarget($this,'performSearch'));
			$this->tpl->setVariable('TXT_PREV',$this->lng->txt('search_page_prev'));
			$this->tpl->parseCurrentBlock();
*/
			$this->prev_link = $this->ctrl->getLinkTarget($this,'performSearch');
		}
		for($i = 1;$i <= $_SESSION["$a_session_key"];$i++)
		{
			if($i == $this->search_cache->getResultPageNumber())
			{
/*				$this->tpl->setCurrentBlock('pages_link');
				$this->tpl->setVariable('NUMBER',$i);
				$this->tpl->parseCurrentBlock();
*/
				continue;
			}
			
			$this->ctrl->setParameter($this,'page_number',$i);
			$link = '<a href="'.$this->ctrl->getLinkTarget($this,'performSearch').'" /a>'.$i.'</a> ';
/*			$this->tpl->setCurrentBlock('pages_link');
			$this->tpl->setVariable('NUMBER',$link);
			$this->tpl->parseCurrentBlock();
*/
		}
		

		if(count($result->getResults()) >= $result->getMaxHits())
		{
			$this->ctrl->setParameter($this,'page_number',$this->search_cache->getResultPageNumber() + 1);
/*			$this->tpl->setCurrentBlock('next');
			$this->tpl->setVariable('NEXT_LINK',$this->ctrl->getLinkTarget($this,'performSearch'));
		 	$this->tpl->setVariable('TXT_NEXT',$this->lng->txt('search_page_next'));
		 	$this->tpl->parseCurrentBlock();
*/
$this->next_link = $this->ctrl->getLinkTarget($this,'performSearch');
		}

/*		$this->tpl->setCurrentBlock('prev_next');
	 	$this->tpl->setVariable('SEARCH_PAGE',$this->lng->txt('search_page'));
	 	$this->tpl->parseCurrentBlock();
*/
	 	
	 	$this->ctrl->clearParameters($this);
	}
	
	/**
	 * Build path for search area
	 * @return
	 */
	protected function buildSearchAreaPath($a_root_node)
	{
		global $tree;

		$path_arr = $tree->getPathFull($a_root_node,ROOT_FOLDER_ID);
		$counter = 0;
		foreach($path_arr as $data)
		{
			if($counter++)
			{
				$path .= " > ";
				$path .= $data['title'];
			}
			else
			{
				$path .= $this->lng->txt('repository');
			}
			
		}
		return $path;
	}
	
	/**
	* Data resource for autoComplete
	*/
	function autoComplete()
	{
		$q = $_REQUEST["query"];
		include_once("./Services/Search/classes/class.ilSearchAutoComplete.php");
		$list = ilSearchAutoComplete::getList($q);
		echo $list;
		exit;
	}

}
?>
