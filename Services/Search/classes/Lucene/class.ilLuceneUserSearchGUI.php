<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilSearchSettings.php';
include_once './Services/Search/classes/class.ilSearchBaseGUI.php';
include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';


/** 
* @classDescription GUI for  Lucene user search
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilLuceneUserSearchGUI: ilSearchController
* 
* @ingroup ServicesSearch
*/
class ilLuceneUserSearchGUI extends ilSearchBaseGUI
{
	protected $ilTabs;
	
	/**
	 * Constructor 
	 */
	public function __construct()
	{
		global $ilTabs;
		
		$this->tabs_gui = $ilTabs;
		parent::__construct();
		$this->initUserSearchCache();
		
	}
	
	/**
	 * Execute Command 
	 */
	public function executeCommand()
	{
		global $ilBench, $ilCtrl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		switch($next_class)
		{
			default:
				$this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_LUCENE);
				if(!$cmd)
				{
					$cmd = "showSavedResults";
				}
				$this->handleCommand($cmd);
				break;
		}
		return true;
	}
	
	/**
	 * Add admin panel command
	 */
	public function prepareOutput()
	{
		parent::prepareOutput();
		$this->getTabs();
		return true;
		
		global $ilAccess, $ilSetting;
		global $ilUser;

		if($_SESSION['il_cont_admin_panel'])
		{
			$GLOBALS["tpl"]->setAdminViewButton(
				$this->ctrl->getLinkTarget($this, "disableAdministrationPanel"),
				$this->lng->txt("basic_commands"));
			
			$GLOBALS["tpl"]->addAdminPanelCommand("delete",
				$this->lng->txt("delete_selected_items"));
			
			if(!$_SESSION["clipboard"])
			{
				$GLOBALS["tpl"]->addAdminPanelCommand("cut",
					$this->lng->txt("move_selected_items"));

				$GLOBALS["tpl"]->addAdminPanelCommand("link",
					$this->lng->txt("link_selected_items"));
			}
			else
			{
				$GLOBALS["tpl"]->addAdminPanelCommand("paste",
					$this->lng->txt("paste_clipboard_items"));
				$GLOBALS["tpl"]->addAdminPanelCommand("clear",
					$this->lng->txt("clear_clipboard"));
			}
		}
		elseif($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$GLOBALS["tpl"]->setAdminViewButton(
				$this->ctrl->getLinkTarget($this, "enableAdministrationPanel"),
				$this->lng->txt("all_commands"));
		}

		$this->ctrl->setParameter($this, "type", "");
		$this->ctrl->setParameter($this, "item_ref_id", "");
		$GLOBALS["tpl"]->setPageFormAction($this->ctrl->getFormAction($this));
		
	}
	
	/**
	 * Get type of search (details | fast)
	 * @todo rename
	 * Needed for base class search form
	 */
	protected function getType()
	{
		if(count($this->search_cache))
		{
			return ilSearchBaseGUI::SEARCH_DETAILS;
		}
		return ilSearchBaseGUI::SEARCH_FAST;
	}
	
	/**
	 * Needed for base class search form
	 * @todo rename
	 * @return type
	 */
	protected function getDetails()
	{
		return (array) $this->search_cache->getItemFilter();
	}
	
	/**
	 * Needed for base class search form
	 * @todo rename
	 * @return type
	 */
	protected function getMimeDetails()
	{
		return (array) $this->search_cache->getMimeFilter();
	}
	
	/**
	 * Search from main menu
	 */
	protected function remoteSearch()
	{
		$_POST['query'] = $_POST['queryString'];
		$this->search_cache->setRoot((int) $_POST['root_id']);
		$this->search_cache->setQuery(ilUtil::stripSlashes($_POST['queryString']));
		$this->search_cache->save();
		
		$this->search();
	}
	
	/**
	 * Show saved results 
	 * @return
	 */
	protected function showSavedResults()
	{
		global $ilUser,$ilBench;
		
		if(!strlen($this->search_cache->getQuery()))
		{
			$this->showSearchForm();
			return false;
		}

	}
	
	/**
	 * Search (button pressed) 
	 * @return
	 */
	protected function search()
	{
		if(!$this->form->checkInput())
		{
			$this->search_cache->deleteCachedEntries();
			// Reset details
			include_once './Services/Object/classes/class.ilSubItemListGUI.php';
			ilSubItemListGUI::resetDetails();
			$this->showSearchForm();
			return false;
		}
		
		unset($_SESSION['max_page']);
		$this->search_cache->deleteCachedEntries();
		
		// Reset details
		include_once './Services/Object/classes/class.ilSubItemListGUI.php';
		ilSubItemListGUI::resetDetails();
		
		$this->performSearch();
	}
	
	/**
	 * Perform search 
	 */
	protected function performSearch()
	{
		return true;
	}
	
	/**
	 * get tabs 
	 */
	protected function getTabs()
	{
		$this->tabs_gui->addTarget('search',$this->ctrl->getLinkTargetByClass('illucenesearchgui'));
		
		if(ilSearchSettings::getInstance()->isLuceneUserSearchEnabled())
		{
			$this->tabs_gui->addTarget('search_user',$this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
		}
		
		$fields = ilLuceneAdvancedSearchFields::getInstance(); 
		
		if(
			!ilSearchSettings::getInstance()->getHideAdvancedSearch() and
			$fields->getActiveFields())
		{
			$this->tabs_gui->addTarget('search_advanced',$this->ctrl->getLinkTargetByClass('illuceneadvancedsearchgui'));
		}
		
		$this->tabs_gui->setTabActive('search_user');
	}
	
	/**
	 * Init user search cache
	 *
	 * @access private
	 * 
	 */
	protected function initUserSearchCache()
	{
		global $ilUser;
		
		include_once('Services/Search/classes/class.ilUserSearchCache.php');
		$this->search_cache = ilUserSearchCache::_getInstance($ilUser->getId());
		$this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_DEFAULT);
		if((int) $_GET['page_number'])
		{
			$this->search_cache->setResultPageNumber((int) $_GET['page_number']);
		}
		if(isset($_POST['term']))
		{
			$this->search_cache->setQuery(ilUtil::stripSlashes($_POST['term']));
			if($_POST['item_filter_enabled'])
			{
				$filtered = array();
				foreach(ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions() as $type => $data)
				{
					if($_POST['filter_type'][$type])
					{
						$filtered[$type] = 1;
					}
				}
				$this->search_cache->setItemFilter($filtered);

				// Mime filter
				$mime = array();
				foreach(ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions() as $type => $data)
				{
					if($_POST['filter_type'][$type])
					{
						$mime[$type] = 1;
					}
				}
				$this->search_cache->setMimeFilter($mime);
			}
			else
			{
				// @todo: keep item filter settings
				$this->search_cache->setItemFilter(array());
				$this->search_cache->setMimeFilter(array());
			}
		}
	}
	
	/**
	* Put admin panel into template:
	* - creation selector
	* - admin view on/off button
	*/
	protected function fillAdminPanel()
	{
		global $lng;
		
		$adm_view_cmp = $adm_cmds = $creation_selector = $adm_view = false;

		// admin panel commands
		if ((count($this->admin_panel_commands) > 0))
		{
			foreach($this->admin_panel_commands as $cmd)
			{
				$this->tpl->setCurrentBlock("lucene_admin_panel_cmd");
				$this->tpl->setVariable("LUCENE_PANEL_CMD", $cmd["cmd"]);
				$this->tpl->setVariable("LUCENE_TXT_PANEL_CMD", $cmd["txt"]);
				$this->tpl->parseCurrentBlock();
			}

			$adm_cmds = true;
		}
		if ($adm_cmds)
		{
			$this->tpl->setCurrentBlock("lucene_adm_view_components");
			$this->tpl->setVariable("LUCENE_ADM_IMG_ARROW", ilUtil::getImagePath("arrow_upright.png"));
			$this->tpl->setVariable("LUCENE_ADM_ALT_ARROW", $lng->txt("actions"));
			$this->tpl->parseCurrentBlock();
			$adm_view_cmp = true;
		}
		
		// admin view button
		if (is_array($this->admin_view_button))
		{
			if (is_array($this->admin_view_button))
			{
				$this->tpl->setCurrentBlock("lucene_admin_button");
				$this->tpl->setVariable("LUCENE_ADMIN_MODE_LINK",
					$this->admin_view_button["link"]);
				$this->tpl->setVariable("LUCENE_TXT_ADMIN_MODE",
					$this->admin_view_button["txt"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("lucene_admin_view");
			$this->tpl->parseCurrentBlock();
			$adm_view = true;
		}
		
		// creation selector
		if (is_array($this->creation_selector))
		{
			$this->tpl->setCurrentBlock("lucene_add_commands");
			if ($adm_cmds)
			{
				$this->tpl->setVariable("LUCENE_ADD_COM_WIDTH", 'width="1"');
			}
			$this->tpl->setVariable("LUCENE_SELECT_OBJTYPE_REPOS",
				$this->creation_selector["options"]);
			$this->tpl->setVariable("LUCENE_BTN_NAME_REPOS",
				$this->creation_selector["command"]);
			$this->tpl->setVariable("LUCENE_TXT_ADD_REPOS",
				$this->creation_selector["txt"]);
			$this->tpl->parseCurrentBlock();
			$creation_selector = true;
		}
		if ($adm_view || $creation_selector)
		{
			$this->tpl->setCurrentBlock("lucene_adm_panel");
			if ($adm_view_cmp)
			{
				$this->tpl->setVariable("LUCENE_ADM_TBL_WIDTH", 'width:"100%";');
			}
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* Add a command to the admin panel
	*/
	protected function addAdminPanelCommand($a_cmd, $a_txt)
	{
		$this->admin_panel_commands[] =
			array("cmd" => $a_cmd, "txt" => $a_txt);
	}
	
	/**
	* Show admin view button
	*/
	protected function setAdminViewButton($a_link, $a_txt)
	{
		$this->admin_view_button =
			array("link" => $a_link, "txt" => $a_txt);
	}
	
	protected function setPageFormAction($a_action)
	{
		$this->page_form_action = $a_action;
	}
	
	/**
	 * Show search form
	 * @return boolean
	 */
	protected function showSearchForm()
	{
		global $ilCtrl, $lng;
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');

		// include js needed
		include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
		ilOverlayGUI::initJavascript();
		$this->tpl->addJavascript("./Services/Search/js/Search.js");

		$this->tpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this,'performSearch'));
		$this->tpl->setVariable("TERM", ilUtil::prepareFormOutput($this->search_cache->getQuery()));
		$this->tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
		$this->tpl->setVariable("TXT_OPTIONS", $lng->txt("options"));
		$this->tpl->setVariable("ARR_IMG", ilUtil::img(ilUtil::getImagePath("mm_down_arrow_dark.png")));
		$this->tpl->setVariable("TXT_COMBINATION", $lng->txt("search_term_combination"));
		$this->tpl->setVariable('TXT_COMBINATION_DEFAULT', ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ? $lng->txt('search_all_words') : $lng->txt('search_any_word'));
		$this->tpl->setVariable('TXT_TYPE_DEFAULT',$lng->txt("search_off"));
		$this->tpl->setVariable("TXT_AREA", $lng->txt("search_area"));
		$this->tpl->setVariable("TXT_FILTER_BY_TYPE", $lng->txt("search_filter_by_type"));
		
		$this->tpl->setVariable('FORM',$this->form->getHTML());
		
		// search area form
		$this->tpl->setVariable('SEARCH_AREA_FORM', $this->getSearchAreaForm()->getHTML());
		$this->tpl->setVariable("TXT_CHANGE", $lng->txt("change"));
		
		return true;
	}
}
?>