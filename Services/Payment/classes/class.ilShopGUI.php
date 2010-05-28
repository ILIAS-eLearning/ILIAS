<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

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
	
	private $form = null;
	
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
		global $ilUser;
		
		// Check for incomplete profile
		if($ilUser->getProfileIncomplete())
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		// check whether password of user have to be changed
		// due to first login or password of user is expired
		if( $ilUser->isPasswordChangeDemanded() || $ilUser->isPasswordExpired() )
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

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
			$oResult = new ilShopSearchResult(SHOP_CONTENT);		
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
	public function resetFilter()
	{
		unset($_SESSION['content_filter']);
		unset($_POST['sel_filter_type']);
		unset($_POST['filter_text']);
		unset($_POST['filter_topic_id']);
		unset($_POST['order_field']);
		unset($_POST['order_direction']);
		unset($_POST['topics_sorting_type']);
		unset($_POST['topics_sorting_direction']);

		unset($_POST['updateView']);
		unset($_POST['show_filter']);

		ilUtil::sendInfo($this->lng->txt('paya_filter_reseted'));
		$this->setString('');
		$this->setType('');
		$this->setTopicId(0);

		$this->performSearch();

		return true;
	}
	public function showShopContent($oResult)
	{
		global $ilUser, $rbacreview, $ilToolbar;

//		include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
		
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		
		$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());

		if(!$_POST['show_filter'] && $_POST['updateView'] == '1')
		{
			$this->resetFilter();
		}
		else
		if ($_POST['updateView'] == 1)
		{
			$_SESSION['content_filter']['updateView'] = $_POST['updateView'];
			$_SESSION['content_filter']['show_filter'] = $_POST['show_filter'];
			$_SESSION['content_filter']['sel_filter_type'] = $_POST['sel_filter_type'];
			$_SESSION['content_filter']['filter_text'] = $_POST['filter_text'];
			$_SESSION['content_filter']['filter_topic_id'] = $_POST['filter_topic_id'];

			$_SESSION['content_filter']['order_field'] = $_POST['order_field'];
			$_SESSION['content_filter']['order_direction'] = $_POST['order_direction'];

			$_SESSION['content_filter']['topics_sorting_type'] = $_POST['topics_sorting_type'];
			$_SESSION['content_filter']['topics_sorting_direction'] = $_POST['topics_sorting_direction'];
		}
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		// FILTER FORM
		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('formular');
		$filter_form->setTableWidth('100 %');

		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'),'show_filter');
		$o_hide_check->setValue(1);
		$o_hide_check->setChecked($_SESSION['content_filter']['show_filter'] ? 1 : 0);

		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);

		$o_filter = new ilSelectInputGUI();
		$filter_option = array(
				'title' => $this->lng->txt('title'),
				'author' => $this->lng->txt('author'),
				'metadata' => $this->lng->txt('meta_data')
      		);
		$o_filter->setTitle($this->lng->txt('search_in'));
		$o_filter->setOptions($filter_option);
		$o_filter->setValue($_SESSION['content_filter']['sel_filter_type']);
		$o_filter->setPostVar('sel_filter_type');
		$o_hide_check->addSubItem($o_filter);

		$o_filter_by = new ilTextInputGUI($this->lng->txt('filter_by'));
		$o_filter_by->setValue($_SESSION['content_filter']['filter_text']);
		$o_filter_by->setPostVar('filter_text');
		$o_hide_check->addSubItem($o_filter_by);

		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();
		$topic_option = array();
		if(count(ilShopTopics::_getInstance()->getTopics()))
		{
			$topic_option[''] = $this->lng->txt('please_select');
			foreach(ilShopTopics::_getInstance()->getTopics() as $oTopic)
			{
			  $topic_option[$oTopic->getId()] = $oTopic->getTitle();
			}
		}
		else
		{
			$topic_option[''] = $this->lng->txt('no_topics_yet');
		}
		$o_topic = new ilSelectInputGUI();
		$o_topic->setTitle($this->lng->txt('topic'));
		$o_topic->setOptions($topic_option);
		$o_topic->setValue($_SESSION['content_filter']['filter_topic_id']);
		$o_topic->setPostVar('filter_topic_id');
		$o_hide_check->addSubItem($o_topic);

		if(count($oResult->getResults()))
		{
			$objects = (bool)$this->oGeneralSettings->get('objects_allow_custom_sorting');
			if($objects)
			{
				// sorting form
				$allow_objects_option = array(
					'title' => $this->lng->txt('title'),
					'author' => $this->lng->txt('author'),
					'price' => $this->lng->txt('price_a')
				);
				$o_allow_objects = new ilSelectInputGUI();
				$o_allow_objects->setTitle($this->lng->txt('sort_by'));
				$o_allow_objects->setOptions($allow_objects_option);
				$o_allow_objects->setValue($this->getSortField());
				$o_allow_objects->setPostVar('order_field'); //objects_sorting_type
				$o_hide_check->addSubItem($o_allow_objects);

				$direction_option = array(
					'asc' => $this->lng->txt('sort_asc'),
					'desc' => $this->lng->txt('sort_desc')
				);

				$o_object_direction = new ilSelectInputGUI();
	
				$o_object_direction->setOptions($direction_option);
				$o_object_direction->setValue($this->getSortDirection());
				$o_object_direction->setPostVar('order_direction'); //objects_sorting_direction

				$o_hide_check->addSubItem($o_object_direction);
			}

			$topics = (bool)$this->oGeneralSettings->get('topics_allow_custom_sorting');
			if($topics)
			{
				// sorting form
				$allow_topics_option = array(
					ilShopTopics::TOPICS_SORT_BY_TITLE => $this->lng->txt('sort_topics_by_title'),
					ilShopTopics::TOPICS_SORT_BY_CREATEDATE => $this->lng->txt('sort_topics_by_date')
				);
				if(ANONYMOUS_USER_ID != $ilUser->getId())
				{
					$allow_topics_option[ilShopTopics::TOPICS_SORT_MANUALLY] = $this->lng->txt('sort_topics_manually');
				}

				$o_allow_topics = new ilSelectInputGUI();
				$o_allow_topics->setTitle($this->lng->txt('sort_topics_by'));
				$o_allow_topics->setOptions($allow_topics_option);

				$o_allow_topics->setValue($this->getSortingTypeTopics());
				$o_allow_topics->setPostVar('topics_sorting_type');
				$o_hide_check->addSubItem($o_allow_topics);

				$direction_option = array(
					'asc' => $this->lng->txt('sort_asc'),
					'desc' => $this->lng->txt('sort_desc')
				);

				$o_topics_direction = new ilSelectInputGUI();
				$o_topics_direction->setOptions($direction_option);
				$o_topics_direction->setValue($this->getSortingDirectionTopics());
				$o_topics_direction->setPostVar('topics_sorting_direction'); //objects_sorting_type

				$o_hide_check->addSubItem($o_topics_direction);
			}
		}

		$filter_form->addCommandButton('setFilter', $this->lng->txt('pay_update_view'));
		$filter_form->addCommandButton('resetFilter', $this->lng->txt('pay_reset_filter'));
		$filter_form->addItem($o_hide_check);

		$this->tpl->setVariable('FORM', $filter_form->getHTML());
		
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
		
		else

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
