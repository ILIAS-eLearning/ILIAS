<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';

/**
* Class ilShopAdvancedSearchGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ServicesPayment
*/
class ilShopAdvancedSearchGUI extends ilShopBaseGUI
{
	const SEARCH_OR = 'or';
	const SEARCH_AND = 'and';
	
	private $string = '';
	private $combination = ''; 
	private $details = array();
	private $topic_id = 0;
	private $sort_type_topics = '';
	private $sort_direction_topics = '';
	private $sort_field = '';
	private $sort_direction = '';
	
	public function __construct()
	{
		parent::__construct();

		if($this->cmd == 'setFilter')
		{
			// set filter
			$this->setCombination($_SESSION['shop_advanced_search']['combination']);
			$this->setString($_SESSION['shop_advanced_search']['string']);
			$this->setDetails($_SESSION['shop_advanced_search']['details']);
			$this->setTopicId($_SESSION['shop_advanced_search']['topic']);

			// set sorting
			$this->setSortingTypeTopics($_SESSION['shop_advanced_search']['order_topics_sorting_type']);
			$this->setSortingDirectionTopics($_SESSION['shop_advanced_search']['shop_topics_sorting_direction']);

			$this->setSortField($_SESSION['shop_advanced_search']['shop_order_field']);
			$this->setSortDirection($_SESSION['shop_advanced_search']['shop_order_direction']);
		}
	}
	
