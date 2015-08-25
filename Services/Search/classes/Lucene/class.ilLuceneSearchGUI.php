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

include_once './Services/Search/classes/class.ilSearchSettings.php';
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
* @ilCtrl_Calls ilLuceneSearchGUI: ilPropertyFormGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilLuceneSearchGUI: ilObjRootFolderGUI, ilObjectCopyGUI
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
		global $ilBench, $ilCtrl;
		
		$ilBench->start('Lucene','0900_executeCommand');
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		switch($next_class)
		{
			case "ilpropertyformgui":
				/*$this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_LUCENE);
				$ilCtrl->setReturn($this, 'storeRoot');
				$ilCtrl->forwardCommand($this->form);*/
				$form = $this->getSearchAreaForm();
				$ilCtrl->setReturn($this, 'storeRoot');
				$ilCtrl->forwardCommand($form);
				break;
			
			case 'ilobjectcopygui':
				$this->ctrl->setReturn($this,'');
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$this->ctrl->forwardCommand($cp);
				break;
			
			default:
				$this->initStandardSearchForm(ilSearchBaseGUI::SEARCH_FORM_LUCENE);
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

		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
		$qp = new ilLuceneQueryParser($this->search_cache->getQuery());
		$qp->parse();
		$searcher = ilLuceneSearcher::getInstance($qp);
		$searcher->search();

		// Load saved results
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->loadFromDb();

		// Highlight
		$searcher->highlight($filter->getResultObjIds());
		
		include_once './Services/Search/classes/class.ilSearchResultPresentation.php';
		$presentation = new ilSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		
		$presentation->setSearcher($searcher);

		// TODO: other handling required
		$this->addPager($filter,'max_page');

		$presentation->setPreviousNext($this->prev_link, $this->next_link);
			
		$this->showSearchForm();	

		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML(true));
		}
		elseif(strlen($this->search_cache->getQuery()))
		{
			ilUtil::sendInfo(sprintf($this->lng->txt('search_no_match_hint'),$qp->getQuery()));
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
		global $ilUser,$ilBench;
		
		unset($_SESSION['vis_references']);

		$filter_query = '';
		if($this->search_cache->getItemFilter() and ilSearchSettings::getInstance()->isLuceneItemFilterEnabled())
		{
			$filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
			foreach((array) $this->search_cache->getItemFilter() as $obj => $value)
			{
				if(!$filter_query)
				{
					$filter_query .= '+( ';
				}
				else
				{
					$filter_query .= 'OR';
				}
				$filter_query .= (' '. (string) $filter_settings[$obj]['filter'].' ');
			}
			$filter_query .= ') ';
		}
		// begin-patch mime_filter
		$mime_query = '';
		if($this->search_cache->getMimeFilter() and ilSearchSettings::getInstance()->isLuceneMimeFilterEnabled())
		{
			$filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions();
			foreach($this->search_cache->getMimeFilter() as $mime => $value)
			{
				if(!$mime_query)
				{
					$mime_query .= '+( ';
				}
				else
				{
					$mime_query .= 'OR';
				}
				$mime_query .= (' '. (string) $filter_settings[$mime]['filter'].' ');
			}
			$mime_query .= ') ';
		}
		
		// begin-patch creation_date
		$cdate_query = $this->parseCreationFilter();
		
		
		
		$filter_query = $filter_query . ' '. $mime_query.' '.$cdate_query;
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
		
		$query = $this->search_cache->getQuery();
		if($query)
		{
			$query = ' +('.$query.')';
		}
		$qp = new ilLuceneQueryParser($filter_query.$query);
		$qp->parse();
		$searcher = ilLuceneSearcher::getInstance($qp);
		$searcher->search();
		
		// Filter results
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		include_once './Services/Search/classes/Lucene/class.ilLucenePathFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->addFilter(new ilLucenePathFilter($this->search_cache->getRoot()));
		$filter->setCandidates($searcher->getResult());
		$filter->filter();
				
		if($filter->getResultObjIds()) {
			$searcher->highlight($filter->getResultObjIds());
		}

		// Show results
		$this->showSearchForm();

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
			ilUtil::sendInfo(sprintf($this->lng->txt('search_no_match_hint'),$this->search_cache->getQuery()));
		}
	}
	
	/**
	 * Store new root node
	 */
	protected function storeRoot()
	{
		$form = $this->getSearchAreaForm();

		$this->root_node = $form->getItemByPostVar('area')->getValue();
		$this->search_cache->setRoot($this->root_node);
		$this->search_cache->save();
		$this->search_cache->deleteCachedEntries();

		include_once './Services/Object/classes/class.ilSubItemListGUI.php';
		ilSubItemListGUI::resetDetails();

		$this->performSearch();
	}
	
	/**
	 * get tabs 
	 */
	protected function getTabs()
	{
		global $ilHelp;

		$ilHelp->setScreenIdComponent("src_luc");

		$this->tabs_gui->addTarget('search',$this->ctrl->getLinkTarget($this));
		
		if(ilSearchSettings::getInstance()->isLuceneUserSearchEnabled())
		{
			$this->tabs_gui->addTarget('search_user',$this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
		}
		
		if($this->fields->getActiveFields() && !ilSearchSettings::getInstance()->getHideAdvancedSearch())
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
			$this->search_cache->setCreationFilter($this->loadCreationFilter());
			if(!$_POST['item_filter_enabled'])
			{
				// @todo: keep item filter settings
				$this->search_cache->setItemFilter(array());
				$this->search_cache->setMimeFilter(array());
			}
			if(!$_POST['screation'])
			{
				$this->search_cache->setCreationFilter(array());
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

		include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");

		$this->tpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this,'performSearch'));
		$this->tpl->setVariable("TERM", ilUtil::prepareFormOutput($this->search_cache->getQuery()));
		include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
		$btn = ilSubmitButton::getInstance();
		$btn->setCommand("performSearch");
		$btn->setCaption("search");
		$this->tpl->setVariable("SUBMIT_BTN",$btn->render());
		$this->tpl->setVariable("TXT_OPTIONS", $lng->txt("options"));
		$this->tpl->setVariable("ARR_IMG", ilGlyphGUI::get(ilGlyphGUI::CARET));
		$this->tpl->setVariable("TXT_COMBINATION", $lng->txt("search_term_combination"));
		$this->tpl->setVariable('TXT_COMBINATION_DEFAULT', ilSearchSettings::getInstance()->getDefaultOperator() == ilSearchSettings::OPERATOR_AND ? $lng->txt('search_all_words') : $lng->txt('search_any_word'));
		$this->tpl->setVariable("TXT_AREA", $lng->txt("search_area"));

		if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled())
		{
			$this->tpl->setCurrentBlock("type_sel");
			$this->tpl->setVariable('TXT_TYPE_DEFAULT',$lng->txt("search_off"));
			$this->tpl->setVariable("ARR_IMGT", ilGlyphGUI::get(ilGlyphGUI::CARET));
			$this->tpl->setVariable("TXT_FILTER_BY_TYPE", $lng->txt("search_filter_by_type"));
			$this->tpl->setVariable('FORM',$this->form->getHTML());
			$this->tpl->parseCurrentBlock();
		}

		// search area form
		$this->tpl->setVariable('SEARCH_AREA_FORM', $this->getSearchAreaForm()->getHTML());
		$this->tpl->setVariable("TXT_CHANGE", $lng->txt("change"));
		
		// begin-patch creation_date
		$this->tpl->setVariable('TXT_FILTER_BY_CDATE',$this->lng->txt('search_filter_cd'));
		$this->tpl->setVariable('TXT_CD_OFF',$this->lng->txt('search_off'));
		$this->tpl->setVariable('FORM_CD',$this->getCreationDateForm()->getHTML());
		// end-patch creation_date
		
		
		return true;
	}
	
	
	// begin-patch creation_date
	protected function getCreationDateForm()
	{
		$options = $this->search_cache->getCreationFilter();
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setOpenTag(false);
		$form->setCloseTag(false);
		
		$enabled = new ilCheckboxInputGUI($this->lng->txt('search_filter_cd'),'screation');
		$enabled->setValue(1);
		$enabled->setChecked((bool) $options['enabled']);
		$form->addItem($enabled);
		
		$group = new ilRadioGroupInputGUI($this->lng->txt('search_filter_cd'), 'screation_type');
		$group->setValue((int) $options['type']);
		$group->addOption($opt1 = new ilRadioOption($this->lng->txt('search_filter_date'), 1));
		
		$limit_sel = new ilSelectInputGUI('','screation_ontype');
		$limit_sel->setValue($options['ontype']);
		$limit_sel->setOptions(
				array(
					1 => $this->lng->txt('search_created_after'),
					2 => $this->lng->txt('search_created_before'),
					3 => $this->lng->txt('search_created_on')
			)
		);
		$opt1->addSubItem($limit_sel);
		
		
		if($options['date'])
		{
			$now = new ilDateTime($options['date'],IL_CAL_UNIX);
		}
		else
		{
			$now = new ilDateTime(time(),IL_CAL_UNIX);
			$now->increment(IL_CAL_MONTH,-3);
		}
		$ds = new ilDateTimeInputGUI('','screation_date');
		$ds->setDate($now);
		$opt1->addSubItem($ds);
		
		$group->addOption($opt2 = new ilRadioOption($this->lng->txt('search_filter_duration'), 2));
		
		$duration = new ilDurationInputGUI($this->lng->txt('search_filter_duration'), 'screation_duration');
		$duration->setMonths((int) $options['duration']['MM']);
		$duration->setDays((int) $options['duration']['dd']);
		$duration->setShowMonths(true);
		$duration->setShowDays(true);
		$duration->setShowHours(false);
		$duration->setShowMinutes(false);
		$duration->setTitle($this->lng->txt('search_newer_than'));
		$opt2->addSubItem($duration);
		
		$enabled->addSubItem($group);
				
		$form->setFormAction($GLOBALS['ilCtrl']->getFormAction($this,'performSearch'));
		
		return $form;
				
	}
	
	protected function loadCreationFilter()
	{
		$form = $this->getCreationDateForm();
		$options = array();
		if($form->checkInput())
		{
			$options['enabled'] = $form->getInput('screation');
			$options['type'] = $form->getInput('screation_type');
			$options['ontype'] = $form->getInput('screation_ontype');
			$options['date'] = $form->getItemByPostVar('screation_date')->getDate()->get(IL_CAL_UNIX);
			$options['duration'] = $form->getInput('screation_duration');
		}
		return $options;
	}
	
	protected function parseCreationFilter()
	{
		
		$options = $this->search_cache->getCreationFilter();
		
		if(!$options['enabled'])
		{
			return '';
		}
		switch($options['type'])
		{
			case 1:
				
				$limit = new ilDate($options['date'],IL_CAL_UNIX);
				
				switch($options['ontype'])
				{
					case 1:
						// after
						return '+(cdate: ['.$limit->get(IL_CAL_DATE).' TO * ]) ';
						
					case 2:
						// before
						return '+(cdate: [* TO '.$limit->get(IL_CAL_DATE).']) ';
						
					case 3:
						// on
						return '+(cdate: '.$limit->get(IL_CAL_DATE).') ';
						
				}
				
				
				
			case 2;
				$start = new ilDate(time(),IL_CAL_UNIX);
				$start->increment(IL_CAL_MONTH, -1 * (int) $options['duration']['MM']);
				$start->increment(IL_CAL_DAY, -1 * (int) $options['duration']['dd']);
				
				$now = new ilDate(time(),IL_CAL_UNIX);
				return '+(cdate: ['.$start->get(IL_CAL_DATE).' TO '.$now->get(IL_CAL_DATE).']) ';
		}
		
		
		return '+(cdate: [2010-11-01 TO 2013-11-30])';
	}
	// end-patch creation_date
}
?>