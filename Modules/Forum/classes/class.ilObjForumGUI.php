<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Modules/Forum/classes/class.ilForumProperties.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Modules/Forum/classes/class.ilForumPost.php';
require_once 'Modules/Forum/classes/class.ilForum.php';
require_once 'Modules/Forum/classes/class.ilForumTopic.php';
require_once 'Services/RTE/classes/class.ilRTE.php';
require_once 'Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';
require_once 'Modules/Forum/classes/class.ilForumMailNotification.php';
require_once 'Services/UIComponent/SplitButton/classes/class.ilSplitButtonGUI.php';

/**
 * Class ilObjForumGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * $Id$
 *
 * @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjForumGUI: ilColumnGUI, ilPublicUserProfileGUI, ilForumModeratorsGUI, ilRepositoryObjectSearchGUI
 * @ilCtrl_Calls ilObjForumGUI: ilObjectCopyGUI, ilExportGUI, ilCommonActionDispatcherGUI, ilRatingGUI
 *
 * @ingroup ModulesForum
 */
class ilObjForumGUI extends ilObjectGUI implements ilDesktopItemHandling
{
	/**
	 * @var ilForumProperties
	 */
	public $objProperties;

	/**
	 * @var ilForumTopic
	 */
	private $objCurrentTopic;

	/**
	 * @var ilForumPost
	 */
	private $objCurrentPost;
	
	/**
	 * @var int
	 */
	private $display_confirm_post_activation = 0;

	/**
	 * @var bool
	 */
	private $is_moderator = false;

	/**
	 * @var ilPropertyFormGUI
	 */
	private $create_form_gui;

	/**
	 * @var ilPropertyFormGUI
	 */
	private $create_topic_form_gui;
	
	/**
	 * @var ilPropertyFormGUI
	 */
	private $replyEditForm;

	/**
	 * @var ilPropertyFormGUI
	 */
	private $notificationSettingsForm;

	/**
	 * @var bool
	 */
	private $hideToolbar = false;
	
	/**
	 * @var null|string
	 */
	private $forum_overview_setting = null;
	
	/**
	 * @var ilObjForum
	 */
	public $object;
	
	/**
	 * @var ILIAS
	 */
	public $ilias;
	
	/**
	 * @var array
	 */
	private $forumObjects;
	
	/**
	 * @var string
	 */
	private $confirmation_gui_html = '';
	
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilAccess ilAccessHandler
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		global $ilCtrl, $ilAccess, $ilObjDataCache;

		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($this, array('ref_id', 'cmdClass'));

		$this->type = 'frm';
		parent::__construct($a_data, $a_id, $a_call_by_reference, false);

		$this->lng->loadLanguageModule('forum');

		$this->initSessionStorage();

		// Forum properties
		$this->objProperties = ilForumProperties::getInstance($ilObjDataCache->lookupObjId($_GET['ref_id']));
		
		// Stored due to performance issues
		$this->is_moderator = $ilAccess->checkAccess('moderate_frm', '', $_GET['ref_id']);

		// Model of current topic/thread
		$this->objCurrentTopic = new ilForumTopic((int) $_GET['thr_pk'], $this->is_moderator);

		// Model of current post
		$this->objCurrentPost = new ilForumPost((int) $_GET['pos_pk'], $this->is_moderator);

