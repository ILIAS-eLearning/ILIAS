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
	 * Show saved results 
	 * @return
	 */
	public function showSavedResults()
	{
		global $ilUser;
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');
		
		$this->initFormSearch();
		$this->tpl->setVariable('SEARCH_TABLE',$this->form->getHTML());
		
		// Load saved results
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->loadFromDb();
		
		// Highlight
		$parser = $this->highlight($filter);

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation();
		$presentation->setResults($filter->getResultIds());
		$presentation->setHighlighter($parser);
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
	 * Perform search 
	 */
	protected function performSearch()
	{
		global $ilUser,$ilBench;
		
		if(!strlen($this->search_cache->getQuery()))
		{
			ilUtil::sendInfo($this->lng->txt('msg_no_search_string'));
			$this->showSavedResults();
			return false;
		}

		// Search in combined index
		$ilBench->start('Lucene','RPCAdapter');
		include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';
		$adapter = new ilLuceneRPCAdapter();
		$adapter->setQueryString($this->search_cache->getQuery());
		$adapter->setMode('search');
		$res = $adapter->send();
		$ilBench->stop('Lucene','RPCAdapter');
		// TODO: Error handling
		
		// Filter results
		$ilBench->start('Lucene','ResultFilter');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultFilter.php';
		$filter = ilLuceneSearchResultFilter::getInstance($ilUser->getId());
		$filter->setCandidates($res);
		$filter->filter();
		$ilBench->stop('Lucene','ResultFilter');
				
		$parser = $this->highlight($filter);

		// Show results
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_search.html','Services/Search');
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation();
		$presentation->setResults($filter->getResultIds());
		$presentation->setHighlighter($parser);
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
	 * Highlight results 
	 * @param object Lucene search result filter
	 * @return
	 */
	protected function highlight($filter)
	{
		global $ilBench;

		if($objIds = $filter->getResultObjIds())
		{
			// Search in combined index
			$ilBench->start('Lucene','RPCAdapterHighlight');
			include_once './Services/Search/classes/Lucene/class.ilLuceneRPCAdapter.php';
			$adapter = new ilLuceneRPCAdapter();
			$adapter->setQueryString($this->search_cache->getQuery());
			$adapter->setMode('highlight');
			$adapter->setResultIds($filter->getResultObjIds());
			$res = $adapter->send();
			
			include_once './Services/Search/classes/Lucene/class.ilLuceneHighlighterResultParser.php';
			$parser = new ilLuceneHighlighterResultParser();
			$parser->setResultString($res);
			$parser->parse();
			$ilBench->stop('Lucene','RPCAdapterHighlight');
			// TODO: Error handling

			return $parser;
		}
		return null;
	}
	
	
	/**
	 * Show search form  
	 */
	protected function initFormSearch()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'search'));
		$this->form->setTitle($this->lng->txt('search'));
		$this->form->addCommandButton('performSearch',$this->lng->txt('search'));
		
		$term = new ilTextInputGUI($this->lng->txt('search_search_term'),'query');
		$term->setValue($this->search_cache->getQuery());
		$term->setSize(40);
		$term->setMaxLength(255);
		$term->setRequired(true);
		$this->form->addItem($term);
		
		return true;
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
		else
		{
			$this->search_cache->delete();
			unset($_SESSION['max_page']);
		}
		if($_POST['query'])
		{
			$this->search_cache->setQuery(ilUtil::stripSlashes($_POST['query']));
		}
	}
	
}
?>