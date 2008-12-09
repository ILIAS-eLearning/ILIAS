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
* Class ilShopGUI
*
* @author Michael Jansen <mjansen@databay.de>
* 
* @ilCtrl_Calls ilShopGUI: ilPageObjectGUI
*
* @ingroup ServicesPayment
*/
class ilShopGUI extends ilShopBaseGUI
{
	const SHOP_PAGE_EDITOR_PAGE_ID = 99999999;
	
	private $sort_type_topics = '';
	private $sort_direction_topics = '';
	private $sort_field = '';
	private $sort_direction = '';
	private $string = '';
	private $type = '';
	private $topic_id = 0;	
	
	public function __construct()
	{
		parent::__construct();
		
		// set filter settings
		$this->setType($_SESSION['shop_content']['type']);
		$this->setString($_SESSION['shop_content']['text']);		
		$this->setTopicId($_SESSION['shop_content']['shop_topic_id']);		
		
		// set sorting
		$this->setSortingTypeTopics($_SESSION['shop_content']['order_topics_sorting_type']);
		$this->setSortingDirectionTopics($_SESSION['shop_content']['shop_topics_sorting_direction']);		
		$this->setSortField($_SESSION['shop_content']['shop_order_field']);
		$this->setSortDirection($_SESSION['shop_content']['shop_order_direction']);
	}
		
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilpageobjectgui':
				$this->prepareOutput();
				$ret = $this->forwardToPageObject();
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}				
				break;
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
	
	public function clearFilter()
	{
		$this->setString('');
		$this->setType('');
		$this->setTopicId(0);
		
		return $this->performSearch();
	}
	
	public function getPageHTML()
	{
		// page object
		include_once 'Services/COPage/classes/class.ilPageObject.php';
		include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';

		// if page does not exist, return nothing
		if(!ilPageObject::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			return '';
		}
		
		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));
		
		// get page object
		$page_gui = new ilPageObjectGUI('shop', self::SHOP_PAGE_EDITOR_PAGE_ID);
		$page_gui->setIntLinkHelpDefault('StructureObject', self::SHOP_PAGE_EDITOR_PAGE_ID);
		$page_gui->setLinkXML('');
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'downloadFile'));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'displayMediaFullscreen'));
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'download_paragraph'));
		$page_gui->setPresentationTitle('');
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader('');
		$page_gui->setEnabledRepositoryObjects(false);
		$page_gui->setEnabledFileLists(true);
		$page_gui->setEnabledPCTabs(true);
		$page_gui->setEnabledMaps(true);
		
		return $page_gui->showPage();
	}
	
	public function forwardToPageObject()
	{	
		global $lng, $ilTabs;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('back'), $this->ctrl->getLinkTarget($this), '_top');

		// page object
		include_once 'Services/COPage/classes/class.ilPageObject.php';
		include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';

		$lng->loadLanguageModule('content');

		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilPageObject::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilPageObject('shop');
			$new_page_object->setParentId(0);
			$new_page_object->setId(self::SHOP_PAGE_EDITOR_PAGE_ID);
			$new_page_object->createFromXML();
		}

		$this->ctrl->setReturnByClass('ilpageobjectgui', 'edit');

		$page_gui = new ilPageObjectGUI('shop', self::SHOP_PAGE_EDITOR_PAGE_ID);		
		$page_gui->setIntLinkHelpDefault('StructureObject', self::SHOP_PAGE_EDITOR_PAGE_ID);
		$page_gui->setTemplateTargetVar('ADM_CONTENT');
		$page_gui->setLinkXML('');
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'downloadFile'));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'displayMediaFullscreen'));
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'download_paragraph'));
		$page_gui->setPresentationTitle('');
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader('');
		$page_gui->setEnabledRepositoryObjects(false);
		$page_gui->setEnabledFileLists(true);
		$page_gui->setEnabledMaps(true);
		$page_gui->setEnabledPCTabs(true);

		return $this->ctrl->forwardCommand($page_gui);
	}
			
	public function setFilter()
	{		
		$this->setString($_POST['filter_text']);
		$this->setType($_POST['sel_filter_type']);
		$this->setTopicId($_POST['filter_topic_id']);
		
		$this->performSearch();
		
		return true;
	}
		
	public function resetFilter()
	{		
		$this->setString('');
		$this->setType('');
		$this->setTopicId(0);
		
		$this->performSearch();
		
		return true;
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
	
	public function performSearch($oResult = null)
	{
		global $ilUser;
		
		if(!is_object($oResult))
		{
			$oResult = new ilShopSearchResult($ilUser->getId());		
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
			$oResult->setTopics(ilShopTopics::_getInstance()->getTopics());
			$oResult->setResultPageNumber((int)$_GET['page_number']);
		}
		
		// query parser
		include_once 'Services/Search/classes/class.ilQueryParser.php';
		$query_parser = new ilQueryParser(ilUtil::stripSlashes($this->getString()));
		$query_parser->setMinWordLength(0);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if (!$query_parser->validate())
		{
			ilUtil::sendInfo($query_parser->getMessage());
		}	

		// search
		$types = array('crs', 'lm', 'sahs', 'htlm', 'file', 'tst');
		if ($this->getType() == '' || $this->getType() == 'title' || 
			$query_parser->getQueryString() == '')
		{
			include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
			$object_search = ilObjectSearchFactory::_getShopObjectSearchInstance($query_parser);			
			$object_search->setFields(array('title'));			
			$object_search->setFilter($types);
			$object_search->setCustomSearchResultObject($oResult);
			$object_search->setFilterShopTopicId((int)$this->getTopicId());
			$res = $object_search->performSearch();			
		}
		else if($this->getType() == 'author')
		{
			include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
			$meta_search =  ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
			$meta_search->setMode('contribute');
			$meta_search->setFilter($types);
			$meta_search->setFilterShopTopicId((int)$this->getTopicId());
			$meta_search->setCustomSearchResultObject($oResult);		
			$res = $meta_search->performSearch();
		}
		else
		{			
			include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
			$meta_search = ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
			$meta_search->setMode('title');
			$meta_search->setFilter($types);
			$meta_search->setCustomSearchResultObject($oResult);
			$meta_search->setFilterShopTopicId((int)$this->getTopicId());
			$res = $meta_search->performSearch();			
			
			$meta_search = ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
			$meta_search->setMode('keyword');
			$meta_search->setFilter($types);
			$meta_search->setCustomSearchResultObject($oResult);
			$meta_search->setFilterShopTopicId((int)$this->getTopicId());	
			$res->mergeEntries($meta_search->performSearch());
		}
		
		$res->filter(ROOT_FOLDER_ID, true);
		$res->save();
		
		if(!count($res->getResults()))
		{
			ilUtil::sendInfo($this->lng->txt('payment_shop_not_objects_found'));
		}
	
		$this->showShopContent($res);
		
		include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
		$search_result_presentation = new ilShopResultPresentationGUI($res);		
		$search_result_presentation->setSortField(strtolower(trim($this->getSortField())));
		$search_result_presentation->setSortDirection(trim($this->getSortDirection()));	
		
		$this->tpl->setVariable('RESULTS', $search_result_presentation->showResults());	 	
		$this->addPager($res, 'shop_content_maxpage');		
		return true;
	}
	
	public function showShopContent($oResult)
	{
		global $ilUser, $rbacreview;
		
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
			$this->tpl->setCurrentBlock('btn_cell');
			$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
			$this->tpl->setVariable('BTN_TXT', $this->lng->txt('edit_page'));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		
		$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());
		
		// filter form
		$this->tpl->setVariable('FILTER_TEXT', $this->lng->txt('filter'));
		$this->tpl->setVariable('TEXT_FILTER_BY', $this->lng->txt('filter_by'));		
		$filter_fields = array(
			'title' => $this->lng->txt('title'),
			'author' => $this->lng->txt('author'),
			'metadata' => $this->lng->txt('meta_data')
		);		
		foreach($filter_fields as $key => $value)
		{
			$this->tpl->setCurrentBlock('filterrow');
			$this->tpl->setVariable('VALUE_FILTER_TYPE', $key);
			$this->tpl->setVariable('NAME_FILTER_TYPE', $value);
			if (strcmp(trim($this->getType()), $key) == 0)
			{
				$this->tpl->setVariable('VALUE_FILTER_SELECTED', ' selected="selected"');
			}
			$this->tpl->parseCurrentBlock();
		}		
		$this->tpl->setVariable('VALUE_FILTER_TEXT', ilUtil::prepareFormOutput($this->getString(), true));		
		$this->tpl->setVariable('FILTER_FORM_ACTION', $this->ctrl->getFormAction($this, 'setFilter'));
		$this->tpl->setVariable('CMD_SUBMIT_FILTER', 'setFilter');
		$this->tpl->setVariable('CMD_RESET_FILTER', 'resetFilter');			
		$this->tpl->setVariable('VALUE_SUBMIT_FILTER', $this->lng->txt('set_filter'));
		$this->tpl->setVariable('VALUE_RESET_FILTER', $this->lng->txt('reset_filter'));
	
		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();
		$options = array();
		if(count(ilShopTopics::_getInstance()->getTopics()))
		{
			$options[''] = $this->lng->txt('please_select');
			foreach(ilShopTopics::_getInstance()->getTopics() as $oTopic)
			{
				$options[$oTopic->getId()] = $oTopic->getTitle();
			}
			$this->tpl->setCurrentBlock('filter_topics');
			$this->tpl->setVariable('TXT_FILTER_TOPICS', $this->lng->txt('topic'));
			$this->tpl->setVariable('SELECTBOX_FILTER_TOPICS', ilUtil::formSelect($this->getTopicId(), 'filter_topic_id', $options, false, true));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$options[''] = $this->lng->txt('no_topics_yet');
		}

		
		if(count($oResult->getResults()))
		{
			// sorting form
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
	}
	
	public function setTopicId($a_topic_id)
	{
		$_SESSION['shop_content']['shop_topic_id'] = $this->topic_id = $a_topic_id;
	}
	public function getTopicId()
	{
		return $this->topic_id;
	}
	public function setString($a_str)
	{
		$_SESSION['shop_content']['text'] = $this->string = $a_str;
	}
	public function getString()
	{
		return $this->string;
	}
	public function setType($a_type)
	{
		$_SESSION['shop_content']['type'] = $this->type = $a_type;
	}
	public function getType()
	{
		return $this->type;
	}
	public function setSortDirection($a_sort_direction)
	{
		$_SESSION['shop_content']['order_direction'] = $this->sort_direction = $a_sort_direction;
	}
	public function getSortDirection()
	{
		return $this->sort_direction;
	}	
	public function setSortField($a_field)
	{
		$_SESSION['shop_content']['shop_order_field'] = $this->sort_field = $a_field;
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
		
		$_SESSION['shop_content']['order_topics_sorting_type'] = $this->sort_type_topics = $a_field;
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
		$_SESSION['shop_content']['shop_topics_sorting_direction'] = $this->sort_direction_topics = $a_sort_direction;
	}
	public function getSortingDirectionTopics()
	{
		return $this->sort_direction_topics;
	}
		
	protected function prepareOutput()
	{
		global $ilTabs;		
		
		parent::prepareOutput();
		
		$ilTabs->setTabActive('content');
	}
}
?>