		$frma_set = new ilSetting('frma');
		$this->forum_overview_setting = $frma_set->get('forum_overview');
	}

	protected function initSessionStorage()
	{
		$sess = ilSession::get('frm');
		if(!is_array($sess))
		{
			$sess = array();
			ilSession::set('frm', $sess);
		}

		if(isset($_GET['thr_fk']) && !is_array($sess[(int)$_GET['thr_fk']]))
		{
			$sess[(int)$_GET['thr_fk']] = array();
			ilSession::set('frm', $sess);
		}
	}

	/**
	 * @return bool
	 */
	private function isHierarchicalView()
	{
		return (
			$_SESSION['viewmode'] == 'answers' ||
			$_SESSION['viewmode'] == ilForumProperties::VIEW_TREE
		) || !(
			$_SESSION['viewmode'] == 'date' ||
			$_SESSION['viewmode'] == ilForumProperties::VIEW_DATE
		);
	}

	/**
	 * @return bool
	 */
	private function isTopLevelReplyCommand()
	{
		return in_array(
			strtolower($this->ctrl->getCmd()),
			array_map('strtolower', array('createToLevelPost', 'quoteTopLevelPost', 'saveTopLevelPost'))
		);
	}

	public function executeCommand()
	{
		/**
		 * @var $ilNavigationHistory ilNavigationHistory
		 * @var $ilAccess ilAccessHandler
		 * @var $ilCtrl ilCtrl
		 * @var $ilTabs ilTabsGUI
		 * @var $ilErr  ilErrorHandling
		 * @var $ilUser ilObjUser
		 */
		global $ilNavigationHistory, $ilAccess, $ilCtrl, $ilTabs, $ilErr, $ilUser;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$exclude_cmds = array(
			'viewThread', 'markPostUnread','markPostRead', 'showThreadNotification',
			'performPostActivation', 
			'askForPostActivation', 'askForPostDeactivation',
			'toggleThreadNotification', 'toggleThreadNotificationTab',
			'toggleStickiness', 'cancelPost', 'savePost', 'saveTopLevelPost', 'createToLevelPost', 'quoteTopLevelPost', 'quotePost', 'getQuotationHTMLAsynch'
		);

		if(!in_array($cmd, $exclude_cmds))
		{
			$this->prepareOutput();
		}

		// add entry to navigation history
		if(!$this->getCreationMode() && !$ilCtrl->isAsynch() && $ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$ilNavigationHistory->addItem($_GET['ref_id'],
				'ilias.php?baseClass=ilRepositoryGUI&amp;cmd=showThreads&amp;ref_id='.$_GET['ref_id'], 'frm');
		}
		
		switch ($next_class)
		{
			case 'ilrepositoryobjectsearchgui':
				$this->addHeaderAction();
				$this->setSideBlocks();
				$ilTabs->setTabActive("forums_threads");
				$ilCtrl->setReturn($this,'view');
				include_once './Services/Search/classes/class.ilRepositoryObjectSearchGUI.php';
				$search_gui = new ilRepositoryObjectSearchGUI(
					$this->object->getRefId(),
					$this,
					'view'
				);
				$ilCtrl->forwardCommand($search_gui);
				break;

			case 'ilpermissiongui':
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilforumexportgui':
				require_once 'Modules/Forum/classes/class.ilForumExportGUI.php';
				$fex_gui = new ilForumExportGUI($this);
				$this->ctrl->forwardCommand($fex_gui);
				exit();
				break;
			
			case 'ilforummoderatorsgui':
				require_once 'Modules/Forum/classes/class.ilForumModeratorsGUI.php';
				$fm_gui = new ilForumModeratorsGUI($this);
				$this->ctrl->forwardCommand($fm_gui);
				break;
				
			case 'ilinfoscreengui':
				$this->infoScreen();
				break;

			case 'ilcolumngui':
				$this->showThreadsObject();
				break;

			case 'ilpublicuserprofilegui':				
				include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI((int)$_GET['user']);
				$add = $this->getUserProfileAdditional((int)$_GET['ref_id'], (int)$_GET['user']);
				$profile_gui->setAdditional($add);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				$this->tpl->setContent($ret);
				break;
				
			case 'ilobjectcopygui':
				include_once 'Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('frm');
				$this->ctrl->forwardCommand($cp);
				break;

			case 'ilexportgui':
				$ilTabs->setTabActive('export');
				include_once 'Services/Export/classes/class.ilExportGUI.php';
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;

			case "ilratinggui":
				if(!$this->objProperties->isIsThreadRatingEnabled() || $ilUser->isAnonymous())
				{
					$ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
				}

				require_once 'Services/Rating/classes/class.ilRatingGUI.php';
				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->object->getId(), $this->object->getType(), $this->objCurrentTopic->getId(), 'thread');

				$ilCtrl->setParameter($this, 'thr_pk', (int)$_GET['thr_pk']);
				$this->ctrl->forwardCommand($rating_gui);

				$avg = ilRating::getOverallRatingForObject($this->object->getId(), $this->object->getType(), (int)$_GET['thr_pk'], 'thread');
				$this->objCurrentTopic->setAverageRating($avg['avg']);
				$this->objCurrentTopic->update();

				$ilCtrl->redirect($this, "showThreads");
				break;
			
			case 'ilcommonactiondispatchergui':
				include_once 'Services/Object/classes/class.ilCommonActionDispatcherGUI.php';
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			default:
				// alex, 11 Jan 2011:
				// I inserted this workaround due to bug report 6971.
				// In general the command handling is quite obscure here.
				// The form action of the table should be filled
				// with $ilCtrl->getFormAction(..) not with $ilCtrl->getLinkTarget(..)
				// Commands should be determined with $ilCtrl->getCmd() not
				// with accessing $_POST['selected_cmd'], since this is internal
				// of ilTable2GUI/ilCtrl and may change.
				if(isset($_POST['select_cmd2']))
				{
					$_POST['selected_cmd'] = $_POST["selected_cmd2"];
				}

				if(isset($_POST['selected_cmd']) && $_POST['selected_cmd'] != null)
				{
					$member_cmd = array('enableAdminForceNoti', 'disableAdminForceNoti', 'enableHideUserToggleNoti', 'disableHideUserToggleNoti');
					in_array($_POST['selected_cmd'], $member_cmd) ? $cmd = $_POST['selected_cmd'] : $cmd = 'performThreadsAction';
				}
				else if(!$cmd && !$_POST['selected_cmd'] )
				{
					$cmd = 'showThreads';
				}

				$cmd .= 'Object';
				$this->$cmd();

				break;
		}
		
		// suppress for topic level
		if($cmd != 'viewThreadObject' && $cmd != 'showUserObject')
		{
			$this->addHeaderAction();
		}
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		$this->settingsTabs();

		//sorting for threads
		$cb_sort = new ilCheckboxInputGUI($this->lng->txt('sorting_manual_sticky'),	'thread_sorting');
		$cb_sort->setValue('1');
		$cb_sort->setInfo($this->lng->txt('sticky_threads_always_on_top'));
		$a_form->addItem($cb_sort);

		// sorting for postings
		$rg_pro = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'default_view');

		$rg_pro->addOption(new ilRadioOption($this->lng->txt('sort_by_posts'), ilForumProperties::VIEW_TREE));
		$rg_sort_by_date = new ilRadioOption($this->lng->txt('sort_by_date'), ilForumProperties::VIEW_DATE);
		$rg_pro->addOption($rg_sort_by_date);

		$view_direction_group_gui = new ilRadioGroupInputGUI('', 'default_view_sort_dir');	
		$view_desc = new ilRadioOption($this->lng->txt('descending_order'), ilForumProperties::VIEW_DATE_DESC);
		$view_asc = new ilRadioOption($this->lng->txt('ascending_order'), ilForumProperties::VIEW_DATE_ASC);
		$view_direction_group_gui->addOption($view_desc);
		$view_direction_group_gui->addOption($view_asc);
	
		$rg_sort_by_date->addSubItem($view_direction_group_gui);
		$a_form->addItem($rg_pro);

		if($ilSetting->get('enable_anonymous_fora') || $this->objProperties->isAnonymized())
		{
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'),	'anonymized');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
			$a_form->addItem($cb_prop);
		}
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('mark_moderator_posts'), 'mark_mod_posts');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('mark_moderator_posts_desc'));
		$a_form->addItem($cb_prop);
		
		if($ilSetting->get('enable_fora_statistics', false))
		{
			$cb_prop = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
			$cb_prop->setValue('1');
			$cb_prop->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
			$a_form->addItem($cb_prop);
		}

		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('post_activation_desc'));
		$a_form->addItem($cb_prop);

		$frm_subject = new ilRadioGroupInputGUI($this->lng->txt('frm_subject_setting'), 'subject_setting');
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('preset_subject'), 'preset_subject'));
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('add_re_to_subject'), 'add_re_to_subject'));
		$frm_subject->addOption(new ilRadioOption($this->lng->txt('empty_subject'), 'empty_subject'));

		$a_form->addItem($frm_subject);

		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('enable_thread_ratings'), 'thread_rating');
		$cb_prop->setValue(1);
		$cb_prop->setInfo($this->lng->txt('enable_thread_ratings_info'));
		$a_form->addItem($cb_prop);

		if(!ilForumProperties::isFileUploadGloballyAllowed())
		{
			$frm_upload = new ilCheckboxInputGUI($this->lng->txt('file_upload_allowed'), 'file_upload_allowed');
			$frm_upload->setValue(1);
			$frm_upload->setInfo($this->lng->txt('allow_file_upload_desc'));
			$a_form->addItem($frm_upload);
		}
	}

	protected function getEditFormCustomValues(Array &$a_values)
	{
		$a_values["desc"] = $this->object->getLongDescription();
		$a_values['default_view'] = $this->objProperties->getDefaultView();
		$a_values['anonymized'] = $this->objProperties->isAnonymized();
		$a_values['statistics_enabled'] = $this->objProperties->isStatisticEnabled();
		$a_values['post_activation'] = $this->objProperties->isPostActivationEnabled();
		$a_values['subject_setting'] = $this->objProperties->getSubjectSetting();
		$a_values['mark_mod_posts'] = $this->objProperties->getMarkModeratorPosts();
		$a_values['thread_sorting'] = $this->objProperties->getThreadSorting();
		$a_values['thread_rating'] = $this->objProperties->isIsThreadRatingEnabled();
		
		$default_view = 
			in_array((int)$this->objProperties->getDefaultView(), array(ilForumProperties::VIEW_DATE_ASC, ilForumProperties::VIEW_DATE_DESC)) 
			? ilForumProperties::VIEW_DATE 
			: ilForumProperties::VIEW_TREE;
		$a_values['default_view'] = $default_view;
		
		$default_view_sort_dir = 	
			(int)$this->objProperties->getDefaultView() != (int)ilForumProperties::VIEW_TREE 
			? (int)$this->objProperties->getDefaultView() 
			: ilForumProperties::VIEW_DATE_ASC;
		
		$a_values['default_view_sort_dir'] = $default_view_sort_dir;
		$a_values['file_upload_allowed']   = (bool)$this->objProperties->getFileUploadAllowed();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;
		
		$view = (int)$_POST['default_view'];
		if($view == ilForumProperties::VIEW_DATE)
		{
			$view = ilForumProperties::VIEW_DATE_ASC;
			if((int)$_POST['default_view_sort_dir'] == ilForumProperties::VIEW_DATE_DESC)
			{
				$view = ilForumProperties::VIEW_DATE_DESC;
			}
		}
		$this->objProperties->setDefaultView($view);

		// BUGFIX FOR 11271
		if(isset($_SESSION['viewmode']))
		{
			$_SESSION['viewmode'] = $view;
		}

		if($ilSetting->get('enable_anonymous_fora') || $this->objProperties->isAnonymized())
		{
			$this->objProperties->setAnonymisation((int) $a_form->getInput('anonymized'));
		}
		if($ilSetting->get('enable_fora_statistics', false))
		{
			$this->objProperties->setStatisticsStatus((int) $a_form->getInput('statistics_enabled'));
		}
		$this->objProperties->setPostActivation((int) $a_form->getInput('post_activation'));
		$this->objProperties->setSubjectSetting( $a_form->getInput('subject_setting'));
		$this->objProperties->setMarkModeratorPosts((int) $a_form->getInput('mark_mod_posts'));
		$this->objProperties->setThreadSorting((int)$a_form->getInput('thread_sorting'));
		$this->objProperties->setIsThreadRatingEnabled((bool)$a_form->getInput('thread_rating'));
		if(!ilForumProperties::isFileUploadGloballyAllowed())
		{
			$this->objProperties->setFileUploadAllowed((bool)$a_form->getInput('file_upload_allowed'));
		}

		$this->objProperties->update();
	}

	/**
	 * @param  int $a_thread_id
	 * @return ilPropertyFormGUI
	 */
	private function getThreadEditingForm($a_thread_id)
	{
		$form = new ilPropertyFormGUI();
		$this->ctrl->setParameter($this, 'thr_pk', $a_thread_id);
		$form->setFormAction($this->ctrl->getFormAction($this, 'updateThread'));

		$ti_prop = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$ti_prop->setRequired(true);
		$ti_prop->setMaxLength(255);
		$ti_prop->setSize(50);
		$form->addItem($ti_prop);

		$form->addCommandButton('updateThread', $this->lng->txt('save'));
		$form->addCommandButton('showThreads', $this->lng->txt('cancel'));
		
		return $form;
	}

	/**
	 * @param                   $a_thread_id
	 * @param ilPropertyFormGUI $form
	 */
	public function editThreadObject($a_thread_id, ilPropertyFormGUI $form = null)
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 */
		global $ilTabs;

		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$ilTabs->setTabActive('forums_threads');

		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getThreadEditingForm($a_thread_id);
			$form->setValuesByArray(array(
				'title' => ilForumTopic::_lookupTitle($a_thread_id)
			));
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * 
	 */
	public function updateThreadObject()
	{
		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		if(!$this->objCurrentTopic->getId())
		{
			$this->showThreadsObject();
			return;
		}

		$forum_id = ilObjForum::lookupForumIdByObjId($this->object->getId());
		if($this->objCurrentTopic->getForumId() != $forum_id)
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$form = $this->getThreadEditingForm($this->objCurrentTopic->getId());
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->editThreadObject($this->objCurrentTopic->getId(), $form);
			return;
		}

		$this->objCurrentTopic->setSubject($form->getInput('title'));
		$this->objCurrentTopic->updateThreadTitle();

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->showThreadsObject();
	}

	public function markAllReadObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$this->object->markAllThreadsRead($ilUser->getId());

		ilUtil::sendInfo($this->lng->txt('forums_all_threads_marked_read'));

		$this->showThreadsObject();
	}

	public function showThreadsObject()
	{
		$this->getSubTabs('showThreads');
		$this->setSideBlocks();
		$this->getCenterColumnHTML();
	}
	public function sortThreadsObject()
	{
		$this->getSubTabs('sortThreads');
		$this->setSideBlocks();
		$this->getCenterColumnHTML(true);
	}


	public function getSubTabs($subtab = 'showThreads')
	{
		global $ilTabs;

		if($this->objProperties->getThreadSorting() == 1 && $this->is_moderator)
		{
			$ilTabs->addSubTabTarget('show', $this->ctrl->getLinkTarget($this, 'showThreads'), 'showThreads', get_class($this), '', $subtab=='showThreads'? true : false );
			$ilTabs->addSubTabTarget('sorting_header', $this->ctrl->getLinkTarget($this, 'sortThreads'), 'sortThreads', get_class($this), '', $subtab=='sortThreads'? true : false );
		}
	}
	public function getContent()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilAccess ilAccessHandler
		 * @var $lng ilLanguage
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilUser, $ilAccess, $lng, $ilToolbar;

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$cmd = $this->ctrl->getCmd();
		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setMDB2Wherecondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_liste.html',	'Modules/Forum');

		if((int)strlen($this->confirmation_gui_html))
		{
			$this->tpl->setVariable('CONFIRMATION_GUI', $this->confirmation_gui_html);
		}

		// Create topic button
		if($ilAccess->checkAccess('add_thread', '', $this->object->getRefId()) && !$this->hideToolbar())
		{
			require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
			$btn = ilLinkButton::getInstance();
			$btn->setUrl($this->ctrl->getLinkTarget($this, 'createThread'));
			$btn->setCaption('forums_new_thread');
			$ilToolbar->addStickyItem($btn);
		}

		// Mark all topics as read button
		include_once 'Services/Accessibility/classes/class.ilAccessKeyGUI.php';
		if($ilUser->getId() != ANONYMOUS_USER_ID && !(int)strlen($this->confirmation_gui_html))
		{
			$ilToolbar->addButton(
				$this->lng->txt('forums_mark_read'),
				$this->ctrl->getLinkTarget($this, 'markAllRead'),
				'',
				ilAccessKey::MARK_ALL_READ
			);
			$this->ctrl->clearParameters($this);
		}

		// Import information: Topic (variable $topicData) means frm object, not thread
		$topicData = $frm->getOneTopic();
		if($topicData)
		{
			// Visit-Counter
			$frm->setDbTable('frm_data');
			$frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topicData['top_pk']));
			$frm->updateVisits($topicData['top_pk']);

			include_once 'Modules/Forum/classes/class.ilForumTopicTableGUI.php';
			if(!in_array($cmd, array('showThreads', 'sortThreads') ))
			{
				$cmd = 'showThreads';
			}

			$tbl = new ilForumTopicTableGUI($this, $cmd, '', (int) $_GET['ref_id'], $topicData, $this->is_moderator, $this->forum_overview_setting);
			$tbl->init();
			$tbl->setMapper($frm)->fetchData();
			$this->tpl->setVariable('THREADS_TABLE', $tbl->getHTML());
		}

		// Permanent link
		include_once 'Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
		$permalink = new ilPermanentLinkGUI('frm', $this->object->getRefId());
		$this->tpl->setVariable('PRMLINK', $permalink->getHTML());
	}

	/**
	 * @param string $object_type
	 */
	private function initForumCreateForm($object_type)
	{
		$this->create_form_gui = new ilPropertyFormGUI();
		$this->create_form_gui->setTableWidth('600px');
		
		$this->create_form_gui->setTitle($this->lng->txt('frm_new'));
		$this->create_form_gui->setTitleIcon(ilUtil::getImagePath('icon_frm.svg'));
		
		// form action
		$this->ctrl->setParameter($this, 'new_type', $object_type);
		$this->create_form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));		
		
		// title
		$title_gui = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$title_gui->setSize(min(40, ilObject::TITLE_LENGTH));
		$title_gui->setMaxLength(ilObject::TITLE_LENGTH);
		$this->create_form_gui->addItem($title_gui);
		
		// description
		$description_gui = new ilTextAreaInputGUI($this->lng->txt('desc'), 'desc');
		$description_gui->setCols(40);
		$description_gui->setRows(2);
		$this->create_form_gui->addItem($description_gui);
		
		// view sorting threads
		$sort_man = new ilCheckboxInputGUI($this->lng->txt('sorting_manual_sticky'), 'thread_sorting');
		$sort_man->setInfo($this->lng->txt('sticky_threads_always_on_top'));
		$sort_man->setValue(1);
		$this->create_form_gui->addItem($sort_man);

		// view
		$view_group_gui = new ilRadioGroupInputGUI($this->lng->txt('frm_default_view'), 'sort');
			$view_hir = new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('answers'), ilForumProperties::VIEW_TREE);
		$view_group_gui->addOption($view_hir);
			$view_dat = new ilRadioOption($this->lng->txt('order_by').' '.$this->lng->txt('date'), ilForumProperties::VIEW_DATE);
		$view_group_gui->addOption($view_dat);
		$this->create_form_gui->addItem($view_group_gui);
		$view_direction_group_gui = new ilRadioGroupInputGUI('', 'default_view_sort_dir');		
		$view_desc = new ilRadioOption($this->lng->txt('frm_post_sort_desc'), ilForumProperties::VIEW_DATE_DESC);
		$view_direction_group_gui->addOption($view_desc);
		$view_asc = new ilRadioOption($this->lng->txt('frm_post_sort_asc'), ilForumProperties::VIEW_DATE_ASC);
		$view_direction_group_gui->addOption($view_asc);
		$view_dat->addSubItem($view_direction_group_gui);
		
		// anonymized or not
		$anonymize_gui = new ilCheckboxInputGUI($this->lng->txt('frm_anonymous_posting'), 'anonymized');
		$anonymize_gui->setInfo($this->lng->txt('frm_anonymous_posting_desc'));
		$anonymize_gui->setValue(1);

		if($this->ilias->getSetting('enable_anonymous_fora', false))
			$anonymize_gui->setDisabled(true);
		$this->create_form_gui->addItem($anonymize_gui);
		
		// statistics enabled or not
		$statistics_gui = new ilCheckboxInputGUI($this->lng->txt('frm_statistics_enabled'), 'statistics_enabled');
		$statistics_gui->setInfo($this->lng->txt('frm_statistics_enabled_desc'));
		$statistics_gui->setValue(1);
		if(!$this->ilias->getSetting('enable_fora_statistics', false))
			$statistics_gui->setDisabled(true);
		$this->create_form_gui->addItem($statistics_gui);
		
		$cb_prop = new ilCheckboxInputGUI($this->lng->txt('activate_new_posts'), 'post_activation');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($this->lng->txt('post_activation_desc'));
		$this->create_form_gui->addItem($cb_prop);
		
		$this->create_form_gui->addCommandButton('save', $this->lng->txt('save'));
		$this->create_form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	
	public function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt('msg_cancel'), true);
		ilUtil::redirect('ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&ref_id='.$_GET['ref_id']);
	}

	/**
	 * @param ilObject|ilObjForum $forumObj
	 */
	protected function afterSave(ilObject $forumObj)
	{
		ilUtil::sendSuccess($this->lng->txt('frm_added'), true);
		$this->ctrl->setParameter($this, 'ref_id', $forumObj->getRefId());
		ilUtil::redirect($this->ctrl->getLinkTarget($this, 'createThread', '', false, false));
	}

	protected function getTabs()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilHelp ilHelpGUI
		 * @var $ilCtrl ilCtrl
		 */
		global $ilAccess, $ilHelp, $ilCtrl;
		
		$ilHelp->setScreenIdComponent("frm");

		$this->ctrl->setParameter($this, 'ref_id', $this->ref_id);

		$active = array(
			'', 'showThreads', 'view', 'markAllRead', 
			'enableForumNotification', 'disableForumNotification', 'moveThreads', 'performMoveThreads',
			'cancelMoveThreads', 'performThreadsAction', 'createThread', 'addThread',
			'showUser', 'confirmDeleteThreads',
			'merge','mergeThreads', 'cancelMergeThreads', 'performMergeThreads'
		);

		(in_array($ilCtrl->getCmd(), $active)) ? $force_active = true : $force_active = false;
		$this->tabs_gui->addTarget('forums_threads', $this->ctrl->getLinkTarget($this,'showThreads'), $ilCtrl->getCmd(), get_class($this), '', $force_active);

		// info tab
		if($ilAccess->checkAccess('visible', '', $this->ref_id))
		{
			$force_active = ($this->ctrl->getNextClass() == 'ilinfoscreengui' || strtolower($_GET['cmdClass']) == 'ilnotegui') ? true : false;
			$this->tabs_gui->addTarget('info_short',
				 $this->ctrl->getLinkTargetByClass(
				 array('ilobjforumgui', 'ilinfoscreengui'), 'showSummary'),
				 array('showSummary', 'infoScreen'),
				 '', '', $force_active);
		}
		
		if($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$force_active = ($ilCtrl->getCmd() == 'edit') ? true	: false;
			$this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $force_active);
		}
		
		if($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$this->tabs_gui->addTarget('frm_moderators', $this->ctrl->getLinkTargetByClass('ilForumModeratorsGUI', 'showModerators'), 'showModerators', get_class($this));			
		}

		if($this->ilias->getSetting('enable_fora_statistics', false) &&
		   ($this->objProperties->isStatisticEnabled() || $ilAccess->checkAccess('write', '', $this->ref_id))) 
		{
			$force_active = ($ilCtrl->getCmd() == 'showStatistics') ? true	: false;
			$this->tabs_gui->addTarget('frm_statistics', $this->ctrl->getLinkTarget($this, 'showStatistics'), 'showStatistics', get_class($this), '', $force_active); //false
		}

		if($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$this->tabs_gui->addTarget('export', $this->ctrl->getLinkTargetByClass('ilexportgui', ''), '', 'ilexportgui');
		}

		if($ilAccess->checkAccess('edit_permission', '', $this->ref_id))
		{
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');							
		}
	}

	public function settingsTabs()
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 * @var $ilAccess ilAccessHandler
		 * @var $tree ilTree
		 */
		global $ilTabs, $ilAccess, $tree;

		$ilTabs->setTabActive('settings');
		$ilTabs->addSubTabTarget('basic_settings', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $_GET['cmd']=='edit'? true : false );

		// notification tab
		if($this->ilias->getSetting('forum_notification') > 0)
		{
			// check if there a parent-node is a grp or crs
			$grp_ref_id = $tree->checkForParentType($this->object->getRefId(), 'grp');
			$crs_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');
	
			if((int)$grp_ref_id > 0 || (int)$crs_ref_id > 0 )
			{
				#show member-tab for notification if forum-notification is enabled in administration
				if($ilAccess->checkAccess('write', '', $this->ref_id))
				{
					$mem_active = array('showMembers', 'forums_notification_settings');
					(in_array($_GET['cmd'],$mem_active)) ? $force_mem_active = true : $force_mem_active = false;
	
					$ilTabs->addSubTabTarget('notifications', $this->ctrl->getLinkTarget($this, 'showMembers'), $_GET['cmd'], get_class($this), '', $force_mem_active);
				}
			}
		}
		return true;
	}
	
	public function showStatisticsObject() 
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		global $ilAccess;
		
		/// if globally deactivated, skip!!! intrusion detected
		if(!$this->ilias->getSetting('enable_fora_statistics', false))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		// if no read access -> intrusion detected
		if(!$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		// if read access and statistics disabled -> intrusion detected
		if(!$ilAccess->checkAccess('read', '',  $_GET['ref_id']) && !$this->objProperties->isStatisticEnabled())
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}				
    			
		// if write access and statistics disabled -> ok, for forum admin
		if($ilAccess->checkAccess('write', '', $_GET['ref_id']) && 
		   !$this->objProperties->isStatisticEnabled())
		{
			ilUtil::sendInfo($this->lng->txt('frm_statistics_disabled_for_participants'));
		}
		
		$this->object->Forum->setForumId($this->object->getId());
		
		require_once 'Modules/Forum/classes/class.ilForumStatisticsTableGUI.php';
		
		$tbl = new ilForumStatisticsTableGUI($this, 'showStatistics');
		$tbl->setId('il_frm_statistic_table_'.(int) $_GET['ref_id']);
		$tbl->setTitle($this->lng->txt('statistic'), 'icon_usr.svg', $this->lng->txt('obj_'.$this->object->getType()));
		
		$data = $this->object->Forum->getUserStatistic($this->is_moderator);
		$result = array();
		$counter = 0;
		foreach($data as $row)
		{
			$result[$counter]['ranking'] = $row[0];
			$result[$counter]['login'] = $row[1];
			$result[$counter]['lastname'] = $row[2];
			$result[$counter]['firstname'] = $row[3];
			
			++$counter;
		}
		$tbl->setData($result);
				
		$this->tpl->setContent($tbl->getHTML());
	}
	
	public static function _goto($a_target, $a_thread = 0, $a_posting = 0)
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr ilErrorHandling
		 * @var $lng ilLanguage
		 */
		global $ilAccess, $ilErr, $lng;

		if($ilAccess->checkAccess('read', '', $a_target))
		{
			if($a_thread != 0)
			{				
				$objTopic = new ilForumTopic($a_thread);
				if ($objTopic->getFrmObjId() && 
					$objTopic->getFrmObjId() != ilObject::_lookupObjectId($a_target))
				{					
					$ref_ids = ilObject::_getAllReferences($objTopic->getFrmObjId());
					foreach($ref_ids as $ref_id)
					{
						if($ilAccess->checkAccess('read', '', $ref_id))
						{
							$new_ref_id = $ref_id;							
							break;
						}
					}
					
					if (isset($new_ref_id) && $new_ref_id != $a_target)
					{
						ilUtil::redirect(ILIAS_HTTP_PATH."/goto.php?target=frm_".$new_ref_id."_".$a_thread."_".$a_posting);
					}
				}			
	
				$_GET['ref_id'] = $a_target;
				$_GET['pos_pk'] = $a_posting;
				$_GET['thr_pk'] = $a_thread;
				$_GET['anchor'] = $a_posting;
				$_GET['cmdClass'] = 'ilObjForumGUI';
				$_GET['cmd'] = 'viewThread';
				$_GET['baseClass'] = 'ilRepositoryGUI';

				include_once('ilias.php');
				exit();
			}
			else
			{
			
				$_GET['ref_id'] = $a_target;
				$_GET['baseClass'] = 'ilRepositoryGUI';
				include_once('ilias.php');
				exit();
			}
		}
		else if($ilAccess->checkAccess('read', '', ROOT_FOLDER_ID))
		{
			$_GET['target'] = '';
			$_GET['ref_id'] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt('msg_no_perm_read_item'),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			$_GET['baseClass'] = 'ilRepositoryGUI';
			include('ilias.php');
			exit();
		}

		$ilErr->raiseError($lng->txt('msg_no_perm_read'), $ilErr->FATAL);
	}
	
	public function performDeleteThreadsObject()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		if(!$this->is_moderator)
		{		
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
			return $this->showThreadsObject();
		}
		
		if(!isset($_POST['thread_ids']) || !is_array($_POST['thread_ids']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
	 		return $this->showThreadsObject();
	 	}	

		require_once 'Modules/Forum/classes/class.ilForum.php';
		require_once 'Modules/Forum/classes/class.ilObjForum.php';

		$forumObj = new ilObjForum($_GET['ref_id']);
		
		$this->objProperties->setObjId($forumObj->getId());

		$frm = new ilForum();
		if(count($_POST['thread_ids']) > 1 )
		{
			$success_message = 'forums_threads_deleted';
		}
		else
		{
			$success_message = 'forums_thread_deleted';
		}

		foreach($_POST['thread_ids'] as $topic_id)
		{
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());

			$first_node = $frm->getFirstPostNode($topic_id);
			if((int)$first_node['pos_pk'])
			{
				$frm->deletePost($first_node['pos_pk']);
				ilUtil::sendInfo($lng->txt($success_message), true);
			}
		}
		$this->ctrl->redirect($this, 'showThreads');
	}
	
	public function confirmDeleteThreads()
	{
		/** 
		 * @var $lng ilLanguage
		 */
		global $lng;

		if(!isset($_POST['thread_ids']) || !is_array($_POST['thread_ids']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
	 		return $this->showThreadsObject();
	 	}
	 	
	 	if(!$this->is_moderator)
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
			return $this->showThreadsObject();
		}
	 	
	 	include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
		$c_gui = new ilConfirmationGUI();
		
		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteThreads'));
		$c_gui->setHeaderText($this->lng->txt('frm_sure_delete_threads'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteThreads');

		foreach((array)$_POST['thread_ids'] as $thread_id)
		{
			$c_gui->addItem('thread_ids[]', $thread_id, ilForumTopic::_lookupTitle($thread_id));
		}
		
		$this->confirmation_gui_html = $c_gui->getHTML();
		
		$this->hideToolbar(true);

		return $this->tpl->setContent($c_gui->getHTML());
	}

	public function prepareThreadScreen(ilObjForum $a_forum_obj)
	{
		/**
		 * @var $tpl ilTemplate
		 * @var $lng ilLanguage
		 * @var $ilTabs ilTabsGUI
		 * @var $ilHelp ilHelpGUI
		 */
		global $tpl, $lng, $ilTabs, $ilHelp;
		
		$ilHelp->setScreenIdComponent("frm");

		$tpl->getStandardTemplate();
		ilUtil::sendInfo();
		ilUtil::infoPanel();

		$tpl->setTitleIcon(ilObject::_getIcon("", "big", "frm"));

 		$ilTabs->setBackTarget($lng->txt('all_topics'),'ilias.php?baseClass=ilRepositoryGUI&amp;ref_id='.$_GET['ref_id']);

		// by answer view
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'viewmode', ilForumProperties::VIEW_TREE);
		$ilTabs->addTarget('sort_by_posts', $this->ctrl->getLinkTarget($this, 'viewThread'));
	
		// by date view
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'viewmode', ilForumProperties::VIEW_DATE);
		$ilTabs->addTarget('order_by_date',	$this->ctrl->getLinkTarget($this, 'viewThread'));

		$this->ctrl->clearParameters($this);

		if($this->isHierarchicalView())
		{
			$ilTabs->setTabActive('sort_by_posts');
		}
		else
		{
			$ilTabs->setTabActive('order_by_date');
		}

		/**
		 * @var $frm ilForum
		 */
		$frm = $a_forum_obj->Forum;
		$frm->setForumId($a_forum_obj->getId());
	}
	
	public function performPostActivationObject()
	{
		if($this->is_moderator)
		{
			$this->objCurrentPost->activatePost();
			ilUtil::sendInfo($this->lng->txt('forums_post_was_activated'), true);
		}
		
		$this->viewThreadObject();
	}

	public function askForPostActivationObject()
	{
		if($this->is_moderator)
		{
			$this->setDisplayConfirmPostActivation(true);
		}
		
		$this->viewThreadObject();
	}
	
	public function setDisplayConfirmPostActivation($status = 0)
	{
		$this->display_confirm_post_activation = $status;
	}
	
	public function displayConfirmPostActivation()
	{
		return $this->display_confirm_post_activation;
	}

	public function toggleThreadNotificationObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		if($this->objCurrentTopic->isNotificationEnabled($ilUser->getId()))
		{
			$this->objCurrentTopic->disableNotification($ilUser->getId());
			ilUtil::sendInfo($this->lng->txt('forums_notification_disabled'), true);
		}
		else
		{
			$this->objCurrentTopic->enableNotification($ilUser->getId());
			ilUtil::sendInfo($this->lng->txt('forums_notification_enabled'), true);
		}
		
		$this->viewThreadObject();
	}
	
	public function toggleStickinessObject()
	{
		if($this->is_moderator)
		{
			if($this->objCurrentTopic->isSticky())
			{
				$this->objCurrentTopic->unmakeSticky();	
			}
			else
			{
				$this->objCurrentTopic->makeSticky();
			}
		}
		
		$this->viewThreadObject();
	}

	public function cancelPostObject()
	{
		$_GET['action'] = '';
		$this->viewThreadObject();
	}

	public function getDeleteFormHTML()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		/** @var $form_tpl ilTemplate */
		$form_tpl = new ilTemplate('tpl.frm_delete_post_form.html', true, true, 'Modules/Forum');

		$form_tpl->setVariable('ANKER', $this->objCurrentPost->getId());
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('TXT_DELETE', $lng->txt('forums_info_delete_post'));
		$this->ctrl->setParameter($this, 'action', 'ready_delete');
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getLinkTarget($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('CANCEL_BUTTON', $lng->txt('cancel'));
		$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('confirm'));

		return $form_tpl->get(); 
	}

	public function getActivationFormHTML()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form_tpl = new ilTemplate('tpl.frm_activation_post_form.html', true, true, 'Modules/Forum');

		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPostActivation'));
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('TXT_ACT', $lng->txt('activate_post_txt'));								
		$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('activate_only_current'));
		$form_tpl->setVariable('CMD_CONFIRM', 'performPostActivation');
		$this->ctrl->clearParameters($this);

		return $form_tpl->get(); 
	}
	
	public function getCensorshipFormHTML()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $frm ilForum
		 */
		global $lng, $frm;

		$form_tpl = new ilTemplate('tpl.frm_censorship_post_form.html', true, true, 'Modules/Forum');

		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$this->ctrl->setParameter($this, 'action', 'ready_censor');
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getLinkTarget($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('TXT_CENS_MESSAGE', $lng->txt('forums_the_post'));
		$form_tpl->setVariable('TXT_CENS_COMMENT', $lng->txt('forums_censor_comment').':');
		$form_tpl->setVariable('CENS_MESSAGE', $frm->prepareText($this->objCurrentPost->getCensorshipComment(), 2));

		if($this->objCurrentPost->isCensored())
		{
			$form_tpl->setVariable('TXT_CENS', $lng->txt('forums_info_censor2_post'));
			$form_tpl->setVariable('YES_BUTTON', $lng->txt('confirm'));
			$form_tpl->setVariable('NO_BUTTON', $lng->txt('cancel'));
		}
		else
		{
			$form_tpl->setVariable('TXT_CENS', $lng->txt('forums_info_censor_post'));
			$form_tpl->setVariable('CANCEL_BUTTON', $lng->txt('cancel'));
			$form_tpl->setVariable('CONFIRM_BUTTON', $lng->txt('confirm'));
		}

  		return $form_tpl->get(); 
	}

	/**
	 * @throws ilHtmlPurifierNotFoundException
	 */
	private function initReplyEditForm()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $rbacsystem ilRbacSystem
		 * @var $frm ilForum
		 * @var $oFDForum ilFileDataForum
		 */
		global $ilUser, $rbacsystem;

		// init objects
		$oForumObjects = $this->getForumObjects();
		$frm = $oForumObjects['frm'];
		$oFDForum = $oForumObjects['file_obj'];

		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->replyEditForm = new ilPropertyFormGUI();
		$this->replyEditForm->setTableWidth('100%');

		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$this->ctrl->setParameter($this, 'action', 'ready_showreply');
		}
		else
		{
			$this->ctrl->setParameter($this, 'action', 'ready_showedit');
		}

		$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		if($this->isTopLevelReplyCommand())
		{
			$this->replyEditForm->setFormAction($this->ctrl->getFormAction($this, 'saveTopLevelPost'), 'frm_page_bottom');
		}
		else
		{
			$this->replyEditForm->setFormAction($this->ctrl->getFormAction($this, 'savePost'), $this->objCurrentPost->getId());
		}
		$this->ctrl->clearParameters($this);

		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$this->replyEditForm->setTitle($this->lng->txt('forums_your_reply'));
		}
		else
		{
			$this->replyEditForm->setTitle($this->lng->txt('forums_edit_post'));
		}

		// alias
		if($this->isWritingWithPseudonymAllowed()				
		  && in_array($_GET['action'], array('showreply', 'ready_showreply')))
		{
			$oAnonymousNameGUI = new ilTextInputGUI($this->lng->txt('forums_your_name'), 'alias');
			$oAnonymousNameGUI->setMaxLength(64);
			$oAnonymousNameGUI->setInfo($this->lng->txt('forums_use_alias'));			
			
			$this->replyEditForm->addItem($oAnonymousNameGUI);
		}

		// subject
		$oSubjectGUI = new ilTextInputGUI($this->lng->txt('forums_subject'), 'subject');
		$oSubjectGUI->setMaxLength(64);
		$oSubjectGUI->setRequired(true);

		if($this->objProperties->getSubjectSetting() == 'empty_subject')
		$oSubjectGUI->setInfo($this->lng->txt('enter_new_subject'));

		$this->replyEditForm->addItem($oSubjectGUI);

		// post
		$oPostGUI = new ilTextAreaInputGUI(
			$_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply' ? $this->lng->txt('forums_your_reply') : $this->lng->txt('forums_edit_post'), 
			'message'
		);
		$oPostGUI->setRequired(true);
		$oPostGUI->setCols(50);
		$oPostGUI->setRows(15);
		$oPostGUI->setUseRte(true);
		$oPostGUI->addPlugin('latex');
		$oPostGUI->addButton('latex');
		$oPostGUI->addButton('pastelatex');
		$oPostGUI->addPlugin('ilfrmquote');

		//$oPostGUI->addPlugin('code'); 
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$oPostGUI->addButton('ilFrmQuoteAjaxCall');
		}
		$oPostGUI->removePlugin('advlink');
		$oPostGUI->setRTERootBlockElement('');
		$oPostGUI->usePurifier(true);
		$oPostGUI->disableButtons(array(
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'formatselect'
		));

		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$oPostGUI->setRTESupport($ilUser->getId(), 'frm~', 'frm_post', 'tpl.tinymce_frm_post.html', false, '3.4.7');
		}
		else
		{
			$oPostGUI->setRTESupport($this->objCurrentPost->getId(), 'frm', 'frm_post', 'tpl.tinymce_frm_post.html', false, '3.4.7');
		}
		// purifier
		require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
		$oPostGUI->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));		

		$this->replyEditForm->addItem($oPostGUI);

		// notification only if gen. notification is disabled and forum isn't anonymous
		include_once 'Services/Mail/classes/class.ilMail.php';
		$umail = new ilMail($ilUser->getId());
		if($rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId()) &&
		   !$frm->isThreadNotificationEnabled($ilUser->getId(), $this->objCurrentPost->getThreadId()) &&
		   !$this->objProperties->isAnonymized())
		{
			$oNotificationGUI = new ilCheckboxInputGUI('', 'notify');
			$oNotificationGUI->setInfo($this->lng->txt('forum_notify_me'));
			
			$this->replyEditForm->addItem($oNotificationGUI);
		}

		if($this->objProperties->isFileUploadAllowed())
		{
			$oFileUploadGUI = new ilFileWizardInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
			$oFileUploadGUI->setFilenames(array(0 => ''));
			$this->replyEditForm->addItem($oFileUploadGUI);
		}

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		if(
			$ilUser->isAnonymous() &&
			!$ilUser->isCaptchaVerified() &&
			ilCaptchaUtil::isActiveForForum()
		)
		{
			require_once 'Services/Captcha/classes/class.ilCaptchaInputGUI.php';
			$captcha = new ilCaptchaInputGUI($this->lng->txt('cont_captcha_code'), 'captcha_code');
			$captcha->setRequired(true);		
			$this->replyEditForm->addItem($captcha);
		}

		$attachments_of_node = $oFDForum->getFilesOfPost();
		if(count($attachments_of_node) && ($_GET['action'] == 'showedit' || $_GET['action'] == 'ready_showedit'))
		{
			$oExistingAttachmentsGUI = new ilCheckboxGroupInputGUI($this->lng->txt('forums_delete_file'), 'del_file');
			foreach($oFDForum->getFilesOfPost() as $file)
			{
				$oAttachmentGUI = new ilCheckboxInputGUI($file['name'], 'del_file');
				$oAttachmentGUI->setValue($file['md5']);
				$oExistingAttachmentsGUI->addOption($oAttachmentGUI);
			}
			$this->replyEditForm->addItem($oExistingAttachmentsGUI);
		}

		if($this->isTopLevelReplyCommand())
		{
			$this->replyEditForm->addCommandButton('saveTopLevelPost', $this->lng->txt('create'));
		}
		else
		{
			$this->replyEditForm->addCommandButton('savePost', $this->lng->txt('create'));
		}

		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			include_once 'Services/RTE/classes/class.ilRTE.php';
			$rtestring = ilRTE::_getRTEClassname();
			
			if(array_key_exists('show_rte', $_POST))
			{
				ilObjAdvancedEditing::_setRichTextEditorUserState($_POST['show_rte']);
			}			

			if(strtolower($rtestring) != 'iltinymce' || !ilObjAdvancedEditing::_getRichTextEditorUserState())
			{
				if($this->isTopLevelReplyCommand())
				{
					$this->replyEditForm->addCommandButton('quoteTopLevelPost', $this->lng->txt('forum_add_quote'));
				}
				else
				{
					$this->replyEditForm->addCommandButton('quotePost', $this->lng->txt('forum_add_quote'));
				}
			}
		}
		$this->replyEditForm->addCommandButton('cancelPost', $this->lng->txt('cancel'));
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	private function getReplyEditForm()
	{
		if(null === $this->replyEditForm)
		{
			$this->initReplyEditForm();
		}
		
		return $this->replyEditForm;
	}

	/**
	 * 
	 */
	public function createToLevelPostObject()
	{
		$this->viewThreadObject();
		return;
	}

	/**
	 * 
	 */
	public function saveTopLevelPostObject()
	{
		$this->savePostObject();
		return;
	}

	/**
	 * 
	 */
	public function quoteTopLevelPostObject()
	{
		$this->quotePostObject();
		return;
	}

	/**
	 * @return bool
	 */
	public function savePostObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilAccess ilAccessHandler
		 * @var $lng ilLanguage
		 */
		global $ilUser, $ilAccess, $lng;

		if(!isset($_POST['del_file']) || !is_array($_POST['del_file'])) $_POST['del_file'] = array();

		if($this->objCurrentTopic->isClosed())
		{
			$_GET['action'] = '';
			return $this->viewThreadObject();
		}

		$oReplyEditForm = $this->getReplyEditForm();
		if($oReplyEditForm->checkInput())
		{
			require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
			if(
				$ilUser->isAnonymous() &&
				!$ilUser->isCaptchaVerified() &&
				ilCaptchaUtil::isActiveForForum()
			)
			{
				$ilUser->setCaptchaVerified(true);
			}

			// init objects
			$oForumObjects = $this->getForumObjects();
			/**
			 * @var $forumObj ilObjForum
			 */
			$forumObj = $oForumObjects['forumObj'];
			/**
			 * @var $frm ilForum
			 */
			$frm = $oForumObjects['frm'];
			$frm->setMDB2WhereCondition(' top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
			$topicData = $frm->getOneTopic();

			// Generating new posting
			if($_GET['action'] == 'ready_showreply')
			{
				if(!$ilAccess->checkAccess('add_reply', '', (int)$_GET['ref_id']))
				{
					$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
				}

				// reply: new post
				$status = 1;
				$send_activation_mail = 0;
				
				if($this->objProperties->isPostActivationEnabled())
				{
					if(!$this->is_moderator)
					{
						$status = 0;
						$send_activation_mail = 1;
					}
					else if($this->objCurrentPost->isAnyParentDeactivated())
					{
						$status = 0;
					}
				}

				if($this->isWritingWithPseudonymAllowed())
				{
					if(!strlen($oReplyEditForm->getInput('alias')))
					{
						$user_alias = $this->lng->txt('forums_anonymous');
					}
					else
					{
						$user_alias = $oReplyEditForm->getInput('alias');
					}
					$display_user_id = 0;
				}
				else
				{
					$user_alias = $ilUser->getLogin();
					$display_user_id = $ilUser->getId();
				}
					
				
				$newPost = $frm->generatePost(
					$topicData['top_pk'], 
					$this->objCurrentTopic->getId(),
					$ilUser->getId(),
					$display_user_id, 
					ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message'), 0),
					$this->objCurrentPost->getId(),
					(int)$oReplyEditForm->getInput('notify'),
					$this->handleFormInput($oReplyEditForm->getInput('subject'), false),
				    $user_alias,
					'',
					$status,
					$send_activation_mail					
				);

				// mantis #8115: Mark parent as read
				$this->object->markPostRead($ilUser->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());

				// copy temporary media objects (frm~)
				include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$mediaObjects = ilRTE::_getMediaObjects($oReplyEditForm->getInput('message'), 0);				
				$myMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $ilUser->getId());
				foreach($mediaObjects as $mob)
				{
					foreach($myMediaObjects as $myMob)
					{
						if($mob == $myMob)
						{
							// change usage
							ilObjMediaObject::_removeUsage($mob, 'frm~:html', $ilUser->getId());
							break;													
						}
					}
					ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
				}

				if($this->objProperties->isFileUploadAllowed())
				{
					$oFDForum = new ilFileDataForum($forumObj->getId(), $newPost);
					$file     = $_FILES['userfile'];
					if(is_array($file) && !empty($file))
					{
						$oFDForum->storeUploadedFile($file);
					}
				}
				
				$GLOBALS['ilAppEventHandler']->raise(
					'Modules/Forum',
					'createdPost',
					array(
						'ref_id'            => $this->object->getRefId(),
						'post'              => new ilForumPost($newPost),
						'notify_moderators' => (bool)$send_activation_mail
					)
				);

				$message = '';
				if(!$this->is_moderator && !$status)
				{
					$message .= $lng->txt('forums_post_needs_to_be_activated');
				}
				else
				{
					$message .= $lng->txt('forums_post_new_entry');
				}

				ilUtil::sendSuccess($message, true);
				$this->ctrl->setParameter($this, 'post_created_below', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'pos_pk', $newPost);
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
				$this->ctrl->redirect($this, 'viewThread');
			}
			else
			{
				if((!$this->is_moderator &&
				   !$this->objCurrentPost->isOwner($ilUser->getId())) || $this->objCurrentPost->isCensored() ||
				   $ilUser->getId() == ANONYMOUS_USER_ID)
				{
				   	$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
					}				

				// remove usage of deleted media objects
				include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm:html', $this->objCurrentPost->getId());
				$curMediaObjects = ilRTE::_getMediaObjects($oReplyEditForm->getInput('message'), 0);
				foreach($oldMediaObjects as $oldMob)
				{
					$found = false;
					foreach($curMediaObjects as $curMob)
					{
						if($oldMob == $curMob)
						{
							$found = true;
							break;																					
						}
					}
					if(!$found)
					{						
						if(ilObjMediaObject::_exists($oldMob))
						{
							ilObjMediaObject::_removeUsage($oldMob, 'frm:html', $this->objCurrentPost->getId());
							$mob_obj = new ilObjMediaObject($oldMob);
							$mob_obj->delete();
						}
					}
				}
					
				// if post has been edited posting mus be activated again by moderator
				$status = 1;
				$send_activation_mail = 0;

				if($this->objProperties->isPostActivationEnabled())
				{
					if(!$this->is_moderator)
					{
						$status = 0;
						$send_activation_mail = 1;
					}
					else if($this->objCurrentPost->isAnyParentDeactivated())
					{
						$status = 0;
					}
				}
				$this->objCurrentPost->setStatus($status);

				$this->objCurrentPost->setSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
				$this->objCurrentPost->setMessage(ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message'), 0));
				$this->objCurrentPost->setNotification((int)$oReplyEditForm->getInput('notify'));
				$this->objCurrentPost->setChangeDate(date('Y-m-d H:i:s'));  
				$this->objCurrentPost->setUpdateUserId($ilUser->getId());
				
				// edit: update post
				if($this->objCurrentPost->update())
				{
					$this->objCurrentPost->reload();
					
					// Change news item accordingly
					include_once 'Services/News/classes/class.ilNewsItem.php';
					// note: $this->objCurrentPost->getForumId() does not give us the forum ID here (why?)
					$news_id = ilNewsItem::getFirstNewsIdForContext($forumObj->getId(),
						'frm', $this->objCurrentPost->getId(), 'pos');
					if($news_id > 0)
					{
						$news_item = new ilNewsItem($news_id);
						$news_item->setTitle($this->objCurrentPost->getSubject());
						$news_item->setContent(ilRTE::_replaceMediaObjectImageSrc($frm->prepareText(
							$this->objCurrentPost->getMessage(), 0), 1)
						);
						$news_item->update();
					}

					$oFDForum = $oForumObjects['file_obj'];

					if($this->objProperties->isFileUploadAllowed())
					{
						$file = $_FILES['userfile'];
						if(is_array($file) && !empty($file))
						{
							$oFDForum->storeUploadedFile($file);
						}
					}

					$file2delete = $oReplyEditForm->getInput('del_file');
					if(is_array($file2delete) && count($file2delete))
					{
						$oFDForum->unlinkFilesByMD5Filenames($file2delete);
					}

					$GLOBALS['ilAppEventHandler']->raise(
						'Modules/Forum',
						'updatedPost',
						array(
							'ref_id'            => $this->object->getRefId(),
							'post'              => $this->objCurrentPost,
							'notify_moderators' => (bool)$send_activation_mail
						)
					);
	
					ilUtil::sendSuccess($lng->txt('forums_post_modified'), true);
				}

				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
				$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
				$this->ctrl->redirect($this, 'viewThread');
			}
		}
		else
		{
			$_GET['action'] = substr($_GET['action'], 6);
		}
		return $this->viewThreadObject();
	}

	private function hideToolbar($a_flag = null)
	{
		if(null === $a_flag)
		{
			return $this->hideToolbar;
		}
		
		$this->hideToolbar = $a_flag;
		return $this;
	}
	
	public function quotePostObject()		
	{
		if(!is_array($_POST['del_file'])) $_POST['del_file'] = array();
		
		if($this->objCurrentTopic->isClosed())
		{
			$_GET['action'] = '';
			return $this->viewThreadObject();
		}
		
		$oReplyEditForm = $this->getReplyEditForm();
		
		// remove mandatory fields
		$oReplyEditForm->getItemByPostVar('subject')->setRequired(false);
		$oReplyEditForm->getItemByPostVar('message')->setRequired(false);
		
		$oReplyEditForm->checkInput();
		
		// add mandatory fields
		$oReplyEditForm->getItemByPostVar('subject')->setRequired(true);
		$oReplyEditForm->getItemByPostVar('message')->setRequired(true);
		
		$_GET['action'] = 'showreply';
		
		$this->viewThreadObject();
	}
	
	public function getQuotationHTMLAsynchObject()
	{
		$oForumObjects = $this->getForumObjects();
		/**
		 * @var $frm ilForum
		 */
		$frm = $oForumObjects['frm'];

		require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
		$authorinfo = new ilForumAuthorInformation(
			$this->objCurrentPost->getPosAuthorId(),
			$this->objCurrentPost->getDisplayUserId(),
			$this->objCurrentPost->getUserAlias(),
			$this->objCurrentPost->getImportName()
		);

		$html = ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($this->objCurrentPost->getMessage(), 1, $authorinfo->getAuthorName()), 1);
		echo $html;
		exit();
	}
	
	private function getForumObjects()
	{
		if(null === $this->forumObjects)
		{
			$forumObj = $this->object;
			$file_obj = new ilFileDataForum($forumObj->getId(), $this->objCurrentPost->getId());
			$frm = $forumObj->Forum;
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
			
			$this->forumObjects['forumObj'] = $forumObj;
			$this->forumObjects['frm'] = $frm;
			$this->forumObjects['file_obj'] = $file_obj;
		}
		
		return $this->forumObjects;
	}

	public function viewThreadObject()
	{
		/**
		 * @var $tpl ilTemplate
		 * @var $lng ilLanguage
		 * @var $ilUser ilObjUser
		 * @var $ilAccess ilAccessHandler
		 * @var $rbacreview ilRbacReview
		 * @var $ilNavigationHistory ilNavigationHistory
		 * @var $ilCtrl ilCtrl
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $tpl, $lng, $ilUser, $ilAccess, $rbacreview, $ilNavigationHistory, $ilCtrl, $frm, $ilToolbar, $ilLocator;

		$bottom_toolbar                    = clone $ilToolbar;
		$bottom_toolbar_split_button_items = array();

		$tpl->addCss('./Modules/Forum/css/forum_tree.css');
		if(!isset($_SESSION['viewmode']))
		{
			$_SESSION['viewmode'] = $this->objProperties->getDefaultView();
		}
		
		// quick and dirty: check for treeview
		if(!isset($_SESSION['thread_control']['old']))
		{
			$_SESSION['thread_control']['old'] = $_GET['thr_pk'];
			$_SESSION['thread_control']['new'] = $_GET['thr_pk'];
		}
		else
		if(isset($_SESSION['thread_control']['old']) && $_GET['thr_pk'] != $_SESSION['thread_control']['old'])
		{
			$_SESSION['thread_control']['new'] = $_GET['thr_pk'];
		}

		if(isset($_GET['viewmode']) && $_GET['viewmode'] != $_SESSION['viewmode'])
		{
			$_SESSION['viewmode'] = $_GET['viewmode'];
		}

		if( (isset($_GET['action']) && $_SESSION['viewmode'] != ilForumProperties::VIEW_DATE)
		||($_SESSION['viewmode'] == ilForumProperties::VIEW_TREE))  
		{
			$_SESSION['viewmode'] = ilForumProperties::VIEW_TREE;
		}
		else
		{
			$_SESSION['viewmode'] = ilForumProperties::VIEW_DATE;
		}

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		// init objects
		$oForumObjects = $this->getForumObjects();
		/**
		 * @var $forumObj ilObjForum
		 */
		$forumObj = $oForumObjects['forumObj'];
		/**
		 * @var $frm ilForum
		 */
		$frm = $oForumObjects['frm'];
		/**
		 * @var $file_obj ilFileDataForum
		 */
		$file_obj = $oForumObjects['file_obj'];

		// download file
		if($_GET['file'])
		{
			if(!$path = $file_obj->getFileDataByMD5Filename($_GET['file']))
			{
				ilUtil::sendFailure($this->lng->txt('error_reading_file'));
			}
			else
			{
				ilUtil::deliverFile($path['path'], $path['clean_filename']);
			}
		}

		if(!$this->objCurrentTopic->getId())
		{
			$ilCtrl->redirect($this, 'showThreads');
		}

		// Set context for login
		$append = '_'.$this->objCurrentTopic->getId().
			($this->objCurrentPost->getId() ? '_'.$this->objCurrentPost->getId() : '');
		$tpl->setLoginTargetPar('frm_'.$_GET['ref_id'].$append);

		// delete temporary media object (not in case a user adds media objects and wants to save an invalid form)
		if($_GET['action'] != 'showreply' && $_GET['action'] != 'showedit')
		{
			try
			{
				include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$mobs = ilObjMediaObject::_getMobsOfObject('frm~:html', $ilUser->getId());
				foreach($mobs as $mob)
				{					
					if(ilObjMediaObject::_exists($mob))
					{
						ilObjMediaObject::_removeUsage($mob, 'frm~:html', $ilUser->getId());
						$mob_obj = new ilObjMediaObject($mob);
						$mob_obj->delete();
					}
				}
			}
			catch(Exception $e)
			{
			}
		}

		if($this->isHierarchicalView())
		{
			require_once 'Modules/Forum/classes/class.ilForumExplorerGUI.php';
			$exp = new ilForumExplorerGUI('frm_exp_' . $this->objCurrentTopic->getId(), $this, 'viewThread');
			$exp->setThread($this->objCurrentTopic);
			if(!$exp->handleCommand())
			{
				$this->tpl->setLeftNavContent($exp->getHTML());
			}
		}

		require_once './Modules/Forum/classes/class.ilObjForum.php';
		require_once './Modules/Forum/classes/class.ilFileDataForum.php';		
		
		$lng->loadLanguageModule('forum');

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$ilCtrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			$ilNavigationHistory->addItem($this->object->getRefId(), $ilCtrl->getLinkTarget($this, 'showThreads'), 'frm');
		}
		
		// save last access
		$forumObj->updateLastAccess($ilUser->getId(), (int) $this->objCurrentTopic->getId());
		
		$this->prepareThreadScreen($forumObj);
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_view.html', 'Modules/Forum');

		if(isset($_GET['anchor']))
		{
			$tpl->setVariable('JUMP2ANCHOR_ID', (int)$_GET['anchor']);
		}

		if($this->isHierarchicalView())
		{
			$orderField = 'frm_posts_tree.rgt';
			$this->objCurrentTopic->setOrderDirection('DESC');
		}
		else
		{
			$orderField = 'frm_posts_tree.fpt_date';
			$this->objCurrentTopic->setOrderDirection(
				in_array($this->objProperties->getDefaultView(), array(ilForumProperties::VIEW_DATE_ASC, ilForumProperties::VIEW_TREE))
				? 'ASC' : 'DESC'
			);
		}

		// get forum- and thread-data
		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		
		if(is_array($topicData = $frm->getOneTopic()))
		{
			// Visit-Counter for topic
			$this->objCurrentTopic->updateVisits();
			
			$tpl->setTitle($lng->txt('forums_thread')." \"".$this->objCurrentTopic->getSubject()."\"");			
		
			// ********************************************************************************
			// build location-links
			$ilLocator->addRepositoryItems();
			$ilLocator->addItem($this->object->getTitle(), $ilCtrl->getLinkTarget($this, ""), "_top");
			$tpl->setLocator();
																		 
			// set tabs					
			// menu template (contains linkbar)
			/** @var $menutpl ilTemplate */
			$menutpl = new ilTemplate('tpl.forums_threads_menu.html', true, true, 'Modules/Forum');

			include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
			
			// mark all as read
			if(
				$ilUser->getId() != ANONYMOUS_USER_ID &&
				$forumObj->getCountUnread($ilUser->getId(), (int) $this->objCurrentTopic->getId())
			)
			{
				$this->ctrl->setParameter($this, 'mark_read', '1');
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());

				require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
				$mark_thr_read_button = ilLinkButton::getInstance();
				$mark_thr_read_button->setCaption('forums_mark_read');
				$mark_thr_read_button->setUrl($this->ctrl->getLinkTarget($this, 'viewThread'));
				$mark_thr_read_button->setAccessKey(ilAccessKey::MARK_ALL_READ);

				$bottom_toolbar_split_button_items[] = $mark_thr_read_button;

				$this->ctrl->clearParameters($this);
			}

			// print thread
			$this->ctrl->setParameterByClass('ilforumexportgui', 'print_thread', $this->objCurrentTopic->getId());
			$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_top_fk', $this->objCurrentTopic->getForumId());

			require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
			$print_thr_button = ilLinkButton::getInstance();
			$print_thr_button->setCaption('forums_print_thread');
			$print_thr_button->setUrl($this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printThread'));

			$bottom_toolbar_split_button_items[] = $print_thr_button;

			$this->ctrl->clearParametersByClass('ilforumexportgui');

			$this->addHeaderAction();
			
			if($_GET['mark_read'])
			{
				$forumObj->markThreadRead($ilUser->getId(), (int)$this->objCurrentTopic->getId());
				ilUtil::sendInfo($lng->txt('forums_thread_marked'), true);
			}

			// delete post and its sub-posts
			require_once './Modules/Forum/classes/class.ilForum.php';
	
			if ($_GET['action'] == 'ready_delete' && $_POST['confirm'] != '')
			{
				if(!$this->objCurrentTopic->isClosed() &&
				   ($this->is_moderator ||
					($this->objCurrentPost->isOwner($ilUser->getId()) && !$this->objCurrentPost->hasReplies())) &&
				   $ilUser->getId() != ANONYMOUS_USER_ID)
				{
					$frm = new ilForum();
	
					$frm->setForumId($forumObj->getId());
					$frm->setForumRefId($forumObj->getRefId());
	
					$dead_thr = $frm->deletePost($this->objCurrentPost->getId());
	
					// if complete thread was deleted ...
					if ($dead_thr == $this->objCurrentTopic->getId())
					{
	
						$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($forumObj->getId()));
	
						$topicData = $frm->getOneTopic();
	
						ilUtil::sendInfo($lng->txt('forums_post_deleted'), true);
	
						if ($topicData['top_num_threads'] > 0)
						{
							$this->ctrl->redirect($this, 'showThreads');
						}
						else
						{
							$this->ctrl->redirect($this, 'createThread');
						}
					}
					ilUtil::sendInfo($lng->txt('forums_post_deleted'));
				}
			}

			// form processing (censor)			
			if(!$this->objCurrentTopic->isClosed() && $_GET['action'] == 'ready_censor')
			{
				$cens_message = $this->handleFormInput($_POST['formData']['cens_message']);
				
				if(($_POST['confirm'] != '' || $_POST['no_cs_change'] != '') && $_GET['action'] == 'ready_censor')
				{
					$frm->postCensorship($cens_message, $this->objCurrentPost->getId(), 1);
					ilUtil::sendSuccess($this->lng->txt('frm_censorship_applied'));
				}
				else if(($_POST['cancel'] != '' || $_POST['yes_cs_change'] != '') && $_GET['action'] == 'ready_censor')
				{
					$frm->postCensorship($cens_message, $this->objCurrentPost->getId());
					ilUtil::sendSuccess($this->lng->txt('frm_censorship_revoked'));
				}
			}

			// get complete tree of thread	
			$first_node = $this->objCurrentTopic->getFirstPostNode();
			$this->objCurrentTopic->setOrderField($orderField);
			$subtree_nodes = $this->objCurrentTopic->getPostTree($first_node);

			if(
				!$this->isTopLevelReplyCommand() &&
				$first_node instanceof ilForumPost &&
				!$this->objCurrentTopic->isClosed() &&
				$ilAccess->checkAccess('add_reply', '', (int)$_GET['ref_id'])
			)
			{
				require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
				$reply_button = ilLinkButton::getInstance();
				$reply_button->setPrimary(true);
				$reply_button->setCaption('add_new_answer');
				$this->ctrl->setParameter($this, 'action',  'showreply');
				$this->ctrl->setParameter($this, 'pos_pk', $first_node->getId());
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
				$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
				$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);

				$reply_button->setUrl($this->ctrl->getLinkTarget($this, 'createToLevelPost', 'frm_page_bottom'));

				$this->ctrl->clearParameters($this);
				array_unshift($bottom_toolbar_split_button_items, $reply_button);
			}

			// no posts
			if (!$posNum = count($subtree_nodes))
			{
				ilUtil::sendInfo($this->lng->txt('forums_no_posts_available'));	
			}			
					
			$pageHits = $frm->getPageHits();

			$z = 0;
		
			// navigation to browse
			if ($posNum > $pageHits)
			{
				$params = array(
					'ref_id'		=> $_GET['ref_id'],
					'thr_pk'		=> $this->objCurrentTopic->getId(),
					'orderby'		=> $_GET['orderby']
				);
		
				if (!$_GET['offset'])
				{
					$Start = 0;
				}
				else
				{
					$Start = $_GET['offset'];
				}
		
				$linkbar = ilUtil::Linkbar($ilCtrl->getLinkTarget($this, 'viewThread'), $posNum, $pageHits, $Start, $params);

				if($linkbar != '')
				{
					$menutpl->setCurrentBlock('linkbar');
					$menutpl->setVariable('LINKBAR', $linkbar);
					$menutpl->parseCurrentBlock();
				}
			}		

			$tpl->setVariable('THREAD_MENU', $menutpl->get());
		
			// assistance val for anchor-links
			$jump = 0;

			// generate post-dates
			foreach($subtree_nodes as $node)
			{
				/**
				 * @var $node ilForumPost 
				 */
				
				$this->ctrl->clearParameters($this);
				
				if($this->objCurrentPost->getId() && $this->objCurrentPost->getId() == $node->getId())
				{
					$jump++;
				}
		
				if ($posNum > $pageHits && $z >= ($Start + $pageHits))
				{
					// if anchor-link was not found ...
					if ($this->objCurrentPost->getId() && $jump < 1)
					{
						$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
						$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
						$this->ctrl->setParameter($this, 'offset', ($Start + $pageHits));
						$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
						$this->ctrl->redirect($this, 'viewThread', $this->objCurrentPost->getId());
					}
					else
					{
						break;
					}
				}
		
				if(($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
				{
					$actions = array();
					if(!$this->isTopLevelReplyCommand() && $this->objCurrentPost->getId() == $node->getId())
					{
						# actions for "active" post
						if($this->is_moderator || $node->isActivated())
						{
							// reply/edit
							if(!$this->objCurrentTopic->isClosed() && ($_GET['action'] == 'showreply' || $_GET['action'] == 'showedit'))
							{
								if($_GET['action'] == 'showedit' &&
								  ((!$this->is_moderator &&
								   !$node->isOwner($ilUser->getId()) || $ilUser->getId() == ANONYMOUS_USER_ID) || $node->isCensored()))
								{
								   	$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
								}
								else if($_GET['action'] == 'showreply' && !$ilAccess->checkAccess('add_reply', '', (int)$_GET['ref_id']))
								{
									$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
								}
													   
								$tpl->setVariable('REPLY_ANKER', $this->objCurrentPost->getId());
								$oEditReplyForm = $this->getReplyEditForm();

								switch($this->objProperties->getSubjectSetting())
								{
									case 'add_re_to_subject':
										$subject = $this->getModifiedReOnSubject(true);
										break;

									case 'preset_subject':
										$subject = $this->objCurrentPost->getSubject();
										break;

									case 'empty_subject':
									default:
										$subject = NULL;
										break;
								}

								switch($_GET['action'])
								{
									case 'showreply':
										if($this->ctrl->getCmd() == 'savePost')
										{
											$oEditReplyForm->setValuesByPost();
										}										
										else if($this->ctrl->getCmd() == 'quotePost')
										{
											require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
											$authorinfo = new ilForumAuthorInformation(
												$node->getPosAuthorId(),
												$node->getDisplayUserId(),
												$node->getUserAlias(),
												$node->getImportName()
											);
											
											$oEditReplyForm->setValuesByPost();
											$oEditReplyForm->getItemByPostVar('message')->setValue(
												ilRTE::_replaceMediaObjectImageSrc(
													$frm->prepareText($node->getMessage(), 1, $authorinfo->getAuthorName())."\n".$oEditReplyForm->getInput('message'),    1
												)
											);
										}
										else
										{
											$oEditReplyForm->setValuesByArray(array(
												'alias' => '',
												'subject' => $subject,
												'message' => '',
												'notify' => 0,
												'userfile' => '',
												'del_file' => array()
											));
										}
										
										$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
										$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());

										$jsTpl = new ilTemplate('tpl.forum_post_quoation_ajax_handler.html', true, true, 'Modules/Forum');
										$jsTpl->setVariable('IL_FRM_QUOTE_CALLBACK_SRC',
											$this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true));
										$this->ctrl->clearParameters($this);
										$this->tpl->setVariable('FORM_ADDITIONAL_JS', $jsTpl->get());
										break;
									case 'showedit':
										if($this->ctrl->getCmd() == 'savePost')
										{
											$oEditReplyForm->setValuesByPost();
										}
										else
										{
											$oEditReplyForm->setValuesByArray(array(
												'alias' => '',
												'subject' => $this->objCurrentPost->getSubject(),
												'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($this->objCurrentPost->getMessage(), 2), 1),
												'notify' => $this->objCurrentPost->isNotificationEnabled() ? true : false,
												'userfile' => '',
												'del_file' => array()
											));
										}
										break;
								}
								$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
								$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'action', $_GET['action']);
								$tpl->setVariable('FORM', $oEditReplyForm->getHTML());
								$this->ctrl->clearParameters($this);
			
							} // if ($_GET['action'] == 'showreply' || $_GET['action'] == 'showedit')
							else if(!$this->objCurrentTopic->isClosed() && $_GET['action'] == 'delete')
							{
								if($this->is_moderator ||
								   ($node->isOwner($ilUser->getId()) && !$node->hasReplies()) &&
							       $ilUser->getId() != ANONYMOUS_USER_ID)
								{
									// confirmation: delete
									$tpl->setVariable('FORM', $this->getDeleteFormHTML());							
								}
							} // else if ($_GET['action'] == 'delete')
							else if(!$this->objCurrentTopic->isClosed() && $_GET['action'] == 'censor')
							{
								if($this->is_moderator)
								{
									// confirmation: censor / remove censorship
									$tpl->setVariable('FORM', $this->getCensorshipFormHTML());							
								}
							}
							else if (!$this->objCurrentTopic->isClosed() && $this->displayConfirmPostActivation())
							{
								if ($this->is_moderator)
								{
									// confirmation: activate
									$tpl->setVariable('FORM', $this->getActivationFormHTML());							
								}
							} 
						}
					} // if ($this->objCurrentPost->getId() == $node->getId())				
					
					if ($this->objCurrentPost->getId() != $node->getId() ||
						($_GET['action'] != 'showreply' &&
						 $_GET['action'] != 'showedit' &&
						 $_GET['action'] != 'censor' &&
						 $_GET['action'] != 'delete' &&
						 !$this->displayConfirmPostActivation()
						))
					{
						if($this->is_moderator || $node->isActivated())
						{
							// button: reply
							if(!$this->objCurrentTopic->isClosed() &&
								$ilAccess->checkAccess('add_reply', '', (int)$_GET['ref_id']) &&
								!$node->isCensored()
							)
							{
								$this->ctrl->setParameter($this, 'action', 'showreply');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());

								$actions['reply_to_postings'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());

								$this->ctrl->clearParameters($this);
							}
							
							// button: edit article
							if (!$this->objCurrentTopic->isClosed() &&
								($node->isOwner($ilUser->getId()) || $this->is_moderator) &&
								 !$node->isCensored() &&
								 $ilUser->getId() != ANONYMOUS_USER_ID)
							{
								$this->ctrl->setParameter($this, 'action', 'showedit');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);

								$actions['edit'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());

								$this->ctrl->clearParameters($this);
							}

							// button: mark read
							if ($ilUser->getId() != ANONYMOUS_USER_ID && !$node->isPostRead())
							{
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);

								$actions['frm_mark_as_read'] = $this->ctrl->getLinkTarget($this, 'markPostRead', $node->getId());

								$this->ctrl->clearParameters($this);
							}

							// button: mark unread
							if ($ilUser->getId() != ANONYMOUS_USER_ID &&
								$node->isPostRead())
							{
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);

								$actions['frm_mark_as_unread'] = $this->ctrl->getLinkTarget($this, 'markPostUnread', $node->getId());

								$this->ctrl->clearParameters($this);
							}

							// button: print
							if (!$node->isCensored())
							{
								$this->ctrl->setParameterByClass('ilforumexportgui', 'print_post', $node->getId());
								$this->ctrl->setParameterByClass('ilforumexportgui', 'top_pk', $node->getForumId());
								$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_pk', $node->getThreadId());

								$actions['print'] = $this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printPost');

								$this->ctrl->clearParameters($this);
							}

							# buttons for every post except the "active"
							if (!$this->objCurrentTopic->isClosed() &&
							   ($this->is_moderator ||
							   ($node->isOwner($ilUser->getId()) && !$node->hasReplies())) &&
							   $ilUser->getId() != ANONYMOUS_USER_ID)
							{
								// button: delete
								$this->ctrl->setParameter($this, 'action', 'delete');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);

								$actions['delete'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());

								$this->ctrl->clearParameters($this);
							}
							
							if (!$this->objCurrentTopic->isClosed() && $this->is_moderator)
							{	
								// button: censor							
								$this->ctrl->setParameter($this, 'action', 'censor');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
								if($node->isCensored())
								{
									$actions['frm_revoke_censorship'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());
								}
								else
								{
									$actions['frm_censorship'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());
								}

								$this->ctrl->clearParameters($this);

								// button: activation/deactivation
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
								$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
								$this->ctrl->setParameter($this, 'offset', $Start);
								$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);

								if (!$node->isActivated())
								{
									$actions['activate_post'] = $this->ctrl->getLinkTarget($this, 'askForPostActivation', $node->getId());
								}

								$this->ctrl->clearParameters($this);
							}
						}
					} // if ($this->objCurrentPost->getId() != $node->getId())										

					// download post attachments
					$tmp_file_obj = new ilFileDataForum($forumObj->getId(), $node->getId());
					$attachments_of_node = $tmp_file_obj->getFilesOfPost();
					if(count($attachments_of_node))
					{
						if ($node->getId() != $this->objCurrentPost->getId() || $_GET['action'] != 'showedit')
						{
							foreach($attachments_of_node as $file)
							{
								$tpl->setCurrentBlock('attachment_download_row');
								$this->ctrl->setParameter($this, 'pos_pk', $node->getId());								
								$this->ctrl->setParameter($this, 'file', $file['md5']);
								$tpl->setVariable('HREF_DOWNLOAD', $this->ctrl->getLinkTarget($this, 'viewThread'));
								$tpl->setVariable('TXT_FILENAME', $file['name']);
								$this->ctrl->clearParameters($this);
								$tpl->parseCurrentBlock();
							}
							$tpl->setCurrentBlock('attachments');
							$tpl->setVariable('TXT_ATTACHMENTS_DOWNLOAD',$lng->txt('forums_attachments'));
							include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
							$tpl->setVariable('DOWNLOAD_IMG', ilGlyphGUI::get(ilGlyphGUI::ATTACHMENT, $lng->txt('forums_download_attachment')));
							$tpl->parseCurrentBlock();
						}
					}
		
					$tpl->setCurrentBlock('posts_row');
					if(count($actions) > 0)
					{
						require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
						$action_button = ilSplitButtonGUI::getInstance();

						$i = 0;
						foreach($actions as $lng_id => $url)
						{
							if($i == 0)
							{
								$sb_item = ilLinkButton::getInstance();
								$sb_item->setCaption($lng_id);
								$sb_item->setUrl($url);

								$action_button->setDefaultButton($sb_item);
							}
							else
							{
								$sb_item = ilLinkButton::getInstance();
								$sb_item->setCaption($lng_id);
								$sb_item->setUrl($url);

								$action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($sb_item));
							}

							++$i;
						}
						
						$tpl->setVariable('COMMANDS', $action_button->render());
					}
					// anker for every post					
					$tpl->setVariable('POST_ANKER', $node->getId());					
					
					//permanent link for every post																
				//	$tpl->setVariable('PERMA_LINK', ILIAS_HTTP_PATH."/goto.php?target="."frm"."_".$this->object->getRefId()."_".$node->getThreadId()."_".$node->getId()."&client_id=".CLIENT_ID);
					$tpl->setVariable('TXT_PERMA_LINK', $lng->txt('perma_link'));
					$tpl->setVariable('PERMA_TARGET', '_top');

					if(!$node->isActivated() && !$this->objCurrentTopic->isClosed() && $this->is_moderator)
					{
						$rowCol = 'ilPostingNeedsActivation';
					}
					else if($this->objProperties->getMarkModeratorPosts() == 1)
					{
						if($node->getIsAuthorModerator() === null && $is_moderator = ilForum::_isModerator($_GET['ref_id'], $node->getPosAuthorId()))
						{
							$rowCol = 'ilModeratorPosting';
						}
						elseif($node->getIsAuthorModerator())
						{
							$rowCol = 'ilModeratorPosting';
						}
						else $rowCol = ilUtil::switchColor($z, 'tblrow1', 'tblrow2');
					}
					else $rowCol = ilUtil::switchColor($z, 'tblrow1', 'tblrow2');
					if ((  $_GET['action'] != 'delete' && $_GET['action'] != 'censor' && 
						   !$this->displayConfirmPostActivation()
						) 
						|| $this->objCurrentPost->getId() != $node->getId())
					{
						$tpl->setVariable('ROWCOL', ' '.$rowCol);
					}
					else
					{
						// highlight censored posts
						$rowCol = 'tblrowmarked';
					}
					
					// post is censored
					if ($node->isCensored())
					{
						// display censorship advice
						if ($_GET['action'] != 'censor')
						{
							$tpl->setVariable('TXT_CENSORSHIP_ADVICE', $this->lng->txt('post_censored_comment_by_moderator'));
						}
						
						// highlight censored posts
						$rowCol = 'tblrowmarked';
					}				
					
					// set row color
					$tpl->setVariable('ROWCOL', ' '.$rowCol);
					// if post is not activated display message for the owner
					if(!$node->isActivated() && $node->isOwner($ilUser->getId()))
					{
						$tpl->setVariable('POST_NOT_ACTIVATED_YET', $this->lng->txt('frm_post_not_activated_yet'));
					}
					
					// Author
					$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
					$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
					$backurl = urlencode($this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
					$this->ctrl->clearParameters($this);

					$this->ctrl->setParameter($this, 'backurl', $backurl);
					$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
					$this->ctrl->setParameter($this, 'user', $node->getDisplayUserId());

					require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
					$authorinfo = new ilForumAuthorInformation(
						$node->getPosAuthorId(),
						$node->getDisplayUserId(),
						$node->getUserAlias(),
						$node->getImportName(),
						array(
							'href' => $this->ctrl->getLinkTarget($this, 'showUser')
						)
					);

					$this->ctrl->clearParameters($this);

					if($authorinfo->hasSuffix())
					{
						$tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
						$tpl->setVariable('USR_NAME', $node->getUserAlias());
					}
					else
					{
						$tpl->setVariable('AUTHOR', $authorinfo->getLinkedAuthorShortName());
						if($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized())
						{
							$tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
						}
					}

					$tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
					if($authorinfo->getAuthor()->getId() && ilForum::_isModerator((int)$_GET['ref_id'], $node->getPosAuthorId()))
					{
						if($authorinfo->getAuthor()->getGender() == 'f')
						{
							$tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
						}
						else if($authorinfo->getAuthor()->getGender() == 'm')
						{
							$tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
						}
					}

					// get create- and update-dates
					if ($node->getUpdateUserId() > 0)
					{
						$spanClass = '';
		
						// last update from moderator?
						$posMod = $frm->getModeratorFromPost($node->getId());
		
						if (is_array($posMod) && $posMod['top_mods'] > 0)
						{
							$MODS = $rbacreview->assignedUsers($posMod['top_mods']);
							
							if (is_array($MODS))
							{
								if (in_array($node->getUpdateUserId(), $MODS))
									$spanClass = 'moderator_small';
							}
						}
		
						$node->setChangeDate($node->getChangeDate());
						
						if ($spanClass == '') $spanClass = 'small';

						$this->ctrl->setParameter($this, 'backurl', $backurl);
						$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
						$this->ctrl->setParameter($this, 'user', $node->getUpdateUserId());

						$this->ctrl->clearParameters($this);

						$tpl->setVariable('POST_UPDATE_TXT', $lng->txt('edited_on').': '.$frm->convertDate($node->getChangeDate()).' - '.strtolower($lng->txt('by')));
						$tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
						if($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized() && !$authorinfo->hasSuffix())
						{
							$tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
						}

					} // if ($node->getUpdateUserId() > 0)*/
					// Author end
		
					// prepare post
					$node->setMessage($frm->prepareText($node->getMessage()));
		
					if($ilUser->getId() == ANONYMOUS_USER_ID ||
					   $node->isPostRead())
					{
						$tpl->setVariable('SUBJECT', $node->getSubject());
					}
					else
					{
						$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
						$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
						$this->ctrl->setParameter($this, 'offset', $Start);
						$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
						$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
						$mark_post_target = $this->ctrl->getLinkTarget($this, 'markPostRead', $node->getId());

						$tpl->setVariable('SUBJECT',"<a href=\"".$mark_post_target."\"><b>".$node->getSubject()."</b></a>");

					}
		
					$tpl->setVariable('POST_DATE', $frm->convertDate($node->getCreateDate()));

					if (!$node->isCensored() ||
						($this->objCurrentPost->getId() == $node->getId() && $_GET['action'] == 'censor'))
					{
						// post from moderator?
						$modAuthor = $frm->getModeratorFromPost($node->getId());
		
						$spanClass = "";
		
						if (is_array($modAuthor) && $modAuthor['top_mods'] > 0)
						{
							unset($MODS);
		
							$MODS = $rbacreview->assignedUsers($modAuthor['top_mods']);
		
							if (is_array($MODS))
							{
								if (in_array($node->getDisplayUserId(), $MODS))
									$spanClass = 'moderator';
							}
						}
						
						// possible bugfix for mantis #8223
						if($node->getMessage() == strip_tags($node->getMessage()))
						{
							// We can be sure, that there are not html tags
							$node->setMessage(nl2br($node->getMessage()));
						}
						
						if ($spanClass != "")
						{
							$tpl->setVariable('POST', "<span class=\"".$spanClass."\">".ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1)."</span>");
						}
						else
						{
							$tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1));
						}
					}
					else
					{
						$tpl->setVariable('POST', "<span class=\"moderator\">".nl2br($node->getCensorshipComment())."</span>");
					}
		
					$tpl->parseCurrentBlock();
		
				}
				$z++;
			}

			$first_node = $this->objCurrentTopic->getFirstPostNode();
			if(
				$first_node instanceof ilForumPost &&
				in_array($this->ctrl->getCmd(), array('createToLevelPost', 'saveTopLevelPost', 'quoteTopLevelPost')) &&
				!$this->objCurrentTopic->isClosed() &&
				$ilAccess->checkAccess('add_reply', '', (int)$_GET['ref_id']))
			{
				// Important: Don't separate the following two lines (very fragile code ...) 
				$this->objCurrentPost->setId($first_node->getId());
				$form = $this->getReplyEditForm();

				if($this->ctrl->getCmd() == 'saveTopLevelPost')
				{
					$form->setValuesByPost();
				}
				else if($this->ctrl->getCmd() == 'quoteTopLevelPost')
				{
					require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
					$authorinfo = new ilForumAuthorInformation(
						$first_node->getPosAuthorId(),
						$first_node->getDisplayUserId(),
						$first_node->getUserAlias(),
						$first_node->getImportName()
					);

					$form->setValuesByPost();
					$form->getItemByPostVar('message')->setValue(
						ilRTE::_replaceMediaObjectImageSrc(
							$frm->prepareText($first_node->getMessage(), 1, $authorinfo->getAuthorName())."\n".$form->getInput('message'),    1
						)
					);
				}
				$this->ctrl->setParameter($this, 'pos_pk', $first_node->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $first_node->getThreadId());
				$jsTpl = new ilTemplate('tpl.forum_post_quoation_ajax_handler.html', true, true, 'Modules/Forum');
				$jsTpl->setVariable('IL_FRM_QUOTE_CALLBACK_SRC',
					$this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true));
				$this->ctrl->clearParameters($this);
				$tpl->setVariable('BOTTOM_FORM_ADDITIONAL_JS', $jsTpl->get());;
				$tpl->setVariable('BOTTOM_FORM', $form->getHTML());
			}
		}
		else
		{
			$tpl->setCurrentBlock('posts_no');
			$tpl->setVariable('TXT_MSG_NO_POSTS_AVAILABLE', $lng->txt('forums_posts_not_available'));
			$tpl->parseCurrentBlock();
		}

		if($bottom_toolbar_split_button_items)
		{
			$bottom_split_button = ilSplitButtonGUI::getInstance();
			$i = 0;
			foreach($bottom_toolbar_split_button_items as $item)
			{
				if($i == 0)
				{
					$bottom_split_button->setDefaultButton($item);
				}
				else
				{
					$bottom_split_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($item));
				}

				++$i;
			}
			$bottom_toolbar->addStickyItem($bottom_split_button);
		}

		$ilToolbar = clone $bottom_toolbar;

		if($bottom_toolbar_split_button_items)
		{
			$bottom_toolbar->addSeparator();
		}

		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$to_top_button = ilLinkButton::getInstance();
		$to_top_button->setCaption('top_of_page');
		$to_top_button->setUrl('#frm_page_top');
		$bottom_toolbar->addButtonInstance($to_top_button);
		$tpl->setVariable('TOOLBAR_BOTTOM', $bottom_toolbar->getHTML());

		include_once 'Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
		$permalink = new ilPermanentLinkGUI('frm', $this->object->getRefId(), '_'.$this->objCurrentTopic->getId());		
		$this->tpl->setVariable('PRMLINK', $permalink->getHTML());

		return true;
	}

	private function getModifiedReOnSubject($on_reply = false)
	{
		$subject = $this->objCurrentPost->getSubject();
		$re_txt = $this->lng->txt('post_reply');

		$re_txt_with_num = str_replace(':', '(',$re_txt);
		$search_length = strlen($re_txt_with_num);
		$comp = substr_compare($re_txt_with_num, substr($subject, 0 , $search_length), 0, $search_length);
		
		if($comp == 0)
		{
			$modified_subject = $subject;
			if($on_reply == true)
			{
				// i.e. $subject = "Re(12):"
				$str_pos_start = strpos($subject, '(');
				$str_pos_end   = strpos($subject, ')');

				$length        = ((int)$str_pos_end - (int)$str_pos_start);
				$str_pos_start++;
				$txt_number = substr($subject, $str_pos_start, $length - 1);

				if(is_numeric($txt_number))
				{
					$re_count         = (int)$txt_number + 1;
					$modified_subject = substr($subject, 0, $str_pos_start) . $re_count . substr($subject, $str_pos_end);
				}
			}
		}
		else
		{
			$re_count = substr_count($subject, $re_txt);
			if($re_count >= 1 && $on_reply == true)
			{
				$subject = str_replace($re_txt, '', $subject);
				
				// i.e. $subject = "Re: Re: Re: ... " -> "Re(4):"
				$re_count++;
				$modified_subject = sprintf($this->lng->txt('post_reply_count'), $re_count).' '.trim($subject);
			}
			else if($re_count >= 1 && $on_reply == false)
			{
				// possibility to modify the subject only for output 
				// i.e. $subject = "Re: Re: Re: ... " -> "Re(3):"
				$modified_subject = sprintf($this->lng->txt('post_reply_count'), $re_count).' '.trim($subject);
			}
			else if($re_count == 0)
			{
				// the first reply to a thread
				$modified_subject = $this->lng->txt('post_reply').' '. $this->objCurrentPost->getSubject();
			}
		}
		return $modified_subject;
	}
	
	public function showUserObject()
	{
		/**
		 * @var $tpl ilTemplate
		 */
		global $tpl;
	
		// we could actually call ilpublicuserprofilegui directly, this method
		// is not needed - but sadly used throughout the forum code
		// see above in execute command
						
		include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
		$profile_gui = new ilPublicUserProfileGUI($_GET['user']);
		$add = $this->getUserProfileAdditional($_GET['ref_id'], $_GET['user']);
		$profile_gui->setAdditional($add);
		$profile_gui->setBackUrl($_GET['backurl']);
		$tpl->setContent($this->ctrl->getHTML($profile_gui));
	}
	
	protected function getUserProfileAdditional($a_forum_ref_id, $a_user_id)
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilAccess ilAccessHandler
		 */
		global $lng, $ilAccess;
		
		if(!$ilAccess->checkAccess('read', '', $a_forum_ref_id))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		require_once 'Modules/Forum/classes/class.ilForum.php';
		
		$lng->loadLanguageModule('forum');
		
		/**
		 * @var $ref_obj ilObjForum
		 */
		$ref_obj = ilObjectFactory::getInstanceByRefId($a_forum_ref_id);
		if($ref_obj->getType() == 'frm')
		{
			$forumObj = new ilObjForum($a_forum_ref_id);
			$frm = $forumObj->Forum;
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
		}
		else
		{
			$frm = new ilForum();
		}
		
		// count articles of user
		if($ilAccess->checkAccess('moderate_frm', '', $a_forum_ref_id))
		{
			$numPosts = $frm->countUserArticles(addslashes($a_user_id));
		}
		else
		{
			$numPosts = $frm->countActiveUserArticles(addslashes($a_user_id));
		}
		
		return array($lng->txt('forums_posts') => $numPosts);
	}

	public function performThreadsActionObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		unset($_SESSION['threads2move']);

		if(isset($_POST['thread_ids']) && is_array($_POST['thread_ids']))
		{
			if(isset($_POST['selected_cmd']) && $_POST['selected_cmd'] == 'move')
			{
				if($this->is_moderator)
				{
					$_SESSION['threads2move'] = $_POST['thread_ids'];
					$this->moveThreadsObject();
				}
			}
			else if($_POST['selected_cmd'] == 'enable_notifications' && $this->ilias->getSetting('forum_notification') != 0)
			{
				for($i = 0; $i < count($_POST['thread_ids']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
					$tmp_obj->enableNotification($ilUser->getId());
					unset($tmp_obj);
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'disable_notifications' && $this->ilias->getSetting('forum_notification') != 0)
			{
				for($i = 0; $i < count($_POST['thread_ids']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
					$tmp_obj->disableNotification($ilUser->getId());
					unset($tmp_obj);
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}

			else if($_POST['selected_cmd'] == 'close')
			{
				if($this->is_moderator)
				{
					for($i = 0; $i < count($_POST['thread_ids']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$tmp_obj->close();
						unset($tmp_obj);
					}
				}
				ilUtil::sendSuccess($this->lng->txt('selected_threads_closed'), true);
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'reopen')
			{
				if($this->is_moderator)
				{
					for($i = 0; $i < count($_POST['thread_ids']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$tmp_obj->reopen();
						unset($tmp_obj);
					}
				}
	
				ilUtil::sendSuccess($this->lng->txt('selected_threads_reopened'), true);
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'makesticky')
			{
				if($this->is_moderator)
				{
					$message = $this->lng->txt('sel_threads_make_sticky');
					
					for($i = 0; $i < count($_POST['thread_ids']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$makeSticky =  $tmp_obj->makeSticky();

						if(!$makeSticky)
						{
							$message = $this->lng->txt('sel_threads_already_sticky');
						}

						unset($tmp_obj);
					}
				}
				if($message != null)
				{
					ilUtil::sendInfo($message,true);
				}
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'unmakesticky')
			{
				if($this->is_moderator)
				{
					$message = $this->lng->txt('sel_threads_make_unsticky');
					for($i = 0; $i < count($_POST['thread_ids']); $i++)
					{
						$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
						$unmakeSticky = $tmp_obj->unmakeSticky();
						if(!$unmakeSticky)
						{
							$message = $this->lng->txt('sel_threads_already_unsticky');
						}
						
						unset($tmp_obj);
					}
				}
				
				if($message != null)
				{
					ilUtil::sendInfo($message,true);
				}
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'editThread')
			{
				if($this->is_moderator)
				{
					$count = count($_POST['thread_ids']);
					if($count != 1)
					{
						ilUtil::sendInfo($this->lng->txt('select_max_one_thread'), true);
						$this->ctrl->redirect($this, 'showThreads');
					}
					else
					{
						foreach($_POST['thread_ids'] as $thread_id)
						{
							return $this->editThreadObject($thread_id, null);
						}
					}
				}

				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'html')
			{
				$this->ctrl->setCmd('exportHTML');
				$this->ctrl->setCmdClass('ilForumExportGUI');
				$this->executeCommand();
			}
			else if($_POST['selected_cmd'] == 'confirmDeleteThreads')
			{
				$this->confirmDeleteThreads();
			}
			else if($_POST['selected_cmd'] == 'merge')
			{
				$this->mergeThreadsObject();
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('topics_please_select_one_action'), true);
				$this->ctrl->redirect($this, 'showThreads');
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}
	}
	
	public function performMoveThreadsObject()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		global $lng, $ilObjDataCache;
	
		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		if(isset($_POST['frm_ref_id']) && (int) $_POST['frm_ref_id'])
		{	
			$this->object->Forum->moveThreads((array) $_SESSION['threads2move'], $this->object->getRefId(), $ilObjDataCache->lookupObjId($_POST['frm_ref_id']));

			unset($_SESSION['threads2move']);
			ilUtil::sendInfo($lng->txt('threads_moved_successfully'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}
		else
		{
			ilUtil::sendInfo($lng->txt('no_forum_selected'));
			$this->moveThreadsObject();
		}
	}
	
	public function cancelMoveThreadsObject()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		unset($_SESSION['threads2move']);
		
		$this->ctrl->redirect($this, 'showThreads');
	}
	
	public function moveThreadsObject()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilToolbar ilToolbarGUI
		 * @var $tree ilTree
		 */
		global $lng, $ilToolbar, $tree;

		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$threads2move = $_SESSION['threads2move'];
		if(!count($threads2move))
		{
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}

		require_once 'Modules/Forum/classes/class.ilForumMoveTopicsExplorer.php';
		$exp = new ilForumMoveTopicsExplorer($this, 'moveThreads');
		$exp->setPathOpen($this->object->getRefId());
		$exp->setNodeSelected(isset($_POST['frm_ref_id']) && (int)$_POST['frm_ref_id'] ? (int)$_POST['frm_ref_id'] : 0);
		$exp->setCurrentFrmRefId($this->object->getRefId());
		$exp->setHighlightedNode($this->object->getRefId());
		if(!$exp->handleCommand())
		{
			$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_move.html', 'Modules/Forum');

			if(!$this->hideToolbar())
				$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));

			$tblThr = new ilTable2GUI($this);
			$tblThr->setId('il_frm_thread_move_table_'.$this->object->getRefId());
			$tblThr->setTitle($this->lng->txt('move_chosen_topics'));
			$tblThr->addColumn($this->lng->txt('subject'), 'top_name', '100%');
			$tblThr->disable('header');
			$tblThr->disable('footer');
			$tblThr->disable('linkbar');
			$tblThr->disable('sort');
			$tblThr->disable('linkbar');
			$tblThr->setLimit(PHP_INT_MAX);
			$tblThr->setRowTemplate('tpl.forums_threads_move_thr_row.html', 'Modules/Forum');
			$tblThr->setDefaultOrderField('is_sticky');
			$counter = 0;
			$result = array();
			foreach($threads2move as $thr_pk)
			{
				$objCurrentTopic = new ilForumTopic($thr_pk, $this->is_moderator);

				$result[$counter]['num'] = $counter + 1;
				$result[$counter]['thr_subject'] = $objCurrentTopic->getSubject();

				unset($objCurrentTopic);
				++$counter;
			}
			$tblThr->setData($result);
			$this->tpl->setVariable('THREADS_TABLE', $tblThr->getHTML());

			$this->tpl->setVariable('FRM_SELECTION_TREE', $exp->getHTML());
			$this->tpl->setVariable('CMD_SUBMIT', 'performMoveThreads');
			$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('move'));
			$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this, 'performMoveThreads'));
		}

		return true;
	}
	
	private function isWritingWithPseudonymAllowed()
	{
		if($this->objProperties->isAnonymized()
		&& (!$this->is_moderator || ($this->is_moderator && !$this->objProperties->getMarkModeratorPosts()))) 
		{
			return true;
		}
		return false;
	}
	
	private function initTopicCreateForm()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $rbacsystem ilRbacSystem
		 * @var $ilias ILIAS
		 * @var $ilSetting ilSetting
		 */
		global $ilUser, $rbacsystem, $ilias, $ilSetting;
		
		$this->create_topic_form_gui = new ilPropertyFormGUI();
		
		$this->create_topic_form_gui->setTitle($this->lng->txt('forums_new_thread'));
		$this->create_topic_form_gui->setTitleIcon(ilUtil::getImagePath('icon_frm.svg'));
		$this->create_topic_form_gui->setTableWidth('100%');
				
		// form action
		$this->create_topic_form_gui->setFormAction($this->ctrl->getFormAction($this, 'addThread'));

		if($this->isWritingWithPseudonymAllowed())
		{
			$alias_gui = new ilTextInputGUI($this->lng->txt('forums_your_name'), 'alias');
			$alias_gui->setInfo($this->lng->txt('forums_use_alias'));
			$alias_gui->setMaxLength(255);
			$alias_gui->setSize(50);
			$this->create_topic_form_gui->addItem($alias_gui);
		}
		else
		{
			$alias_gui = new ilNonEditableValueGUI($this->lng->txt('forums_your_name'), 'alias');
			$alias_gui->setValue($ilUser->getLogin());
			$this->create_topic_form_gui->addItem($alias_gui);
		}
		
		// topic
		$subject_gui = new ilTextInputGUI($this->lng->txt('forums_thread'), 'subject');
		$subject_gui->setMaxLength(255);
		$subject_gui->setSize(50);
		$subject_gui->setRequired(true);
		$this->create_topic_form_gui->addItem($subject_gui);
		
		// message
		$post_gui = new ilTextAreaInputGUI($this->lng->txt('forums_the_post'), 'message');
		$post_gui->setCols(50);
		$post_gui->setRows(15);
		$post_gui->setRequired(true);
		$post_gui->setUseRte(true);
		$post_gui->addPlugin('latex');
		$post_gui->addButton('latex');
		$post_gui->addButton('pastelatex');
		$post_gui->addPlugin('ilfrmquote');
		//$post_gui->addPlugin('code'); 
		$post_gui->removePlugin('advlink');
		$post_gui->usePurifier(true);
		$post_gui->setRTERootBlockElement('');
		$post_gui->setRTESupport($ilUser->getId(), 'frm~', 'frm_post', 'tpl.tinymce_frm_post.html', false, '3.4.7');
		$post_gui->disableButtons(array(
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'formatselect'
		));

		// purifier
		require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
		$post_gui->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));
		$this->create_topic_form_gui->addItem($post_gui);

		if($this->objProperties->isFileUploadAllowed())
		{
			$fi = new ilFileWizardInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
			$fi->setFilenames(array(0 => ''));
			$this->create_topic_form_gui->addItem($fi);
		}

		include_once 'Services/Mail/classes/class.ilMail.php';
		$umail = new ilMail($ilUser->getId());
		// catch hack attempts
		if($rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId()) &&
		   !$this->objProperties->isAnonymized())
		{
			// direct notification
			$dir_notification_gui = new ilCheckboxInputGUI($this->lng->txt('forum_direct_notification'), 'notify');
			$dir_notification_gui->setInfo($this->lng->txt('forum_notify_me_directly'));
			$dir_notification_gui->setValue(1);
			$this->create_topic_form_gui->addItem($dir_notification_gui);
			
			if($ilias->getSetting('forum_notification') != 0)
			{
				// gen. notification
				$gen_notification_gui = new ilCheckboxInputGUI($this->lng->txt('forum_general_notification'), 'notify_posts');
				$gen_notification_gui->setInfo($this->lng->txt('forum_notify_me_generally'));
				$gen_notification_gui->setValue(1);
				$this->create_topic_form_gui->addItem($gen_notification_gui);
			}
		}

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		if(
			$ilUser->isAnonymous() &&
			!$ilUser->isCaptchaVerified() &&
			ilCaptchaUtil::isActiveForForum()
		)
		{
			require_once 'Services/Captcha/classes/class.ilCaptchaInputGUI.php';			
			$captcha = new ilCaptchaInputGUI($this->lng->txt('cont_captcha_code'), 'captcha_code');
			$captcha->setRequired(true);		
			$this->create_topic_form_gui->addItem($captcha);
		}
		$this->create_topic_form_gui->addCommandButton('addThread', $this->lng->txt('create'));
		$this->create_topic_form_gui->addCommandButton('showThreads', $this->lng->txt('cancel'));
	}
	
	private function setTopicCreateDefaultValues()
	{
		$this->create_topic_form_gui->setValuesByArray(array(
			'subject' => '',
			'message' => '',
			'userfile' => '',
			'notify' => 0,
			'notify_posts' => 0
		));
	}

	public function createThreadObject()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $lng ilLanguage
		 */
		global $ilAccess, $lng;

		if(!$ilAccess->checkAccess('add_thread', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$this->initTopicCreateForm();
		$this->setTopicCreateDefaultValues();
		
		$create_form = new ilTemplate('tpl.create_thread_form.html', true, true, 'Modules/Forum');
		$create_form->setVariable('CREATE_FORM',$this->create_topic_form_gui->getHTML());
		$create_form->parseCurrentBlock();
		
		$this->tpl->setContent($create_form->get());
	}
	
	public function addThreadObject($a_prevent_redirect = false)
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilAccess ilAccessHandler
		 * @var $lng ilLanguage
		 */
		global $ilUser, $ilAccess, $lng;
		
		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		
		if(!$ilAccess->checkAccess('add_thread', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		
		$topicData = $frm->getOneTopic();

		$this->initTopicCreateForm();
		if($this->create_topic_form_gui->checkInput())
		{
			require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
			if(
				$ilUser->isAnonymous() &&
				!$ilUser->isCaptchaVerified() &&
				ilCaptchaUtil::isActiveForForum()
			)
			{
				$ilUser->setCaptchaVerified(true);
			}

			if($this->isWritingWithPseudonymAllowed())
			{
				if(!strlen($this->create_topic_form_gui->getInput('alias')))
				{
					$user_alias = $this->lng->txt('forums_anonymous');
				}
				else
				{
					$user_alias = $this->create_topic_form_gui->getInput('alias');
				}
				$display_user_id = 0;
			}
			else
			{
				$user_alias = $ilUser->getLogin();
				$display_user_id = $ilUser->getId();
			}
			
			$status = 1;
			if(
				$this->objProperties->isPostActivationEnabled() &&
				!$this->is_moderator || $this->objCurrentPost->isAnyParentDeactivated()
			)
			{
				$status = 0;
			}

			// build new thread
			$newPost = $frm->generateThread(
				$topicData['top_pk'],
				$ilUser->getId(),
				$display_user_id,
				$this->handleFormInput($this->create_topic_form_gui->getInput('subject'), false),
				ilRTE::_replaceMediaObjectImageSrc($this->create_topic_form_gui->getInput('message'), 0),
				$this->create_topic_form_gui->getItemByPostVar('notify') ? (int)$this->create_topic_form_gui->getInput('notify') : 0,
				$this->create_topic_form_gui->getItemByPostVar('notify_posts') ? (int)$this->create_topic_form_gui->getInput('notify_posts') : 0,
				$user_alias,
				'',
				$status
			);

			if($this->objProperties->isFileUploadAllowed())
			{
				$file = $_FILES['userfile'];
				if(is_array($file) && !empty($file))
				{
					$tmp_file_obj = new ilFileDataForum($this->object->getId(), $newPost);
					$tmp_file_obj->storeUploadedFile($file);
				}
			}

			// Visit-Counter
			$frm->setDbTable('frm_data');
			$frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topicData['top_pk']));
			$frm->updateVisits($topicData['top_pk']);			

			$frm->setMDB2WhereCondition('thr_top_fk = %s AND thr_subject = %s AND thr_num_posts = 1 ', 
										array('integer', 'text'), array($topicData['top_pk'], $this->create_topic_form_gui->getInput('subject')));
			
			// copy temporary media objects (frm~)
			include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
			$mediaObjects = ilRTE::_getMediaObjects($this->create_topic_form_gui->getInput('message'), 0);
			foreach($mediaObjects as $mob)
			{
				if(ilObjMediaObject::_exists($mob))
				{
					ilObjMediaObject::_removeUsage($mob, 'frm~:html', $ilUser->getId());
					ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
				}
			}
			
			$GLOBALS['ilAppEventHandler']->raise(
				'Modules/Forum',
				'createdPost',
				array(
					'ref_id'            => $this->object->getRefId(),
					'post'              => new ilForumPost($newPost),
					'notify_moderators' => !$status
				)
			);
				
			if(!$a_prevent_redirect)
			{
				ilUtil::sendSuccess($this->lng->txt('forums_thread_new_entry'), true);
				$this->ctrl->redirect($this);
			}
			else
			{
				return $newPost;
			}
		}
		else
		{
			$this->create_topic_form_gui->setValuesByPost();

			if(!$this->objProperties->isAnonymized())
			{
				$this->create_topic_form_gui->getItemByPostVar('alias')->setValue($ilUser->getLogin());
			}

			return $this->tpl->setContent($this->create_topic_form_gui->getHTML());
		}
	}
	
	public function enableForumNotificationObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;
 
		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->enableForumNotification($ilUser->getId());
		
		if(!$this->objCurrentTopic->getId())
		{
			ilUtil::sendInfo($this->lng->txt('forums_forum_notification_enabled'));
			$this->showThreadsObject();
		}
		else
		{
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			ilUtil::sendInfo($this->lng->txt('forums_forum_notification_enabled'), true);
			$this->ctrl->redirect($this, 'viewThread');
		}
	}

	public function disableForumNotificationObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;
		
		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->disableForumNotification($ilUser->getId());
		
		if(!$this->objCurrentTopic->getId())
		{
			$this->showThreadsObject();
			ilUtil::sendInfo($this->lng->txt('forums_forum_notification_disabled'));
		}
		else
		{
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			ilUtil::sendInfo($this->lng->txt('forums_forum_notification_disabled'), true);
			$this->ctrl->redirect($this, 'viewThread');
		}
	}
	
	public function checkEnableColumnEdit()
	{
		return false;
	}

	public function setColumnSettings(ilColumnGUI $column_gui)
	{
		/** 
		 * @var $lng ilLanguage
		 * @var $ilAccess ilAccessHandler
		 */
		global $lng, $ilAccess;

		$column_gui->setBlockProperty('news', 'title', $lng->txt('frm_latest_postings'));
		$column_gui->setBlockProperty('news', 'prevent_aggregation', true);
		$column_gui->setRepositoryMode(true);
		
		if($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$news_set = new ilSetting('news');
			$enable_internal_rss = $news_set->get('enable_rss_for_internal');
			if($enable_internal_rss)
			{
				$column_gui->setBlockProperty('news', 'settings', true);
				$column_gui->setBlockProperty('news', 'public_notifications_option', true);
			}
		}
	}
	
	
	public function cloneWizardPageObject()
	{
		global $ilObjDataCache;
		
	 	if (!$_POST['clone_source'])
	 	{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			if (isset($_SESSION['wizard_search_title']))
			{
				$this->searchCloneSourceObject();
			}
			else
			{
				$this->createObject();
			}
			return false;
	 	}
		$source_id = $_POST['clone_source'];

	 	$new_type = $_REQUEST['new_type'];
	 	$this->ctrl->setParameter($this, 'clone_source', (int) $_POST['clone_source']);
	 	$this->ctrl->setParameter($this, 'new_type', $new_type);
	 	
	 	$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.frm_wizard_page.html', 'Modules/Forum');
	 	$this->tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TYPE_IMG', ilUtil::getImagePath('icon_'.$new_type.'.svg'));
	 	$this->tpl->setVariable('ALT_IMG', $this->lng->txt('obj_'.$new_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE', $this->lng->txt('frm_wizard_page'));
	 	$this->tpl->setVariable('INFO_THREADS', $this->lng->txt('fmr_copy_threads_info'));
	 	$this->tpl->setVariable('THREADS', $this->lng->txt('forums_threads'));
	 	
	 	$forum_id = $ilObjDataCache->lookupObjId((int) $_POST['clone_source']);
	 	include_once('Modules/Forum/classes/class.ilForum.php');
	 	$threads = ilForum::_getThreads($forum_id, ilForum::SORT_TITLE);
	 	foreach ($threads as $thread_id => $title)
	 	{
	 		$this->tpl->setCurrentBlock('thread_row');
	 		$this->tpl->setVariable('CHECK_THREAD', ilUtil::formCheckbox(0, 'cp_options['.$source_id.'][threads][]', $thread_id));
	 		$this->tpl->setVariable('NAME_THREAD', $title);
	 		$this->tpl->parseCurrentBlock();
	 	}
	 	$this->tpl->setVariable('SELECT_ALL', $this->lng->txt('select_all'));
	 	$this->tpl->setVariable('JS_FIELD', 'cp_options['.$source_id.'][threads]');
	 	$this->tpl->setVariable('BTN_COPY', $this->lng->txt('obj_'.$new_type.'_duplicate'));
	 	if (isset($_SESSION['wizard_search_title']))
	 	{
	 		$this->tpl->setVariable('BACK_CMD', 'searchCloneSource');
	 	}
	 	else
	 	{
	 		$this->tpl->setVariable('BACK_CMD', 'create');
	 	}
 		$this->tpl->setVariable('BTN_BACK', $this->lng->txt('btn_back'));
	}
	
	public function addLocatorItems()
	{
		/** 
		 * @var $ilLocator ilLocatorGUI */
		global $ilLocator;
		
		if($this->object instanceof ilObject)
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ''), '', $this->object->getRefId());
		}
	}

	public function handleFormInput($a_text, $a_stripslashes = true)
	{
		$a_text = str_replace("<", "&lt;", $a_text);
		$a_text = str_replace(">", "&gt;", $a_text);
		if($a_stripslashes)
			$a_text = ilUtil::stripSlashes($a_text);
		
		return $a_text;
	}
	
	public function prepareFormOutput($a_text)
	{
		$a_text = str_replace("&lt;", "<", $a_text);
		$a_text = str_replace("&gt;", ">", $a_text);
		$a_text = ilUtil::prepareFormOutput($a_text);
		return $a_text;
	}

	/**
	 * this one is called from the info button in the repository
	 * not very nice to set cmdClass/Cmd manually, if everything
	 * works through ilCtrl in the future this may be changed
	 */
	public function infoScreenObject()
	{
		$this->ctrl->setCmd('showSummary');
		$this->ctrl->setCmdClass('ilinfoscreengui');
		$this->infoScreen();
	}

	public function infoScreen()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		global $ilAccess;

		if(!$ilAccess->checkAccess('visible', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}

		include_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();

		// standard meta data
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

		// forward the command
		$this->ctrl->forwardCommand($info);
	}

	public function updateNotificationSettingsObject()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr		ilErr
		 */
		global $ilAccess, $ilErr;
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		// instantiate the property form
		$this->initNotificationSettingsForm();

		// check input
		if($this->notificationSettingsForm->checkInput())
		{
			if(isset($_POST['notification_type']) && $_POST['notification_type']== 'all_users')
			{
				// set values and call update
				$this->objProperties->setAdminForceNoti(1);
				$this->objProperties->setUserToggleNoti((int) $this->notificationSettingsForm->getInput('usr_toggle'));
				$this->objProperties->setNotificationType('all_users');
				$this->updateUserNotifications(true);
			}
			else if($_POST['notification_type']== 'per_user')
			{
				$this->objProperties->setNotificationType('per_user');
				$this->objProperties->setAdminForceNoti(1);
				$this->objProperties->setUserToggleNoti(0);
				$this->updateUserNotifications();
			}
			else //  if($_POST['notification_type'] == 'default')
			{
				$this->objProperties->setNotificationType('default');
				$this->objProperties->setAdminForceNoti(0);
				$this->objProperties->setUserToggleNoti(0);
				include_once 'Modules/Forum/classes/class.ilForumNotification.php';
				$frm_noti = new ilForumNotification($this->object->getRefId());
				$frm_noti->deleteNotificationAllUsers();					
			}

			$this->objProperties->update();

			// print success message
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}
		$this->notificationSettingsForm->setValuesByPost();

		return $this->showMembersObject();
	}

	private function updateUserNotifications($update_all_users = false)
	{
		include_once 'Modules/Forum/classes/class.ilForumNotification.php';

		$oParticipants = $this->getParticipantsObject();

		$frm_noti = new ilForumNotification($this->object->getRefId());
		$moderator_ids = ilForum::_getModerators($this->object->getRefId());

		$admin_ids = $oParticipants->getAdmins();
		$member_ids = $oParticipants->getMembers();
		$tutor_ids = $oParticipants->getTutors();

		$all_forum_users = array_merge($moderator_ids, $admin_ids, $member_ids, $tutor_ids);
		$all_forum_users= array_unique($all_forum_users);

		$all_notis = $frm_noti->read();

		foreach($all_forum_users as $user_id)
		{
			$frm_noti->setUserId($user_id);

			$frm_noti->setAdminForce(1);
			$frm_noti->setUserToggle($this->objProperties->isUserToggleNoti());

			if(array_key_exists($user_id, $all_notis) && $update_all_users)
			{
				$frm_noti->update();
			}
			else if($frm_noti->existsNotification() == false)
			{
				$frm_noti->insertAdminForce();
			}
		}
	}
	
	private function initNotificationSettingsForm()
	{
		if(null === $this->notificationSettingsForm)
		{
			$form = new ilPropertyFormGUI();
			$form->setFormAction($this->ctrl->getFormAction($this, 'updateNotificationSettings'));
			$form->setTitle($this->lng->txt('forums_notification_settings'));

			$radio_grp = new ilRadioGroupInputGUI('','notification_type');
			$radio_grp->setValue('default');

			$opt_default  = new ilRadioOption($this->lng->txt("user_decides_notification"), 'default');
			$opt_0 = new ilRadioOption($this->lng->txt("settings_for_all_members"), 'all_users');
			$opt_1 = new ilRadioOption($this->lng->txt("settings_per_users"), 'per_user');

			$radio_grp->addOption($opt_default, 'default');
			$radio_grp->addOption($opt_0, 'all_users');
			$radio_grp->addOption($opt_1, 'per_user');

			$chb_2 = new ilCheckboxInputGUI($this->lng->txt('user_toggle_noti'), 'usr_toggle');
			$chb_2->setValue(1);

			$opt_0->addSubItem($chb_2);
			$form->addItem($radio_grp);

			$form->addCommandButton('updateNotificationSettings', $this->lng->txt('save'));

			$this->notificationSettingsForm = $form;

			return false;
		}

		return true;
	}
	
	public function getIcon($user_toggle_noti)
	{
		$icon = $user_toggle_noti
		? "<img src=\"".ilUtil::getImagePath("icon_ok.svg")."\" alt=\"".$this->lng->txt("enabled")."\" title=\"".$this->lng->txt("enabled")."\" border=\"0\" vspace=\"0\"/>"
		: "<img src=\"".ilUtil::getImagePath("icon_not_ok.svg")."\" alt=\"".$this->lng->txt("disabled")."\" title=\"".$this->lng->txt("disabled")."\" border=\"0\" vspace=\"0\"/>";
		return $icon;
	}
	
	public function showMembersObject()
	{
		/**
		 * @var $tree ilTree
		 * @var $tpl ilTemplate
		 * @var $ilTabs ilTabsGUI
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr		ilErr
		 */
		global $tree, $tpl, $ilTabs, $ilAccess, $ilErr;

		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_members_list.html', 'Modules/Forum');

		$ilTabs->setTabActive('settings');
		$this->settingsTabs();
		
		// instantiate the property form
		if(!$this->initNotificationSettingsForm())
		{
			// if the form was just created set the values fetched from database
			$this->notificationSettingsForm->setValuesByArray(array(
				'notification_type' => $this->objProperties->getNotificationType(),
				'adm_force' => (bool) $this->objProperties->isAdminForceNoti(),
				'usr_toggle' => (bool) $this->objProperties->isUserToggleNoti()
			));
		}

		// set form html into template
		$tpl->setVariable('NOTIFICATIONS_SETTINGS_FORM', $this->notificationSettingsForm->getHTML());

		include_once 'Modules/Forum/classes/class.ilForumNotification.php';
		include_once 'Modules/Forum/classes/class.ilObjForum.php';

		$frm_noti = new ilForumNotification($this->object->getRefId());
		$oParticipants = $this->getParticipantsObject();

		$moderator_ids = ilForum::_getModerators($this->object->getRefId());

		$admin_ids = $oParticipants->getAdmins();
		$member_ids = $oParticipants->getMembers();
		$tutor_ids = $oParticipants->getTutors();

		if($this->objProperties->getNotificationType() == 'default')
		{
			// update forum_notification table
			include_once 'Modules/Forum/classes/class.ilForumNotification.php';
			$forum_noti = new ilForumNotification($this->object->getRefId());
			$forum_noti->setAdminForce($this->objProperties->isAdminForceNoti());
			$forum_noti->setUserToggle($this->objProperties->isUserToggleNoti());
			$forum_noti->setForumId($this->objProperties->getObjId());
		}
		else if($this->objProperties->getNotificationType() == 'per_user')
		{
			$moderators = $this->getUserNotificationTableData($moderator_ids, $frm_noti);
			$admins = $this->getUserNotificationTableData($admin_ids, $frm_noti);
			$members = $this->getUserNotificationTableData($member_ids, $frm_noti);
			$tutors = $this->getUserNotificationTableData($tutor_ids, $frm_noti);

			$this->__showMembersTable($moderators, $admins, $members, $tutors);
		}
	}

	private function getUserNotificationTableData($user_ids, ilForumNotification $frm_noti)
	{
		$counter = 0;
		$users = array();
		foreach($user_ids as $user_id)
		{
			$frm_noti->setUserId($user_id);
			$user_toggle_noti = $frm_noti->isUserToggleNotification();
			$icon_ok = $this->getIcon(!$user_toggle_noti);

			$users[$counter]['user_id'] = ilUtil::formCheckbox(0, 'user_id[]', $user_id);
			$users[$counter]['login'] = ilObjUser::_lookupLogin($user_id);
			$name = ilObjUser::_lookupName($user_id);
			$users[$counter]['firstname'] = $name['firstname'];
			$users[$counter]['lastname'] = $name['lastname'];
			$users[$counter]['user_toggle_noti'] = $icon_ok;
			$counter++;
		}
		return $users;
	}
	
	private function __showMembersTable($moderators, $admins, $members, $tutors)
	{
		/**
		 * @var $lng ilLanguage
		 * @var $tpl ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $tpl, $ilCtrl;

		if($moderators)
		{
			$tbl_mod = new ilTable2GUI($this);
			$tbl_mod->setId('tbl_id_mod');
			$tbl_mod->setFormAction($ilCtrl->getFormAction($this, 'showMembers'));
			$tbl_mod->setTitle($lng->txt('moderators'));

			$tbl_mod->addColumn('', '', '1%', true);
			$tbl_mod->addColumn($lng->txt('login'), '', '10%');
			$tbl_mod->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_mod->addColumn($lng->txt('lastname'), '', '10%');
			$tbl_mod->addColumn($lng->txt('allow_user_toggle_noti'), '', '10%');
			$tbl_mod->setSelectAllCheckbox('user_id');

			$tbl_mod->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
			$tbl_mod->setData($moderators);

			$tbl_mod->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_mod->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('moderators_table');
			$tpl->setVariable('MODERATORS',$tbl_mod->getHTML());
		}

		if($admins)
		{
			$tbl_adm = new ilTable2GUI($this);
			$tbl_adm->setId('tbl_id_adm');
			$tbl_adm->setFormAction($ilCtrl->getFormAction($this, 'showMembers'));
			$tbl_adm->setTitle($lng->txt('administrator'));

			$tbl_adm->addColumn('', '', '1%', true);
			$tbl_adm->addColumn($lng->txt('login'), '', '10%');
			$tbl_adm->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_adm->addColumn($lng->txt('lastname'), '', '10%');
			$tbl_adm->addColumn($lng->txt('allow_user_toggle_noti'), '', '10%');
			$tbl_adm->setSelectAllCheckbox('user_id');
			$tbl_adm->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');

			$tbl_adm->setData($admins);
			$tbl_adm->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_adm->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('admins_table');
			$tpl->setVariable('ADMINS',$tbl_adm->getHTML());
		}

		if($members)
		{
			$tbl_mem = new ilTable2GUI($this);
			$tbl_mem->setId('tbl_id_mem');
			$tbl_mem->setFormAction($ilCtrl->getFormAction($this, 'showMembers'));
			$tbl_mem->setTitle($lng->txt('members'));

			$tbl_mem->addColumn('', '', '1%', true);
			$tbl_mem->addColumn($lng->txt('login'), '', '10%');
			$tbl_mem->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_mem->addColumn($lng->txt('lastname'), '', '10%');
			$tbl_mem->addColumn($lng->txt('allow_user_toggle_noti'), '', '10%');
			$tbl_mem->setSelectAllCheckbox('user_id');
			$tbl_mem->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
			$tbl_mem->setData($members);

			$tbl_mem->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_mem->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('members_table');
			$tpl->setVariable('MEMBERS',$tbl_mem->getHTML());
		}
		
		if($tutors)
		{
			$tbl_tut = new ilTable2GUI($this);
			$tbl_tut->setId('tbl_id_tut');
			$tbl_tut->setFormAction($ilCtrl->getFormAction($this, 'showMembers'));
			$tbl_tut->setTitle($lng->txt('tutors'));

			$tbl_tut->addColumn('', '', '1%', true);
			$tbl_tut->addColumn($lng->txt('login'), '', '10%');
			$tbl_tut->addColumn($lng->txt('firstname'), '', '10%');
			$tbl_tut->addColumn($lng->txt('lastname'), '', '10%');
			$tbl_tut->addColumn($lng->txt('allow_user_toggle_noti'), '', '10%');
			$tbl_tut->setSelectAllCheckbox('user_id');
			$tbl_tut->setRowTemplate('tpl.forums_members_row.html', 'Modules/Forum');
			$tbl_tut->setData($tutors);

			$tbl_tut->addMultiCommand('enableHideUserToggleNoti',$lng->txt('enable_hide_user_toggle'));
			$tbl_tut->addMultiCommand('disableHideUserToggleNoti',$lng->txt('disable_hide_user_toggle'));

			$tpl->setCurrentBlock('tutors_table');
			$tpl->setVariable('TUTORS',$tbl_tut->getHTML());
		}
	}

	public function enableAdminForceNotiObject()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr		ilErr
		 */
		global $ilAccess, $ilErr;
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		if(!isset($_POST['user_id']) || !is_array($_POST['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'), true);
		}
		else
		{
			include_once 'Modules/Forum/classes/class.ilForumNotification.php';
			$frm_noti = new ilForumNotification($this->object->getRefId());
			
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId((int) $user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();

				$frm_noti->setUserToggle(0);
				if(!$is_enabled)
				{
					$frm_noti->setAdminForce(1);
					$frm_noti->insertAdminForce();
				}
			}

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$this->showMembersObject();
	}

	public function disableAdminForceNotiObject()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr		ilErr
		 */
		global $ilAccess, $ilErr;
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		if(!isset($_POST['user_id']) || !is_array($_POST['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
		}
		else
		{
			include_once 'Modules/Forum/classes/class.ilForumNotification.php';
			$frm_noti = new ilForumNotification($this->object->getRefId());
			
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId((int) $user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();

				if($is_enabled)
				{
					$frm_noti->deleteAdminForce();
				}
			}

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$this->showMembersObject();
	}

	public function enableHideUserToggleNotiObject()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr		ilErr
		 */
		global $ilAccess, $ilErr;
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		if(!isset($_POST['user_id']) || !is_array($_POST['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
		}
		else
		{
			include_once 'Modules/Forum/classes/class.ilForumNotification.php';
			$frm_noti = new ilForumNotification($this->object->getRefId());
			
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId((int) $user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();
				$frm_noti->setUserToggle(1);

				if(!$is_enabled)
				{
					$frm_noti->setAdminForce(1);
					$frm_noti->insertAdminForce();
				}
				else
				{
					$frm_noti->updateUserToggle();
				}
			}

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$this->showMembersObject();
	}

	public function disableHideUserToggleNotiObject()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr		ilErr
		 */
		global $ilAccess, $ilErr;
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		if(!isset($_POST['user_id']) || !is_array($_POST['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_no_users_selected'));
		}
		else
		{
			include_once 'Modules/Forum/classes/class.ilForumNotification.php';
			$frm_noti = new ilForumNotification($this->object->getRefId());
			
			foreach($_POST['user_id'] as $user_id)
			{
				$frm_noti->setUserId((int) $user_id);
				$is_enabled = $frm_noti->isAdminForceNotification();
				$frm_noti->setUserToggle(0);
				if($is_enabled)
				{
					$frm_noti->updateUserToggle();
				}
				else
				{
					$frm_noti->setAdminForce(1);
					$frm_noti->insertAdminForce();
				}
			}

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$this->showMembersObject();
	}

	public function markPostUnreadObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;
		
		if(isset($_GET['pos_pk']))
		{
			$this->object->markPostUnread($ilUser->getId(), (int) $_GET['pos_pk']);
		}
		
		$this->viewThreadObject();
	}

	public function markPostReadObject()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$this->object->markPostRead($ilUser->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());
		$this->viewThreadObject();
	}

	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$lg = parent::initHeaderAction();

		// Workaround: Do not show "desktop actions" in thread view
		if($this->objCurrentTopic->getId())
		{
			$container_obj = null;
			$lg->setContainerObject($container_obj);
		}

		if($lg instanceof ilObjForumListGUI)
		{
			if($ilUser->getId() != ANONYMOUS_USER_ID && $this->ilias->getSetting('forum_notification') != 0 )
			{
				$is_user_allowed_to_deactivate_notification = $this->isUserAllowedToDeactivateNotification();

				$frm = $this->object->Forum;
				$frm->setForumId($this->object->getId());
				$frm->setForumRefId($this->object->getRefId());
				$frm->setMDB2Wherecondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
				$frm_notificiation_enabled = $frm->isForumNotificationEnabled($ilUser->getId());
				
				if($this->objCurrentTopic->getId())
				{
					$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
				}

				if($this->isParentObjectCrsOrGrp())
				{
					// special behaviour for CRS/GRP-Forum notification!!
					if(
						$frm_notificiation_enabled &&
						$is_user_allowed_to_deactivate_notification
					)
					{
						$lg->addCustomCommand($this->ctrl->getLinkTarget($this, 'disableForumNotification'), "forums_disable_forum_notification");
					}
					else
					{
						$lg->addCustomCommand($this->ctrl->getLinkTarget($this, 'enableForumNotification'), "forums_enable_forum_notification");
					}
				}
				else
				{
					if($frm_notificiation_enabled)
					{
						$lg->addCustomCommand($this->ctrl->getLinkTarget($this, 'disableForumNotification'), "forums_disable_forum_notification");
					}
					else
					{
						$lg->addCustomCommand($this->ctrl->getLinkTarget($this, 'enableForumNotification'), "forums_enable_forum_notification");
					}
				}

				$topic_notification_enabled = false;
				if($this->objCurrentTopic->getId())
				{
					$topic_notification_enabled = $this->objCurrentTopic->isNotificationEnabled($ilUser->getId());
					if($topic_notification_enabled)
					{
						$lg->addCustomCommand($this->ctrl->getLinkTarget($this, 'toggleThreadNotification'), "forums_disable_notification");
					}
					else
					{
						$lg->addCustomCommand($this->ctrl->getLinkTarget($this, 'toggleThreadNotification'), "forums_enable_notification");
					}
				}
				$this->ctrl->setParameter($this, 'thr_pk', '');
	
				if($frm_notificiation_enabled || $topic_notification_enabled)
				{
					$lg->addHeaderIcon(
						"not_icon",
						ilUtil::getImagePath("notification_on.svg"),
						$this->lng->txt("frm_notification_activated")
					);
				}
				else
				{
					$lg->addHeaderIcon(
						"not_icon",
						ilUtil::getImagePath("notification_off.svg"),
						$this->lng->txt("frm_notification_deactivated")
					);
				}
			}
		}

		return $lg;
	}
	
	public function isUserAllowedToDeactivateNotification()
	{
		if($this->objProperties->getNotificationType() == 'default')
		{
			return true;
		}
		
		if($this->objProperties->isUserToggleNoti() ==  0)
		{
			return true;
		}
		
		if($this->isParentObjectCrsOrGrp());
		{	
			global $ilUser;

			include_once 'Modules/Forum/classes/class.ilForumNotification.php';

			$frm_noti = new ilForumNotification((int) $_GET['ref_id']);
			$frm_noti->setUserId($ilUser->getId());
			
			$user_toggle = (int)$frm_noti->isUserToggleNotification();
			if($user_toggle == 0) 
			{	
				return true;
			}
		}
		
		return false;
	}
	
	private function isParentObjectCrsOrGrp()
	{
		global $tree;
		
		// check if there a parent-node is a grp or crs
		$grp_ref_id = $tree->checkForParentType($this->object->getRefId(), 'grp');
		$crs_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');
		
		if($grp_ref_id == 0 && $crs_ref_id == 0)
		{
			return false;
		}
		return true;
	}
	
	/**
	 * @return ilParticipants for course or group
	 */
	public function getParticipantsObject()
	{
		global $tree, $ilErr;

		$grp_ref_id = $tree->checkForParentType($this->object->getRefId(), 'grp');
		$crs_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');
		
		if($this->isParentObjectCrsOrGrp() == false)
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
		}

		/**
		 * @var $oParticipants ilParticipants
		 */
		$oParticipants = null;

		if($grp_ref_id > 0)
		{
			$parent_obj = ilObjectFactory::getInstanceByRefId($grp_ref_id);
			include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
			$oParticipants = ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
			return $oParticipants;
		}
		else if($crs_ref_id > 0)
		{
			$parent_obj = ilObjectFactory::getInstanceByRefId($crs_ref_id);

			include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
			$oParticipants = ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());
			return $oParticipants;
		}

		return $oParticipants;
	}

	/**
	 * @see ilDesktopItemHandling::addToDesk()
	 */
	public function addToDeskObject()
	{
		/** 
		 * @var $ilSetting ilSetting
		 * @var $lng ilLanguage
		 */
		global $ilSetting, $lng;

		if((int)$ilSetting->get('disable_my_offers'))
		{
			$this->showThreadsObject();
			return;
		}

		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::addToDesktop();
		ilUtil::sendSuccess($lng->txt("added_to_desktop"));
		$this->showThreadsObject();
	}

	/**
	 * @see ilDesktopItemHandling::removeFromDesk()
	 */
	public function removeFromDeskObject()
	{
		global $ilSetting, $lng;

		if((int)$ilSetting->get('disable_my_offers'))
		{
			$this->showThreadsObject();
			return;
		}

		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::removeFromDesktop();
		ilUtil::sendSuccess($lng->txt("removed_from_desktop"));
		$this->showThreadsObject();
	}

	public function saveThreadSortingObject()
	{
		$_POST['thread_sorting'] ? $thread_sorting = $_POST['thread_sorting'] :$thread_sorting =  array();

		foreach($thread_sorting as $thr_pk=>$sorting_value)
		{
			$sorting_value = str_replace(',','.',$sorting_value);
			$sorting_value =  (float)$sorting_value * 100;
			$this->object->setThreadSorting($thr_pk,$sorting_value);
		}
		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->showThreadsObject();
		return true;
	}

	/**
	 * 
	 */
	public function mergeThreadsObject()
	{
		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		$selected_thread_id = 0;
		if(isset($_GET['merge_thread_id']) && (int)$_GET['merge_thread_id'])
		{
			$selected_thread_id = (int)$_GET['merge_thread_id'];
		}
		else if(isset($_POST['thread_ids']) && count((array)$_POST['thread_ids']) == 1)
		{
			$selected_thread_id = (int)current($_POST['thread_ids']);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->showThreadsObject();
			return;
		}

		if($selected_thread_id)
		{
			$frm = $this->object->Forum;
			$frm->setForumId($this->object->getId());
			$frm->setForumRefId($this->object->getRefId());

			$selected_thread_obj = new ilForumTopic($selected_thread_id);

			if(ilForum::_lookupObjIdForForumId($selected_thread_obj->getForumId()) != $frm->getForumId())
			{
				ilUtil::sendFailure($this->lng->txt('not_allowed_to_merge_into_another_forum'));
				$this->showThreadsObject();
				return;
			}

			$frm->setMDB2Wherecondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));

			$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_liste.html', 'Modules/Forum');

			$topicData = $frm->getOneTopic();
			if($topicData)
			{
				include_once 'Modules/Forum/classes/class.ilForumTopicTableGUI.php';
				$this->ctrl->setParameter($this, 'merge_thread_id', $selected_thread_id);
				$tbl = new ilForumTopicTableGUI($this, 'mergeThreads', '', (int)$_GET['ref_id'], $topicData, $this->is_moderator, $this->forum_overview_setting);
				$tbl->setSelectedThread($selected_thread_obj);
				$tbl->setMapper($frm)->fetchData();
				$tbl->populate();
				$this->tpl->setVariable('THREADS_TABLE', $tbl->getHTML());
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('select_one'));
				$this->showThreadsObject();
				return;
			}
		}
	}

	/**
	 *
	 */
	public function confirmMergeThreadsObject()
	{
		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		if(!isset($_GET['merge_thread_id']) || !(int)$_GET['merge_thread_id'] || !is_array($_POST['thread_ids']) || count($_POST['thread_ids']) != 1)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->mergeThreadsObject();
			return;
		}

		$source_thread_id = (int)$_GET['merge_thread_id'];
		$target_thread_id = (int)current($_POST['thread_ids']);

		if($source_thread_id == $target_thread_id)
		{
			ilUtil::sendFailure($this->lng->txt('error_same_thread_ids'));
			$this->showThreadsObject();
			return;
		}

		if(ilForumTopic::lookupForumIdByTopicId($source_thread_id) != ilForumTopic::lookupForumIdByTopicId($target_thread_id))
		{
			ilUtil::sendFailure($this->lng->txt('not_allowed_to_merge_into_another_forum'));
			$this->ctrl->clearParameters($this);
			$this->showThreadsObject();
			return;
		}

		if(ilForumTopic::_lookupDate($source_thread_id) < ilForumTopic::_lookupDate($target_thread_id))
		{
			ilUtil::sendInfo($this->lng->txt('switch_threads_for_merge'));
		}	
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$c_gui = new ilConfirmationGUI();

		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performMergeThreads'));
		$c_gui->setHeaderText($this->lng->txt('frm_sure_merge_threads'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performMergeThreads');

		$c_gui->addItem('thread_ids[]', $source_thread_id, sprintf($this->lng->txt('frm_merge_src'), ilForumTopic::_lookupTitle($source_thread_id)));
		$c_gui->addItem('thread_ids[]', $target_thread_id, sprintf($this->lng->txt('frm_merge_target'), ilForumTopic::_lookupTitle($target_thread_id)));

		$this->tpl->setContent($c_gui->getHTML());
		return;
	}

	/**
	 * 
	 */
	public function performMergeThreadsObject()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		if(!$this->is_moderator)
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}

		if(!isset($_POST['thread_ids']) || !is_array($_POST['thread_ids']) || count($_POST['thread_ids']) != 2)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showThreadsObject();
			return;
		}

		if((int)$_POST['thread_ids'][0] == (int)$_POST['thread_ids'][1])
		{
			ilUtil::sendFailure($this->lng->txt('error_same_thread_ids'));
			$this->showThreadsObject();
			return;
		}

		try
		{
			ilForum::mergeThreads($this->object->id, (int)$_POST['thread_ids'][0], (int)$_POST['thread_ids'][1]);
			ilUtil::sendSuccess($this->lng->txt('merged_threads_successfully'));
		}
		catch(ilException $e)
		{
			return ilUtil::sendFailure($lng->txt($e->getMessage()));
		}
		$this->showThreadsObject();
	}

	/**
	 *
	 */
	public function cancelMergeThreads()
	{
		$this->showThreadsObject();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setSideBlocks()
	{
		$rgt_content = '';
		if(!$GLOBALS['ilCtrl']->isAsynch())
		{
			require_once 'Services/Search/classes/class.ilRepositoryObjectSearchGUI.php';
			$rgt_content = ilRepositoryObjectSearchGUI::getSearchBlockHTML($this->lng->txt('frm_search'));
		}
		$this->tpl->setRightContent($rgt_content . $this->getRightColumnHTML());
	}
}