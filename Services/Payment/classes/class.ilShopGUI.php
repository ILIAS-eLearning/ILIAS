<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once 'Services/Payment/classes/class.ilPaymentSettings.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once  './Services/Payment/classes/class.ilShopRepositoryExplorer.php';

/**
 * Class ilShopGUI
 * @author       Michael Jansen <mjansen@databay.de>
 * @author       Nadia Ahmad <nahmad@databay.de>
 * @version      $Id:$
 * @ilCtrl_Calls ilShopGUI: ilPageObjectGUI
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

//	private $form = null;
	public $genSet = null;

	public $cur_ref_id = null;

	public function __construct($_post = '')
	{
		parent::__construct();

		global $ilCtrl;

		$this->cur_ref_id = (int)$_GET['ref_id'];
		$this->cmd        = $ilCtrl->getCmd();

		// set filter settings
		$this->setType($_SESSION['shop_content']['type']);
		$this->setString($_SESSION['shop_content']['text']);
		$this->setTopicId($_POST['filter_topic_id']);

		// set sorting
		$this->setSortingTypeTopics($_SESSION['shop_content']['order_topics_sorting_type']);
		$this->setSortingDirectionTopics($_SESSION['shop_content']['shop_topics_sorting_direction']);
#		$this->setSortField($_SESSION['shop_content']['shop_order_field']);
		$this->setSortField($_POST['order_field']);
		$this->setSortDirection($_SESSION['shop_content']['shop_order_direction']);

		$this->genSet = ilPaymentSettings::_getInstance();
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
		if($ilUser->isPasswordChangeDemanded() || $ilUser->isPasswordExpired())
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		$next_class = $this->ctrl->getNextClass($this);

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
				switch($this->cmd)
				{
					case 'firstpage':
						$this->clearFilter();

						if(!$this->genSet->get('show_general_filter')
							&& !$this->genSet->get('show_topics_filter')
							&& !$this->genSet->get('show_shop_explorer')
						)
						{
							$cmd = 'performSearch';
						}
						else
						{

							if(ilPaymentSettings::useShopSpecials() == true)
							{
								$cmd = 'showSpecialContent';
							}
							else
							{
								$cmd = 'performSearch';
							}
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

				if($this->cmd != 'firstpage' && (isset($_GET['ref_id']) || $this->cmd == 'showTree')) #&& $_GET['ref_id'] != ROOT_FOLDER_ID )				
				{
					$obj_type  = ilObject::_lookupType(ilObject::_lookupObjId($this->cur_ref_id));
					$container = array("root", "cat", 'catr', "grp", "icrs", "crs", 'crsr', 'rcrs');

					if(in_array($obj_type, $container))
					{
						$cmd = 'showContainerContent';
					}
					else
					{
						$cmd = 'performSearch';
					}
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

		#return $this->performSearch();
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
		$this->setSortDirection('asc');

		$this->performSearch();

		return true;
	}

	public function showShopExplorer()
	{
		global $ilCtrl, $tree, $lng;

		$ilCtrl->setParameter($this, "active_node", $_GET["active_node"]);
		$shop_explorer_tpl = new ilTemplate('tpl.shop_explorer.html', true, true, 'Services/Payment');

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
		$shop_explorer_tpl->setVariable("EXPLORER", $output);
		$ilCtrl->setParameter($this, "repexpand", $_GET["repexpand"]);
		
		global $tpl;
		$tpl->setLeftContent($shop_explorer_tpl->get());
	}

	public function performSearch($oResult = null)
	{
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
			#ilUtil::sendInfo($this->lng->txt('payment_shop_not_objects_found'));
			$this->tpl->setVariable('ERROR', $this->lng->txt('payment_shop_not_objects_found'));

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

	//showSpecialContent
	public function showSpecialContent()
	{
		global $ilUser, $rbacreview, $ilToolbar;

		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		$this->tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());

		include_once './Services/Payment/classes/class.ilPaymentObject.php';
		#$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');

		$pobjects = ilPaymentObject::_getSpecialObjects();
		if(count($pobjects))
		{
			foreach($pobjects as $result)
			{
				$obj_id      = ilObject::_lookupObjId($result['ref_id']);
				$title       = ilObject::_lookupTitle($obj_id);
				$description = ilObject::_lookupDescription($obj_id);
				$type        = ilObject::_lookupType($obj_id);

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

		$show_general_filter = $this->oGeneralSettings->get('show_general_filter');
		$show_topics_filter  = $this->oGeneralSettings->get('show_topics_filter');
		$show_shop_explorer  = $this->oGeneralSettings->get('show_shop_explorer');

		if($show_general_filter)
		{
			$g_filter_html = $this->showGeneralFilter(count($search_result_presentation));
			$this->tpl->setVariable('FORM', $g_filter_html);
		}
		if($show_topics_filter)
		{
			$this->showTopicsFilter(count($search_result_presentation));
		}
		if($show_shop_explorer)
		{
			$this->showShopExplorer();
		}
	}

	public function showShopContent($oResult)
	{
		global $ilUser, $rbacreview, $ilToolbar;

		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');
		if(!count($oResult->getResults()))
		{
			$this->tpl->setVariable('ERROR', $this->lng->txt('payment_shop_not_objects_found'));
			#$this->tpl->setVariable('ERROR',ilUtil::sendInfo($this->lng->txt('payment_shop_not_objects_found')));
		}

		$show_general_filter = $this->oGeneralSettings->get('show_general_filter');
		$show_topics_filter  = $this->oGeneralSettings->get('show_topics_filter');
		$show_shop_explorer  = $this->oGeneralSettings->get('show_shop_explorer');

		if($show_general_filter)
		{
			$g_filter_html = $this->showGeneralFilter(count($oResult->getResults()));
			$this->tpl->setVariable('FORM', $g_filter_html);
		}
		if($show_topics_filter)
		{
			$this->showTopicsFilter(count($oResult->getResults()));
		}
		if($show_shop_explorer)
		{
			$this->showShopExplorer();
		}
	}

	//showContainerContent
	/*
	 * show buyable "sub"-objects of containers  (cat, catr, crs, grp, ...)
	 */
	public function showContainerContent()
	{
		global $ilUser, $rbacreview, $ilToolbar;

		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		}

		include_once './Services/Payment/classes/class.ilPaymentObject.php';

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

		$shop_content_tpl = new ilTemplate('tpl.shop_content.html', true, true,'Services/Payment');
		$shop_content_tpl->setVariable('PAGE_CONTENT', $this->getPageHTML());

		include_once 'Services/Payment/classes/class.ilShopResultPresentationGUI.php';
		$search_result_presentation = new ilShopResultPresentationGUI($presentation_results);
		$search_result_presentation->setSortField(strtolower(trim($this->getSortField())));
		$search_result_presentation->setSortDirection(trim($this->getSortDirection()));

		$html = $search_result_presentation->showSpecials();

		$shop_content_tpl->setVariable('RESULTS', $html);

		$show_general_filter = $this->oGeneralSettings->get('show_general_filter');
		$show_topics_filter  = $this->oGeneralSettings->get('show_topics_filter');
		$show_shop_explorer  = $this->oGeneralSettings->get('show_shop_explorer');

		if($show_general_filter)
		{
			$g_filter_html = $this->showGeneralFilter(count($search_result_presentation));
			$shop_content_tpl->setVariable('FORM', $g_filter_html);
		}
		if($show_topics_filter)
		{
			$this->showTopicsFilter(count($search_result_presentation));
		}
		if($show_shop_explorer)
		{
			$this->showShopExplorer();
		}
		global $tpl;
		$tpl->setContent($shop_content_tpl->parse());
	}

	public function showGeneralFilter($a_count_result = 0)
	{
		global $ilUser;

		if(!$_POST['show_filter'] && $_POST['updateView'] == '1')
		{
			$this->resetFilter();
		}
		else
			if($_POST['updateView'] == 1)
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
			}


		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		// FILTER FORM
		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('formular');
		$filter_form->setTableWidth('100 %');

		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'), 'show_filter');
		$o_hide_check->setValue(1);
		$o_hide_check->setChecked($_SESSION['content_filter']['show_filter'] ? 1 : 0);

		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);

		$o_filter      = new ilSelectInputGUI();
		$filter_option = array(
			'title'    => $this->lng->txt('title'),
			'author'   => $this->lng->txt('author'),
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

		#if(count($oResult->getResults()))
		if($a_count_result)
		{
			$objects = (bool)$this->oGeneralSettings->get('objects_allow_custom_sorting');
			if($objects)
			{
				// sorting form
				$allow_objects_option = array(
					'title'  => $this->lng->txt('title'),
					'author' => $this->lng->txt('author'),
					'price'  => $this->lng->txt('price_a')
				);
				$o_allow_objects      = new ilSelectInputGUI();
				$o_allow_objects->setTitle($this->lng->txt('sort_by'));
				$o_allow_objects->setOptions($allow_objects_option);
				$o_allow_objects->setValue($this->getSortField());
				$o_allow_objects->setPostVar('order_field'); //objects_sorting_type
				$o_hide_check->addSubItem($o_allow_objects);

				$direction_option = array(
					'asc'  => $this->lng->txt('sort_asc'),
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
					ilShopTopics::TOPICS_SORT_BY_TITLE      => $this->lng->txt('sort_topics_by_title'),
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
					'asc'  => $this->lng->txt('sort_asc'),
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

		return $filter_form->getHTML();
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
			$objects = (bool)$this->oGeneralSettings->get('objects_allow_custom_sorting');
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

			$topics = (bool)$this->oGeneralSettings->get('topics_allow_custom_sorting');

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
//				$direction_option = array(
//					'asc' => $this->lng->txt('sort_asc'),
//					'desc' => $this->lng->txt('sort_desc')
//				);
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
