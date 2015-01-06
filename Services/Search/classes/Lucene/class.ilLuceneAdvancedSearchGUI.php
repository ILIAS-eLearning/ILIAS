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

include_once './Services/Search/classes/class.ilSearchBaseGUI.php';
include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';
include_once './Services/Administration/interfaces/interface.ilAdministrationCommandHandling.php';

/** 
* Meta Data search GUI
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilLuceneAdvancedSearchGUI: ilSearchController
* @ilCtrl_Calls ilLuceneAdvancedSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilLuceneAdvancedSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilLuceneAdvancedSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchGUI extends ilSearchBaseGUI
{
	protected $ilTabs;
	
	protected $fields = null;
	
	/**
	 * Constructor 
	 */
	public function __construct()
	{
		global $ilTabs;
		
		$this->tabs_gui = $ilTabs;
		parent::__construct();
		
		$this->fields = ilLuceneAdvancedSearchFields::getInstance(); 
		$this->initUserSearchCache();
	}
	
	/**
	 * Execute Command 
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		switch($next_class)
		{
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$this->ctrl->forwardCommand($cp);
				break;
			
			
			default:
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
	 * Show saved results 
	 * @return
	 */
	public function showSavedResults()
	{
		global $ilUser,$ilBench;
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedQueryParser.php';
		$qp = new ilLuceneAdvancedQueryParser($this->search_cache->getQuery());
		$qp->parse();
		$searcher = ilLuceneSearcher::getInstance($qp);
		$searcher->search();

		// Load saved results
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->loadFromDb();

		// Highlight
		if($filter->getResultObjIds()) {
			$searcher->highlight($filter->getResultObjIds());
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_adv_search.html','Services/Search');
		include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
		$presentation = new ilSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		$presentation->setSearcher($searcher);


		// TODO: other handling required
		$this->addPager($filter,'max_page');
		$presentation->setPreviousNext($this->prev_link, $this->next_link);
		
		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML(true));
		}
		elseif(strlen(trim($qp->getQuery())))
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}
		
		// and finally add search form
		$this->initFormSearch();
		$this->tpl->setVariable('SEARCH_TABLE',$this->form->getHTML());
		
		if($filter->getResultIds())
		{	
			$this->fillAdminPanel();
		}
	}
	
	/**
	 * Show search form  
	 */
	protected function initFormSearch()
	{
		global $tree;
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'search'));
		$this->form->setTitle($this->lng->txt('search_advanced'));
		$this->form->addCommandButton('search',$this->lng->txt('search'));
		$this->form->addCommandButton('reset',$this->lng->txt('reset'));
		
		foreach($this->fields->getActiveSections() as $definition)
		{
			if($definition['name'] != 'default')
			{
				$section = new ilFormSectionHeaderGUI();
				$section->setTitle($definition['name']);
				$this->form->addItem($section);
			}
			
			foreach($definition['fields'] as $field_name)
			{
				if(is_object($element = $this->fields->getFormElement($this->search_cache->getQuery(),$field_name,$this->form)))
				{
					$this->form->addItem($element);
				}
			}
		}
		return true;
	}
	
	/**
	 * Search from main menu
	 */
	protected function remoteSearch()
	{
		$this->search_cache->setRoot((int) $_POST['root_id']);
		$this->search_cache->setQuery(array('lom_content' => ilUtil::stripSlashes($_POST['queryString'])));
		$this->search_cache->save();
		$this->search();
	}
	
	protected function search()
	{
		if(!is_array($this->search_cache->getQuery()))
		{
			// TOD: handle empty advances search
			ilUtil::sendInfo($this->lng->txt('msg_no_search_string'));
			$this->showSavedResults();
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
	 * Reset search form
	 */
	protected function reset()
	{
		$this->search_cache->setQuery(array());
		$this->search_cache->save();
		$this->showSavedResults();
	}
	
	/**
	 * Perform search 
	 */
	protected function performSearch()
	{
		global $ilUser,$ilBench;
		
		unset($_SESSION['vis_references']);
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedQueryParser.php';
		$qp = new ilLuceneAdvancedQueryParser($this->search_cache->getQuery());
		$qp->parse();
		if(!strlen(trim($qp->getQuery())))
		{		
			ilUtil::sendInfo($this->lng->txt('msg_no_search_string'));
			$this->showSavedResults();
			return false;
		}
		
		$searcher = ilLuceneSearcher::getInstance($qp);
		$searcher->search();
		
		// Filter results
		$ilBench->start('Lucene','ResultFilter');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		include_once './Services/Search/classes/Lucene/class.ilLucenePathFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->addFilter(new ilLucenePathFilter($this->search_cache->getRoot()));
		$filter->setCandidates($searcher->getResult());
		$filter->filter();
		$ilBench->stop('Lucene','ResultFilter');
				
		if($filter->getResultObjIds()) {
			$searcher->highlight($filter->getResultObjIds());
		}

		// Show results
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_adv_search.html','Services/Search');
		include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
		$presentation = new ilSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		$presentation->setSearcher($searcher);

		// TODO: other handling required
		$ilBench->start('Lucene','1500_fo');
		$this->addPager($filter,'max_page');
		$ilBench->stop('Lucene','1500_fo');
		$presentation->setPreviousNext($this->prev_link, $this->next_link);

		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML(true));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}
		
		// and finally add search form
		$this->initFormSearch();
		$this->tpl->setVariable('SEARCH_TABLE',$this->form->getHTML());
		
		if($filter->getResultIds())
		{
			$this->fillAdminPanel();
		}
	}
	
	/**
	 * Add admin panel command
	 */
	public function prepareOutput()
	{
		parent::prepareOutput();
		$this->getTabs();
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
		
		if(
			!ilSearchSettings::getInstance()->getHideAdvancedSearch() and
			$this->fields->getActiveFields())
		{
			$this->tabs_gui->addTarget('search_advanced',$this->ctrl->getLinkTarget($this));
		}
		
		$this->tabs_gui->setTabActive('search_advanced');
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
		$this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_ADVANCED);
		if((int) $_GET['page_number'])
		{
			$this->search_cache->setResultPageNumber((int) $_GET['page_number']);
		}
		if(isset($_POST['query']))
		{
			$this->search_cache->setQuery($_POST['query']);
		}
	}
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
			$this->tpl->setVariable("LUCENE_ADM_IMG_ARROW", ilUtil::getImagePath("arrow_upright.svg"));
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
}
?>