	public function setSorting()
	{
		$this->setSortingTypeTopics($_POST['topics_sorting_type']);
		$this->setSortingDirectionTopics($_POST['topics_sorting_direction']);
		$this->setSortField($_POST['order_field']);
		$this->setSortDirection($_POST['order_direction']);
		
		$this->performSearch();
		
		return true;
	}
	
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		if(isset($_POST['cmd']) && $_POST['cmd'] == 'setFilter')
		{
			$this->cmd = 'setFilter';
		}
		else
		{
			$this->cmd = $this->ctrl->getCmd();
		}

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'performSearch';
				}		
				$this->prepareOutput();		
				$this->$cmd();
				
				break;
		}
		
		return true;
	}	
	
	public function setFilter()
	{
		if(isset($_POST['search_combination']))
		{
			$this->setCombination($_POST['search_combination']);
		}
		if(isset($_POST['search_string'])) 
		{
			$this->setString($_POST['search_string']);
			
		}
		if(isset($_POST['search_details'])) 
		{
			$this->setDetails($_POST['search_details']);
		}
		if(isset($_POST['search_topic'])) 
		{
			$this->setTopicId($_POST['search_topic']);
		}
		$this->performSearch();
	}


	public function resetFilter()
	{
		unset($_SESSION['shop_advanced_search']);
		return $this->showForm();
	}
	
	public function performSearch()
	{		
		if(!$this->getDetails())
		{
			if(method_exists($this, $this->ctrl->getCmd()))
				ilUtil::sendInfo($this->lng->txt('search_choose_object_type'));
			$this->showForm(ilShopSearchResult::_getInstance(SHOP_ADVANCED_SEARCH));

			return false;
		}
		
		// Step 1: parse query string
		if(!is_object($query_parser = $this->parseQueryString()))
		{
			ilUtil::sendInfo($query_parser);
			$this->showForm(ilShopSearchResult::_getInstance(SHOP_ADVANCED_SEARCH));
			
			return false;
		}
		
		// Step 2: perform object search. Get an ObjectSearch object via factory. Depends on fulltext or like search type.
		$result = $this->searchObjects($query_parser);

		// Step 3:
		$result->filter(ROOT_FOLDER_ID, $query_parser->getCombination() == 'and');
		$result->save();
		
		if(!count($result->getResults()))
		{
			ilUtil::sendInfo($this->lng->txt('payment_shop_not_objects_found'));
		}
				
		$this->showForm($result);		

		return true;
	}
	
	private function parseQueryString()
	{
		include_once 'Services/Search/classes/class.ilQueryParser.php';

		$query_parser = new ilQueryParser(ilUtil::stripSlashes($this->getString()));
		$query_parser->setCombination($this->getCombination());
		$query_parser->parse();

		if (!$query_parser->validate())
		{
			return $query_parser->getMessage();
		}
		
		return $query_parser;
	}
	
	private function searchObjects($query_parser)
	{
		// create new search result object and assign the sorted topics
		$oSearchResult = ilShopSearchResult::_getInstance(SHOP_ADVANCED_SEARCH);
		if((bool)$this->settings->get('topics_allow_custom_sorting'))
		{		
			ilShopTopics::_getInstance()->setIdFilter((int)$this->getTopicId());
			ilShopTopics::_getInstance()->enableCustomSorting(true);
			ilShopTopics::_getInstance()->setSortingType((int)$this->getSortingTypeTopics());
			ilShopTopics::_getInstance()->setSortingDirection(strtoupper($this->getSortingDirectionTopics()));
			ilShopTopics::_getInstance()->read();
		}
		else
		{
			ilShopTopics::_getInstance()->setIdFilter((int)$this->getTopicId());
			ilShopTopics::_getInstance()->enableCustomSorting(false);
			ilShopTopics::_getInstance()->setSortingType((int)$this->settings->get('topics_sorting_type'));
			ilShopTopics::_getInstance()->setSortingDirection(strtoupper($this->settings->get('topics_sorting_direction')));
			ilShopTopics::_getInstance()->read();
		}
		$oSearchResult->setTopics(ilShopTopics::_getInstance()->getTopics());
		$oSearchResult->setResultPageNumber((int)$_GET['page_number']);		
		
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
		$res = null;	
			
		$obj_search = ilObjectSearchFactory::_getShopObjectSearchInstance($query_parser);
		$obj_search->setFilterShopTopicId((int)$this->getTopicId());
		$obj_search->setFilter($this->getFilter());
		$obj_search->setCustomSearchResultObject($oSearchResult);
		$res = $obj_search->performSearch();

		$meta_search_c = ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
		$meta_search_c->setMode('contribute');
		$meta_search_c->setFilter($this->getFilter());
		$meta_search_c->setFilterShopTopicId((int)$this->getTopicId());
		$meta_search_c->setCustomSearchResultObject($oSearchResult);		
		$res->mergeEntries($meta_search_c->performSearch());
		
		$meta_search_t = ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
		$meta_search_t->setMode('title');
		$meta_search_t->setFilter($this->getFilter());
		$meta_search_t->setCustomSearchResultObject($oSearchResult);
		$meta_search_t->setFilterShopTopicId((int)$this->getTopicId());
		$res->mergeEntries($meta_search_t->performSearch());
			
		$meta_search_k = ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
		$meta_search_k->setMode('keyword');
		$meta_search_k->setFilter($this->getFilter());
		$meta_search_k->setCustomSearchResultObject($oSearchResult);
		$meta_search_k->setFilterShopTopicId((int)$this->getTopicId());	
		$res->mergeEntries($meta_search_k->performSearch());

		return $res;
	}
	
	private function getFilter()
	{
		foreach($this->getDetails() as $key => $detail_type)
		{
			switch($detail_type)
			{
				case 'crs':
					$filter[] = 'crs';
					break;
				case 'lms':
					$filter[] = 'lm';
					$filter[] = 'sahs';
					$filter[] = 'htlm';
					break;
				case 'tst':
					$filter[] = 'tst';
					break;
				case 'fil':
					$filter[] = 'file';
					break;
			}
		}
		return $filter ? $filter : array();
	}
	
	public function showForm($result = null)
	{
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_advanced_search.html', 'Services/Payment');
		include_once 'Services/Payment/classes/class.ilAdvancedSearchFilterGUI.php';
		$filterGUI = new ilAdvancedSearchFilterGUI($this, $this->cmd);
		$filterGUI->initFilter();
		if($this->cmd == 'setFilter')
		{	
			$filterGUI->writeFilterToSession();
		}
		else
		{
			$_SESSION['shop_advanced_search'] = array();
			$filterGUI->resetFilter();
		}
		$this->tpl->setVariable('FILTER', $filterGUI->getHtml());

		// show results
		if($result && count($result->getResults()))
		{
			include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
			$search_result_presentation = new ilShopResultPresentationGUI($result);			
			$this->tpl->setVariable('RESULTS', $search_result_presentation->showAdvancedSearchResults());
		}
		else
		{
			$this->tpl->setVariable('RESULTS', $this->lng->txt('payment_shop_not_objects_found'));
		}
		
		if($result)
		{
			$this->addPager($result);
		}	
		
//		return true;
	}
	
	public function setCombination($a_combination)
	{
		$_SESSION['shop_advanced_search']['combination'] = $this->combination = $a_combination;
	}
	public function getCombination()
	{
		return $this->combination ? $this->combination : self::SEARCH_OR;
	}
	public function setString($a_str)
	{
		$_SESSION['shop_advanced_search']['string'] = $this->string = $a_str;
	}
	public function getString()
	{
		return $this->string;
	}
	public function setDetails($a_details)
	{
		$_SESSION['shop_advanced_search']['details'] = $this->details = $a_details;
	}	
	public function getDetails()
	{
		return $this->details ? $this->details : array();
	}
	public function setTopicId($a_topic)
	{
		$_SESSION['shop_advanced_search']['topic'] = $this->topic_id = $a_topic;
	}
	public function getTopicId()
	{
		return $this->topic_id;
	}
	
	public function setSortDirection($a_sort_direction)
	{
		$_SESSION['shop_advanced_search']['order_direction'] = $this->sort_direction = $a_sort_direction;
	}
	public function getSortDirection()
	{
		return $this->sort_direction;
	}	
	public function setSortField($a_field)
	{
		$_SESSION['shop_advanced_search']['shop_order_field'] = $this->sort_field = $a_field;
	}
	public function getSortField()
	{
		return $this->sort_field;
	}
	public function setSortingTypeTopics($a_field)
	{
		global $ilUser;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId() && 
		   $a_field == ilShopTopics::TOPICS_SORT_MANUALLY)
		{
			$a_field = ilShopTopics::TOPICS_SORT_BY_TITLE;
		}
		
		$_SESSION['shop_advanced_search']['order_topics_sorting_type'] = $this->sort_type_topics = $a_field;
	}
	public function getSortingTypeTopics()
	{
		global $ilUser;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId() && 
		   $this->sort_type_topics == ilShopTopics::TOPICS_SORT_MANUALLY)
		{
			$this->sort_type_topics = ilShopTopics::TOPICS_SORT_BY_TITLE;
		}
		
		return $this->sort_type_topics;
	}
	public function setSortingDirectionTopics($a_sort_direction)
	{
		$_SESSION['shop_advanced_search']['shop_topics_sorting_direction'] = $this->sort_direction_topics = $a_sort_direction;
	}
	public function getSortingDirectionTopics()
	{
		return $this->sort_direction_topics;
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;
			
		parent::prepareOutput();		
		
		$ilTabs->setTabActive('advanced_search');
	}
}
?>