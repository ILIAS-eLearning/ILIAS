<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
	
	public function performSearch()
	{		
		if(isset($_POST['search']['combination'])) $this->setCombination($_POST['search']['combination']);
		if(isset($_POST['search']['string'])) $this->setString($_POST['search']['string']);
		if(isset($_POST['search']['details'])) $this->setDetails($_POST['search']['details']);
		if(isset($_POST['search']['topic'])) $this->setTopicId($_POST['search']['topic']);		
		
		if(!$this->getDetails())
		{
			if(method_exists($this, $this->ctrl->getCmd()))
				ilUtil::sendInfo($this->lng->txt('search_choose_object_type'));
			$this->showForm(new ilShopSearchResult());

			return false;
		}
		
		// Step 1: parse query string
		if(!is_object($query_parser = $this->parseQueryString()))
		{
			ilUtil::sendInfo($query_parser);
			$this->showForm(new ilShopSearchResult());
			
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
		$oSearchResult = new ilShopSearchResult();
		if((bool)$this->oGeneralSettings->get('topics_allow_custom_sorting'))
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
			ilShopTopics::_getInstance()->setSortingType((int)$this->oGeneralSettings->get('topics_sorting_type'));
			ilShopTopics::_getInstance()->setSortingDirection(strtoupper($this->oGeneralSettings->get('topics_sorting_direction')));
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
			switch($key)
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
		global $ilUser;
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_advanced_search.html', 'Services/Payment');

		$this->tpl->setVariable('TBL_TITLE',$this->lng->txt('advanced_search'));		
		$this->tpl->setVariable('SEARCH_ACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_SEARCHTERM',$this->lng->txt('search_search_term'));
		$this->tpl->setVariable('TXT_AND',$this->lng->txt('search_all_words'));
		$this->tpl->setVariable('TXT_OR',$this->lng->txt('search_any_word'));		
		$this->tpl->setVariable('TXT_OBJECT_TYPE',$this->lng->txt('obj_type'));
		$this->tpl->setVariable('TXT_TOPIC',$this->lng->txt('topic'));
		$this->tpl->setVariable('BTN_SEARCH',$this->lng->txt('search'));

		$this->tpl->setVariable('FORM_SEARCH_STR', ilUtil::prepareFormOutput($this->getString(), true));
		
		if ($this->getCombination() == self::SEARCH_AND)
		{
			$this->tpl->setVariable('AND_CHECKED', 'checked="checked"');
		}
		else
		{
			$this->tpl->setVariable('OR_CHECKED', 'checked="checked"');
		}	
		
		$this->tpl->setVariable('CRS',$this->lng->txt('courses'));
		$this->tpl->setVariable('LMS',$this->lng->txt('learning_resources'));
		$this->tpl->setVariable('TST',$this->lng->txt('tests'));
		$this->tpl->setVariable('FIL',$this->lng->txt('objs_file'));
		$details = $this->getDetails();
		$this->tpl->setVariable('CHECK_CRS', ilUtil::formCheckbox($details['crs'] ? 1 : 0,'search[details][crs]', 1));
		$this->tpl->setVariable('CHECK_LMS', ilUtil::formCheckbox($details['lms'] ? 1 : 0,'search[details][lms]', 1));
		$this->tpl->setVariable('CHECK_TST', ilUtil::formCheckbox($details['tst'] ? 1 : 0,'search[details][tst]', 1));
		$this->tpl->setVariable('CHECK_FIL', ilUtil::formCheckbox($details['fil'] ? 1 : 0,'search[details][fil]', 1));
		
		$selectable_topics = array();
		$selectable_topics[''] = $this->lng->txt('search_any');;
		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();
		foreach(ilShopTopics::_getInstance()->getTopics() as $oTopic)
		{
			$selectable_topics[$oTopic->getId()] = $oTopic->getTitle();			
		}
		
		$this->tpl->setVariable('SELECT_TOPIC', ilUtil::formSelect(array($this->getTopicId()), 'search[topic]', $selectable_topics, false, true));
		
		// show results
		if(count($result->getResults()))
		{
			include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
			$search_result_presentation = new ilShopResultPresentationGUI($result);			
		 		
			$this->tpl->setVariable('RESULTS', $search_result_presentation->showResults());
			
			$order_fields = array(
				'title' => $this->lng->txt('title'),
				'author' => $this->lng->txt('author'),
				'price' => $this->lng->txt('price_a')
			);
			
			foreach($order_fields as $key => $value)
			{
				$this->tpl->setCurrentBlock('order_field');
				$this->tpl->setVariable('ORDER_FIELD_VALUE', $key);
				$this->tpl->setVariable('ORDER_FIELD_TEXT', $value);
				if (strcmp(trim($this->getSortField()), $key) == 0)
				{
					$this->tpl->setVariable('ORDER_FIELD_SELECTED', ' selected="selected"');
				}
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setVariable('SORTING_FORM_ACTION', $this->ctrl->getFormAction($this, 'setSorting'));			
			$this->tpl->setVariable('CMD_SORT', 'setSorting');
			$this->tpl->setVariable('SORT_TEXT', $this->lng->txt('sort'));
			$this->tpl->setVariable('SORT_BY_TEXT', $this->lng->txt('sort_by'));			
			$this->tpl->setVariable('ASCENDING_TEXT', $this->lng->txt('sort_asc'));
			$this->tpl->setVariable('DESCENDING_TEXT', $this->lng->txt('sort_desc'));			
			$this->tpl->setVariable('ORDER_DIRECTION_'.strtoupper(trim($this->getSortDirection())).'_SELECTED', " selected=\"selected\"");		
			
			if((bool)$this->oGeneralSettings->get('topics_allow_custom_sorting'))
			{
				$this->tpl->setCurrentBlock('topics_sort_block');
				
				$this->tpl->setVariable('SORT_TOPICS_BY_TEXT', $this->lng->txt('sort_topics_by'));
				
				$this->tpl->setVariable('SORTING_TYPE_BY_TITLE', ilShopTopics::TOPICS_SORT_BY_TITLE);
				$this->tpl->setVariable('SORTING_TYPE_BY_TITLE_TEXT', $this->lng->txt('sort_topics_by_title'));
				if($this->getSortingTypeTopics() == ilShopTopics::TOPICS_SORT_BY_TITLE)
				{
					$this->tpl->setVariable('SORTING_TYPE_BY_TITLE_SELECTED', ' selected="selected"');
				}
				
				$this->tpl->setVariable('SORTING_TYPE_BY_DATE', ilShopTopics::TOPICS_SORT_BY_CREATEDATE);
				$this->tpl->setVariable('SORTING_TYPE_BY_DATE_TEXT', $this->lng->txt('sort_topics_by_date'));
				if($this->getSortingTypeTopics() == ilShopTopics::TOPICS_SORT_BY_CREATEDATE)
				{
					$this->tpl->setVariable('SORTING_TYPE_BY_DATE_SELECTED', ' selected="selected"');
				}
				
				if(ANONYMOUS_USER_ID != $ilUser->getId())
				{
					$this->tpl->setCurrentBlock('sort_manually');
					$this->tpl->setVariable('SORTING_TYPE_MANUALLY', ilShopTopics::TOPICS_SORT_MANUALLY);			
					$this->tpl->setVariable('SORTING_TYPE_MANUALLY_TEXT', $this->lng->txt('sort_topics_manually'));
					if($this->getSortingTypeTopics() == ilShopTopics::TOPICS_SORT_MANUALLY)
					{
						$this->tpl->setVariable('SORTING_TYPE_MANUALLY_SELECTED', ' selected="selected"');
					}
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setVariable('SORTING_DIRECTION_ASCENDING_TEXT', $this->lng->txt('sort_asc'));				
				$this->tpl->setVariable('SORTING_DIRECTION_DESCENDING_TEXT', $this->lng->txt('sort_desc'));
				if(in_array(strtoupper($this->getSortingDirectionTopics()), array('ASC', 'DESC')))
				{
					$this->tpl->setVariable('SORTING_DIRECTION_'.strtoupper($this->getSortingDirectionTopics()).'_SELECTED', 
											 ' selected="selected"');
				}
				else
				{
					$this->tpl->setVariable('SORTING_DIRECTION_'.strtoupper(ilShopTopics::DEFAULT_SORTING_DIRECTION).'_SELECTED', ' selected="selected"');
				}
				
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock('sorting');
			$this->tpl->parseCurrentBlock();
		}
		
		$this->addPager($result, 'shop_advanced_search_maxpage');
	
		return true;
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