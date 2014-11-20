<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once 'Services/Payment/classes/class.ilPaymentSettings.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once  './Services/Payment/classes/class.ilShopRepositoryExplorer.php';

include_once './Services/Payment/classes/class.ilShopSearchResult.php';

/**
 * Class ilShopGUI
 * @author       Michael Jansen <mjansen@databay.de>
 * @author       Nadia Ahmad <nahmad@databay.de>
 * @version      $Id:$
 * @ilCtrl_Calls ilShopGUI: ilShopPageGUI
 * @ingroup      ServicesPayment
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

	public $settings = null;

	public $cur_ref_id = null;

	/**
	 * @var object $ilShopSearchResult ilShopSearchResult
	 */
	protected $ilShopSearchResult = null;

	public function __construct()
	{
		parent::__construct();

		global $ilCtrl;

		if(isset($_POST['cmd']) && $_POST['cmd'] == 'setFilter')
		{
			$this->cmd        = 'setFilter';
		}
		else
		{
			$this->cmd        = $ilCtrl->getCmd();	
		}
			
		$this->cur_ref_id = (int)$_GET['ref_id'];
		$this->settings = ilPaymentSettings::_getInstance();
		
		$this->ilShopSearchResult = ilShopSearchResult::_getInstance(SHOP_CONTENT);
	
		// set filter settings
		$this->setType($_SESSION['shop_content']['type']);
		$this->setString($_SESSION['shop_content']['text']);
		$this->setTopicId($_POST['filter_topic_id']);

		// set sorting
		$this->setSortingTypeTopics($_SESSION['shop_content']['order_topics_sorting_type']);
		$this->setSortingDirectionTopics($_SESSION['shop_content']['shop_topics_sorting_direction']);
		$this->setSortField($_POST['order_field']);
		$this->setSortDirection($_SESSION['shop_content']['shop_order_direction']);
	}

	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		if(isset($_GET['ref_id']))
		{
			$this->cmd = 'showTree';
		}
		
		switch($next_class)
		{
			case 'ilshoppagegui':
				$this->prepareOutput();

				$ret = $this->forwardToPageObject();
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}
				break;

			default:
				switch($this->cmd)
				{
					case 'firstpage':
						$this->clearFilter();

						if($this->settings->get('use_shop_specials') == true)
						{
							//@todo ... continue
							$this->ilShopSearchResult->setFilterMode(ilShopSearchResult::SHOW_SPECIAL_CONTENT);
							
							$cmd = 'showSpecialContent';
						}
						else
						{
							$cmd = 'performSearch';
						}
						break;
					case 'showTree':
						$obj_type  = ilObject::_lookupType(ilObject::_lookupObjId($this->cur_ref_id));
						$container = array("root", "cat", 'catr', "grp", "crs", 'crsr', 'rcrs');

						if(in_array($obj_type, $container))
						{
							//@todo ... continue
							$this->ilShopSearchResult->setFilterMode(ilShopSearchResult::SHOW_CONTAINER_CONTENT);
							$cmd = 'showContainerContent';
						}
						else
						{
							$cmd = 'performSearch';
						}
						break;
					case 'resetFilter':
						$cmd = 'resetFilter';
						break;
					case 'setFilter':
						$cmd = 'setFilter';
						break;
					default:
						$cmd = 'performSearch';
						break;
				}

				$this->prepareOutput();
				$this->$cmd();
				break;
		}

		return true;
	}

	
	public function showFilters()
	{
		$show_general_filter = $this->settings->get('show_general_filter');
		$show_topics_filter  = $this->settings->get('show_topics_filter');
		$topics_enabled 	 = $this->settings->get('enable_topics'); 
		$show_shop_explorer  = $this->settings->get('show_shop_explorer');

		if($show_general_filter)
		{
			$g_filter_html = $this->showGeneralFilter();
			$this->tpl->setCurrentBlock('show_general_filter');
			$this->tpl->setVariable('FORM', $g_filter_html);
			$this->tpl->parseCurrentBlock();
		}
		
		if($show_topics_filter && $topics_enabled)
		{
			$this->showTopicsFilter();
		}
		if($show_shop_explorer)
		{
			$this->showShopExplorer();
		}
	}
	
	
	public function clearFilter()
	{
		$this->setString('');
		$this->setType('');
		$this->setTopicId(0);

		#return $this->performSearch();
	}

	public function getPageHTML()
	{
		// page object
		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

		// if page does not exist, return nothing
		if(!ilShopPage::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			return '';
		}

		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		// get page object
		$page_gui = new ilShopPageGUI(self::SHOP_PAGE_EDITOR_PAGE_ID);

		return $page_gui->showPage();
	}

	public function forwardToPageObject()
	{
		global $lng, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('back'), $this->ctrl->getLinkTarget($this), '_top');

		// page object
		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

		$lng->loadLanguageModule('content');

		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilShopPage::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilShopPage();
			$new_page_object->setParentId(0);
			$new_page_object->setId(self::SHOP_PAGE_EDITOR_PAGE_ID);
			$new_page_object->createFromXML();
		}

		$this->ctrl->setReturnByClass('ilshoppagegui', 'edit');

		$page_gui = new ilShopPageGUI(self::SHOP_PAGE_EDITOR_PAGE_ID);

		return $this->ctrl->forwardCommand($page_gui);
	}

	public function setFilter()
	{
		$_SESSION['content_filter']['updateView']      = $_POST['updateView'];
		$_SESSION['content_filter']['show_filter']     = $_POST['show_filter'];
		$_SESSION['content_filter']['sel_filter_type'] = $_POST['sel_filter_type'];
		$_SESSION['content_filter']['filter_text']     = $_POST['filter_text'];
		$_SESSION['content_filter']['filter_topic_id'] = $_POST['filter_topic_id'];

		$_SESSION['content_filter']['order_field']     = $_POST['order_field'];
		$_SESSION['content_filter']['order_direction'] = $_POST['order_direction'];

		$_SESSION['content_filter']['topics_sorting_type']      = $_POST['topics_sorting_type'];
		$_SESSION['content_filter']['topics_sorting_direction'] = $_POST['topics_sorting_direction'];

		$this->setString($_POST['filter_text']);
		$this->setType($_POST['sel_filter_type']);
		$this->setTopicId($_POST['filter_topic_id']);

		$this->setSortingTypeTopics($_POST['topics_sorting_type']);
		$this->setSortingDirectionTopics($_POST['topics_sorting_direction']);
		$this->setSortField($_POST['order_field']);
		$this->setSortDirection('asc');

		$this->performSearch();
		return;
	}

	public function showShopExplorer()
	{
		global $ilCtrl, $tree, $lng, $tpl;

		$ilCtrl->setParameter($this, "active_node", $_GET["active_node"]);
		$tpl->addCss('Services/Payment/css/shop_explorer.css');

		include_once ("./Services/Payment/classes/class.ilShopRepositoryExplorer.php");

		$active_node = ($_GET["active_node"] >= 1)
			? $_GET["active_node"]
			: ($_GET["ref_id"] >= 1)
				? $_GET["ref_id"]
				: 0;

		$top_node = 0;

		$exp = new ilShopRepositoryExplorer("ilias.php", $top_node);
		$exp->setUseStandardFrame(false);
		$exp->setExpandTarget("ilias.php?baseClass=ilshopcontroller&ref_id=1&cmd=showTree");

		$exp->setFrameUpdater("tree", "updater");
		$exp->setTargetGet("ref_id");

		if($_GET["repexpand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["repexpand"];
		}

		$exp->setExpand($expanded);

		if($active_node > 0)
		{
			$path = $tree->getPathId($active_node);
			if($top_node > 0)
			{
				$exp->setForceOpenPath($path);
				$exp->setExpand($expanded);
			}
			else
			{
				$exp->setForceOpenPath($path + array($top_node));
			}
			$exp->highlightNode($active_node);
		}

		// build html-output
		if($top_node > 0)
		{
			$head_tpl = new ilTemplate("tpl.cont_tree_head.html", true, true,
				"Services/Repository");
			$path     = ilObject::_getIcon(ROOT_FOLDER_ID, "tiny", "root");
			$nd       = $tree->getNodeData(ROOT_FOLDER_ID);
			$title    = $nd["title"];
			if($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
			$head_tpl->setVariable("IMG_SRC", $path);
			$head_tpl->setVariable("ALT_IMG", $lng->txt("icon") . " " . $title);
			$head_tpl->setVariable("LINK_TXT", $title);
			$head_tpl->setVariable("LINK_HREF", "ilias.php?baseClass=ilshopcontroller&ref_id=1");
			$exp->setTreeLead($head_tpl->get());

			$exp->initItemCounter(1);
			$exp->setOutput($tree->getParentId($top_node), 1,
				ilObject::_lookupObjId($tree->getParentId($top_node)));
		}
		else
		{
			$exp->setOutput(0);
		}
		$output = $exp->getOutput(true);

		// asynchronous output
		if($ilCtrl->isAsynch())
		{
			echo $output;
			exit;
		}
		
		$ilCtrl->setParameter($this, "repexpand", $_GET["repexpand"]);
		$tpl->setLeftContent($output);
	}

	public function performSearch($oResult = null)
	{
		if(!is_object($oResult))
		{
			$oResult = ilShopSearchResult::_getInstance(SHOP_CONTENT);
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

			$topics = ilShopTopics::_getInstance()->getTopics();

			$oResult->setTopics($topics);
			$oResult->setResultPageNumber((int)$_GET['page_number']);
		}

		// query parser
		include_once 'Services/Search/classes/class.ilQueryParser.php';
		$query_parser = new ilQueryParser(ilUtil::stripSlashes($this->getString()));
		$query_parser->setMinWordLength(0);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			ilUtil::sendInfo($query_parser->getMessage());
		}

		// search
		$types = array('crs', 'lm', 'sahs', 'htlm', 'file', 'tst', 'exc', 'glo');
		if($this->getType() == '' || $this->getType() == 'title' ||
			$query_parser->getQueryString() == ''
		)
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
			$meta_search = ilObjectSearchFactory::_getShopMetaDataSearchInstance($query_parser);
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
			
		$this->showTopicsContent($res);
		$this->addPager($res);
		return;
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
		return;
	}

	//showSpecialContent
	public function showSpecialContent()
	{
		global $ilUser, $rbacreview, $ilToolbar;

		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());

		include_once './Services/Payment/classes/class.ilPaymentObject.php';

		$pobjects = ilPaymentObject::_getSpecialObjects();
		if(count($pobjects))
		{
			foreach($pobjects as $result)
			{
				$obj_id      = ilObject::_lookupObjId($result['ref_id']);
				$title       = ilObject::_lookupTitle($obj_id);
				$description = ilObject::_lookupDescription($obj_id);
				$type        = ilObject::_lookupType($obj_id);

				$presentation_results[(int)$result['pt_topic_fk']][$type][] =
					array(
						'ref_id'      => $result['ref_id'],
						'title'       => $title,
						'description' => $description,
						'type'        => $type,
						'obj_id'      => $obj_id,
						'topic_id'    => (int)$result['pt_topic_fk'],
						'child'       => $result['child']
					);
			}
			$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());
		}
		else
		{
			$this->tpl->setVariable('PAGE_CONTENT', $this->lng->txt('please_choose_category'));
		}

		include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
		$search_result_presentation = new ilShopResultPresentationGUI($presentation_results);
		$search_result_presentation->setSortField(strtolower(trim($this->getSortField())));
		$search_result_presentation->setSortDirection(trim($this->getSortDirection()));

		$html = $search_result_presentation->showSpecials();

		$this->tpl->setVariable('RESULTS', $html);
		$this->showFilters();
	}

	public function showTopicsContent($oResult)
	{
		global $ilUser, $rbacreview, $ilToolbar;

		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		if(!count($oResult->getResults()))
		{
			$this->tpl->setVariable('ERROR',ilUtil::sendInfo($this->lng->txt('payment_shop_not_objects_found')));
		}

		include_once './Services/Payment/classes/class.ilPaymentObject.php';
		$filter_topics_id = NULL;
		if($_SESSION['content_filter']['filter_topic_id'] != NULL)
		{
			$filter_topics_id = (int)$_SESSION['content_filter']['filter_topic_id'];
			$pobjects = ilPaymentObject::_getTopicsObjects($filter_topics_id);
			if(count($pobjects))
			{
				foreach($pobjects as $result)
				{
					$obj_id      = ilObject::_lookupObjId($result['ref_id']);
					$title       = ilObject::_lookupTitle($obj_id);
					$description = ilObject::_lookupDescription($obj_id);
					$type        = ilObject::_lookupType($obj_id);

					$presentation_results[(int)$result['pt_topic_fk']][$type][] = array(
						'ref_id' => $result['ref_id'], 'title' => $title, 'description' => $description, 'type' => $type, 'obj_id' => $obj_id, 'topic_id' => (int)$result['pt_topic_fk'], 'child' => $result['child']
					);
				}
				$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());
			}
			else
			{
				$this->tpl->setVariable('PAGE_CONTENT', $this->lng->txt('please_choose_category'));
			}

			include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
			$search_result_presentation = new ilShopResultPresentationGUI($presentation_results);
			$search_result_presentation->setSortField(strtolower(trim($this->getSortField())));
			$search_result_presentation->setSortDirection(trim($this->getSortDirection()));

			$html = $search_result_presentation->showTopics();
		}
		else
		{
			foreach($oResult->getResults() as $result)
			{

				$obj_id      = ilObject::_lookupObjId($result['ref_id']);
				$title       = ilObject::_lookupTitle($obj_id);
				$description = ilObject::_lookupDescription($obj_id);
				$tmp_res = array('ref_id' => $result['ref_id'], 'title' => $title, 'description' => $description, 
								 'type' => $result['type'], 'obj_id' => $obj_id, 'topic_id' => 0, 'child' => 0);
			
				$presentation_results[0][$result['type']][] = $tmp_res;
			}	
			
			include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
			$search_result_presentation = new ilShopResultPresentationGUI($presentation_results);
			$search_result_presentation->setSortField(strtolower(trim($this->getSortField())));
			$search_result_presentation->setSortDirection(trim($this->getSortDirection()));

			$html = $search_result_presentation->showTopics();
		}
		$this->tpl->setVariable('RESULTS', $html);
		$this->showFilters();
	}

	/*
	 * show buyable "sub"-objects of containers  (cat, catr, crs, grp, ...)
	 */
	public function showContainerContent()
	{
		global $ilUser, $rbacreview, $ilToolbar, $tpl;

		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));
		}

		include_once './Services/Payment/classes/class.ilPaymentObject.php';

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());

		$is_buyable = ilPaymentObject::_isBuyable($this->cur_ref_id);

		if($is_buyable)
		{
			$pobjects = ilPaymentObject::_getObjectData(ilPaymentObject::_lookupPobjectId($this->cur_ref_id));

			$obj_id      = ilObject::_lookupObjId($this->cur_ref_id);
			$title       = ilObject::_lookupTitle($obj_id);
			$description = ilObject::_lookupDescription($obj_id);
			$type        = ilObject::_lookupType($obj_id);

			$presentation_results[$pobjects['pt_topic_fk']][$type][] =
				array(
					'ref_id'      => $pobjects['ref_id'],
					'title'       => $title,
					'description' => $description,
					'type'        => $type,
					'obj_id'      => $obj_id,
					'topic_id'    => $pobjects['pt_topic_fk'],
					'child'       => $pobjects['child']
				);
		}
		else
		{
			$pobjects = ilPaymentObject::_getContainerObjects($this->cur_ref_id);

			if(count($pobjects) >= 1)
			{
				foreach($pobjects as $result)
				{
					$obj_id      = $result['obj_id'];
					$title       = $result['title'];
					$description = $result['description'];
					$type        = $result['type'];

					$presentation_results[$result['pt_topic_fk']][$type][] =
						array(
							'ref_id'      => $result['ref_id'],
							'title'       => $title,
							'description' => $description,
							'type'        => $type,
							'obj_id'      => $obj_id,
							'topic_id'    => $result['pt_topic_fk'],
							'child'       => $result['child']
						);
				}
			}
		}

		include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
		$search_result_presentation = new ilShopResultPresentationGUI($presentation_results);
		$search_result_presentation->setSortField(strtolower(trim($this->getSortField())));
		$search_result_presentation->setSortDirection(trim($this->getSortDirection()));

		if(!$presentation_results)
		{
			$this->tpl->setVariable('RESULTS', $this->lng->txt('payment_shop_not_objects_found'));
		}
		else
		{	
			$html = $search_result_presentation->showSpecials();
			$this->tpl->setVariable('RESULTS', $html);
		}
		$this->showFilters();
	}

	public function showGeneralFilter($a_count_result = 0)
	{
		include_once 'Services/Payment/classes/class.ilShopFilterGUI.php';
		$filterGUI = new ilShopFilterGUI($this, 'setCmd');
		$filterGUI->initFilter();
		if($this->cmd == 'setFilter')
		{
			$filterGUI->writeFilterToSession();
		}
		else
		{
			$filterGUI->resetFilter();
		}
		return $filterGUI->getHtml();
	}

	public function showTopicsFilter($a_count_result = 0)
	{
		global $ilUser;

		$this->tpl->setCurrentBlock('show_topics_filter');

		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();

		if(count(ilShopTopics::_getInstance()->getTopics()))
		{
			$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());
			$this->tpl->setVariable('SORTING_FORM_ACTION', $this->ctrl->getFormAction($this, 'setFilter'));

			$this->tpl->setVariable('SET_FILTER_VAL', $this->lng->txt('pay_update_view'));

			$this->tpl->setCurrentBlock('topics_option');
			$this->tpl->setVariable('SORT_TOPICS', $this->lng->txt('topic'));

			$this->tpl->setVariable('FILTER_TOPIC_ID', 'no_selection');
			$this->tpl->setVariable('FILTER_TOPIC_TEXT', '------------');
			if($_POST['cmd'] == 'firstpage')
				$this->tpl->setVariable('FILTER_TOPIC_SELECTED', 'selected');
			$this->tpl->parseCurrentBlock('topics_option');

			$this->tpl->setVariable('FILTER_TOPIC_ID', 'all');
			$this->tpl->setVariable('FILTER_TOPIC_TEXT', $this->lng->txt('all'));
			if($_POST['filter_topic_id'] == 'all')
				$this->tpl->setVariable('FILTER_TOPIC_SELECTED', 'selected');
			$this->tpl->parseCurrentBlock('topics_option');

			$oTopics = array();
			$oTopics = ilShopTopics::_getInstance()->getTopics();
			foreach($oTopics as $oTopic)
			{
				$this->tpl->setVariable('FILTER_TOPIC_ID', $oTopic->getId());
				$this->tpl->setVariable('FILTER_TOPIC_TEXT', $oTopic->getTitle());
				if($_POST['filter_topic_id'] == $oTopic->getId())
					$this->tpl->setVariable('FILTER_TOPIC_SELECTED', 'selected');
				$this->tpl->parseCurrentBlock('topics_option');
			}
		}

		if($a_count_result)
		{
			$objects = (bool)$this->settings->get('objects_allow_custom_sorting');
			if($objects)
			{
				// sorting form
				$allow_objects_option = array(
					'title'  => $this->lng->txt('title'),
					'author' => $this->lng->txt('author'),
					'price'  => $this->lng->txt('price_a')
				);
				$this->tpl->setCurrentBlock('order_field');
				$this->tpl->setVariable('SORT_BY_TEXT', $this->lng->txt('sort_by'));

				foreach($allow_objects_option as $key=> $value)
				{
					$this->tpl->setVariable('ORDER_FIELD_VALUE', $key);
					$this->tpl->setVariable('ORDER_FIELD_TEXT', $value);
					if($_POST['order_field'] == $key)
						$this->tpl->setVariable('ORDER_FIELD_SELECTED', 'selected');
					$this->tpl->parseCurrentBlock('order_field');
				}
			}

			$topics = (bool)$this->settings->get('topics_allow_custom_sorting');

			if($topics)
			{
				// sorting form
				$allow_topics_option = array(
					ilShopTopics::TOPICS_SORT_BY_TITLE      => $this->lng->txt('sort_topics_by_title'),
					ilShopTopics::TOPICS_SORT_BY_CREATEDATE => $this->lng->txt('sort_topics_by_date')
				);
				if(ANONYMOUS_USER_ID != $ilUser->getId())
				{
					$allow_topics_option[ilShopTopics::TOPICS_SORT_MANUALLY] = $this->lng->txt('sort_topics_manually');
				}
			}
		}
	}

	public function setTopicId($a_topic_id)
	{
		$this->topic_id = $a_topic_id;
	}

	public function getTopicId()
	{
		return $this->topic_id;
	}

	public function setString($a_str)
	{
		$this->string = $a_str;
	}

	public function getString()
	{
		return $this->string;
	}

	public function setType($a_type)
	{
		$this->type = $a_type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setSortDirection($a_sort_direction)
	{
		$this->sort_direction = $a_sort_direction;
	}

	public function getSortDirection()
	{
		return $this->sort_direction;
	}

	public function setSortField($a_field)
	{
		$this->sort_field = $a_field;
	}

	public function getSortField()
	{
		return $this->sort_field;
	}

	public function setSortingTypeTopics($a_field)
	{
		global $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId() &&
			$a_field == ilShopTopics::TOPICS_SORT_MANUALLY
		)
		{
			$a_field = ilShopTopics::TOPICS_SORT_BY_TITLE;
		}

		$this->sort_type_topics = $a_field;
	}

	public function getSortingTypeTopics()
	{
		global $ilUser;

		if(ANONYMOUS_USER_ID == $ilUser->getId() &&
			$this->sort_type_topics == ilShopTopics::TOPICS_SORT_MANUALLY
		)
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
