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
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';
/** 
* Lucene Search GUI
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilLuceneSearchGUI: ilSearchController
* 
* @ingroup ServicesSearch
*/
class ilLuceneSearchGUI extends ilSearchBaseGUI implements ilDesktopItemHandling
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
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * Interface methods
	 */
	 public function addToDesk()
	 {
	 	include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::addToDesktop();
	 	$this->showSavedResults();
	 }
	 
	 /**
	  * Remove from dektop  
	  */
	 public function removeFromDesk()
	 {
	 	include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::removeFromDesktop();
	 	$this->showSavedResults();
	 }
	
	/**
	 * Show saved results 
	 * @return
	 */
	protected function showSavedResults()
	{
		global $ilUser;
		
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
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		$presentation->setSearcher($searcher);
		
		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}
		
		// and finally add search form
		$this->initFormSearch();
		$this->tpl->setVariable('SEARCH_TABLE',$this->form->getHTML());
		$this->addPager($filter,'max_page');
		
	}
	
	/**
	 * Search (button pressed) 
	 * @return
	 */
	protected function search()
	{
		if(!strlen($this->search_cache->getQuery()))
		{
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
	 * Perform search 
	 */
	protected function performSearch()
	{
		global $ilUser,$ilBench;
		
		
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
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation($this);
		$presentation->setResults($filter->getResultIds());
		$presentation->setSearcher($searcher);
		if($presentation->render())
		{
			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}
		
		// and finally add search form
		$this->initFormSearch();
		$this->tpl->setVariable('SEARCH_TABLE',$this->form->getHTML());
		$this->addPager($filter,'max_page');
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
		$this->form->setTitle($this->lng->txt('search'));
		$this->form->addCommandButton('search',$this->lng->txt('search'));
		
		$term = new ilTextInputGUI($this->lng->txt('search_search_term'),'query');
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
	 * Prepare output 
	 */
	public function prepareOutput()
	{
		parent::prepareOutput();
		$this->getTabs();
	}
	
	/**
	 * get tabs 
	 */
	protected function getTabs()
	{
		$this->tabs_gui->addTarget('search',$this->ctrl->getLinkTarget($this));
		$this->tabs_gui->addTarget('search_advanced',$this->ctrl->getLinkTargetByClass('illuceneAdvancedSearchgui'));
		
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
	
}
?>