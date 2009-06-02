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
* @classDescription GUI for simple Lucene search
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilLuceneSearchGUI: ilSearchController
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjRootFolderGUI
* 
* @ingroup ServicesSearch
*/
class ilLuceneSearchGUI extends ilSearchBaseGUI
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
		$this->fields = ilLuceneAdvancedSearchFields::getInstance(); 
		$this->initUserSearchCache();
		
	}
	
	/**
	 * Execute Command 
	 */
	public function executeCommand()
	{
		global $ilBench;
		
		$ilBench->start('Lucene','0900_executeCommand');
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "showSavedResults";
				}
				$this->handleCommand($cmd);
				break;
		}
		$ilBench->stop('Lucene','0900_executeCommand');
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
	 * Search from main menu
	 */
	protected function remoteSearch()
	{
		$_POST['query'] = $_POST['queryString'];
		$this->search_cache->setRoot((int) $_POST['root_id']);
		$this->search_cache->setQuery(ilUtil::stripSlashes($_POST['queryString']));
		$this->search_cache->save();
		
		$this->initFormSearch();
		$this->search();
	}
	
	/**
	 * Show saved results 
	 * @return
	 */
	protected function showSavedResults()
	{
		global $ilUser,$ilBench;
		
		$ilBench->start('Lucene','1000_savedResults');
		$ilBench->start('Lucene','1000_qp');
		
		if(!strlen($this->search_cache->getQuery()))
		{
			$this->showSearchForm();
			return false;
		}

		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
		$qp = new ilLuceneQueryParser($this->search_cache->getQuery());
		$qp->parse();
		$searcher = ilLuceneSearcher::getInstance($qp);
		$searcher->search();

		$ilBench->stop('Lucene','1000_qp');

		$ilBench->start('Lucene','1100_lr');
		// Load saved results
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->loadFromDb();
		$ilBench->stop('Lucene','1100_lr');

		// Highlight
		$ilBench->start('Lucene','1200_hi');
		$searcher->highlight($filter->getResultObjIds());
		$ilBench->stop('Lucene','1200_hi');
		
		$ilBench->start('Lucene','1300_pr');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		
		$presentation->setSearcher($searcher);
		$presentation->setPreviousNext($this->prev_link, $this->next_link);
		$ilBench->stop('Lucene','1300_pr');
		
		$ilBench->start('Lucene','1400_re');
			
		$this->showSearchForm();	

		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML(true));
		}
		elseif(strlen($this->search_cache->getQuery()))
		{
			ilUtil::sendInfo(sprintf($this->lng->txt('search_no_match_hint'),$qp->getQuery()));
		}
		$ilBench->stop('Lucene','1400_re');
		
		$ilBench->start('Lucene','1500_fo');
		$this->addPager($filter,'max_page');
		$ilBench->stop('Lucene','1500_fo');
		$ilBench->stop('Lucene','1000_savedResults');
	}
	
	/**
	 * Search (button pressed) 
	 * @return
	 */
	protected function search()
	{
		$this->initFormSearch();
		
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
		global $ilUser,$ilBench;
		
		unset($_SESSION['vis_references']);

		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
		$qp = new ilLuceneQueryParser($this->search_cache->getQuery());
		$qp->parse();
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
		$this->showSearchForm();

		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		$presentation->setSearcher($searcher);
		$presentation->setPreviousNext($this->prev_link, $this->next_link);

		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML(true));
		}
		else
		{
			ilUtil::sendInfo(sprintf($this->lng->txt('search_no_match_hint'),$this->search_cache->getQuery()));
		}
		
		$this->addPager($filter,'max_page');
		
		if($filter->getResultIds())
		{
			#$this->fillAdminPanel();
		}
	}
	
	/**
	 * Show search form  
	 */
	protected function initFormSearch()
	{
		global $tree;
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		if(is_object($this->form))
		{
			return true;
		}
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'search'));
		$this->form->setTitle($this->lng->txt('search'));
		$this->form->addCommandButton('search',$this->lng->txt('search'));
		
		include_once './Services/Search/classes/Form/class.ilLuceneQueryInputGUI.php';
		$term = new ilLuceneQueryInputGUI($this->lng->txt('search_search_term'),'query');
		$term->setValue($this->search_cache->getQuery());
		$term->setSize(40);
		$term->setMaxLength(255);
		$term->setRequired(true);
		$this->form->addItem($term);
		
		
		$path = new ilCustomInputGUI($this->lng->txt('search_area'),'root');
		$tpl = new ilTemplate('tpl.root_selection.html',true,true,'Services/Search');
		switch($this->search_cache->getRoot())
		{
			default:
				$pathIds = $tree->getPathId($this->search_cache->getRoot(),ROOT_FOLDER_ID);
				$counter = 0;
				foreach($pathIds as $ref_id)
				{
					if($counter++) {
						$tpl->touchBlock('path_separator');
					}
					if(($counter % 3) == 0) {
						$tpl->touchBlock('line_break');
					}
					if($ref_id == ROOT_FOLDER_ID) {
						$title = $this->lng->txt('search_in_magazin');
					}
					else {
						$title = ilUtil::shortenText(ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)),30,true);
					}
					$this->ctrl->setParameter($this,'root_id',$ref_id);
					$tpl->setCurrentBlock('item');
					$tpl->setVariable('ITEM_LINK',$this->ctrl->getLinkTarget($this,'selectRoot'));
					$tpl->setVariable('NAME_WITH_DOTS',$title);
					$tpl->parseCurrentBlock();
				}
				$tpl->setVariable('LINK_SELECT',$this->ctrl->getLinkTarget($this,'chooseRoot'));
				$tpl->setVariable('TXT_CHANGE',$this->lng->txt('change'));
				break;
				
		}
		$path->setHTML($tpl->get());
		$this->form->addItem($path);
		
		
		/*
		$path = new ilRadioGroupInputGUI($this->lng->txt('search_area'),'root');
		$path->setValue($this->search_cache->enabledSearchArea() ?
			$this->search_cache->getRoot() : 
			ROOT_FOLDER_ID);
		$path->setRequired(true);
		
			// In repository
			$repos = new ilRadioOption($this->lng->txt('search_in_magazin'),1);
			$path->addOption($repos);
			
			// Sub area
			$path_link = $this->buildSearchAreaPath($this->search_cache->getRoot());
			$path_link .= (' <a href="'.$this->ctrl->getLinkTarget($this,'chooseRoot').'">['.$this->lng->txt('change').']</a>');
			
			$sub_area = new ilRadioOption($path_link,$this->search_cache->getRoot());
			$path->addOption($sub_area);
			
		$this->form->addItem($path);
		*/
		return true;
	}
	
	/**
	 * Show root node selection 
	 * @param
	 * @return
	 */
	protected function chooseRoot()
	{
		global $tree;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.search_root_selector.html','Services/Search');

		include_once 'Services/Search/classes/class.ilSearchRootSelector.php';

		ilUtil::sendInfo($this->lng->txt('search_area_info'));

		$exp = new ilSearchRootSelector($this->ctrl->getLinkTarget($this,'chooseRoot'));
		$exp->setTargetClass(get_class($this));
		$exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'chooseRoot'));

		// build html-output
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());
	
	}
	
	/**
	 * Select root 
	 * @return
	 */
	protected function selectRoot()
	{
		$this->search_cache->setRoot((int) $_GET['root_id']);
		$this->search_cache->save();
		$this->search();
	}
	
	/**
	 * get tabs 
	 */
	protected function getTabs()
	{
		$this->tabs_gui->addTarget('search',$this->ctrl->getLinkTarget($this));
		if($this->fields->getActiveFields())
		{
			$this->tabs_gui->addTarget('search_advanced',$this->ctrl->getLinkTargetByClass('illuceneAdvancedSearchgui'));
		}
		
		$this->tabs_gui->setTabActive('search');
		
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
		if(isset($_POST['query']))
		{
			$this->search_cache->setQuery(ilUtil::stripSlashes($_POST['query']));
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
			$this->tpl->setVariable("LUCENE_ADM_IMG_ARROW", ilUtil::getImagePath("arrow_upright.gif"));
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
	
	protected function showSearchForm()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');
		$this->initFormSearch();
		$this->tpl->setVariable('SEARCH_TABLE',$this->form->getHTML());
		return true;
	}
}
?>