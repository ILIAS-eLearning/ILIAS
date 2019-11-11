<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilObjForumGUI
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilObjForumGUI: ilPermissionGUI, ilForumExportGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjForumGUI: ilColumnGUI, ilPublicUserProfileGUI, ilForumModeratorsGUI, ilRepositoryObjectSearchGUI
 * @ilCtrl_Calls ilObjForumGUI: ilObjectCopyGUI, ilExportGUI, ilCommonActionDispatcherGUI, ilRatingGUI
 * @ilCtrl_Calls ilObjForumGUI: ilForumSettingsGUI, ilContainerNewsSettingsGUI
 *
 * @ingroup ModulesForum
 */
class ilObjForumGUI extends \ilObjectGUI implements \ilDesktopItemHandling
{
	/** @var string */
	public $modal_history = '';

	/** @var ilForumProperties */
	public $objProperties;

	/** @var ilForumTopic */
	private $objCurrentTopic;

	/** @var ilForumPost */
	private $objCurrentPost;
	
	/** @var int */
	private $display_confirm_post_activation = 0;

	/** @var bool */
	private $is_moderator = false;

	/** @var ilPropertyFormGUI */
	private $create_form_gui;

	/** @var ilPropertyFormGUI */
	private $replyEditForm;

	/** @var bool */
	private $hideToolbar = false;

	/** @var ilObjForum */
	public $object;

	/** @var \Psr\Http\Message\ServerRequestInterface */
	private $httpRequest;

	/** @var Factory */
	private $uiFactory;

	/** @var Renderer */
	private $uiRenderer;

	/** @var array|null */
	private $forumObjects = null;
	
	/** @var string */
	private $confirmation_gui_html = '';
	
	/** @var ilForumSettingsGUI */
	private $forum_settings_gui;

	/** @var \ilNavigationHistory */
	public $ilNavigationHistory;

	public $access;
	public $ilObjDataCache;
	public $tabs;
	public $error;
	public $user;
	public $settings;
	public $toolbar;
	public $repositoryTree;
	public $rbac; 
	public $locator;
	public $ilHelp;
	
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->ctrl->saveParameter($this, array('ref_id', 'cmdClass'));

		$this->tpl = $DIC->ui()->mainTemplate();
		$this->lng = $DIC->language();
		$this->httpRequest = $DIC->http()->request();
		$this->uiFactory = $DIC->ui()->factory();
		$this->uiRenderer = $DIC->ui()->renderer();

		$this->access = $DIC->access();
		$this->ilObjDataCache = $DIC['ilObjDataCache'];
		$this->tabs = $DIC->tabs();
		$this->error = $DIC['ilErr'];
		$this->ilNavigationHistory = $DIC['ilNavigationHistory'];
		$this->user = $DIC->user();
		$this->settings = $DIC->settings();
		$this->toolbar = $DIC->toolbar();
		$this->repositoryTree = $DIC->repositoryTree();
		$this->ilHelp = $DIC['ilHelp'];
		$this->rbac = $DIC->rbac();
		$this->locator = $DIC['ilLocator'];
		
		$this->type = 'frm';
		parent::__construct($a_data, $a_id, $a_call_by_reference, false);

		$this->lng->loadLanguageModule('forum');

		$this->initSessionStorage();

		$this->objProperties = \ilForumProperties::getInstance($this->ilObjDataCache->lookupObjId($_GET['ref_id']));

		// Stored due to performance issues
		$this->is_moderator = $this->access->checkAccess('moderate_frm', '', $_GET['ref_id']);

		// Model of current topic/thread
		$this->objCurrentTopic = new ilForumTopic((int) $_GET['thr_pk'], $this->is_moderator);

		// Model of current post
		$this->objCurrentPost = new ilForumPost((int) $_GET['pos_pk'], $this->is_moderator);
	}

	protected function initSessionStorage()
	{
		$forumValues = \ilSession::get('frm');
		if (!is_array($forumValues)) {
			$forumValues = [];
			\ilSession::set('frm', $forumValues);
		}

		$threadId = $this->httpRequest->getQueryParams()['thr_pk'] ?? 0;
		if ((int)$threadId > 0 && !is_array($forumValues[(int)$threadId])) {
			$forumValues[(int)$threadId] = [];
			\ilSession::set('frm', $forumValues);
		}
	}

	/**
	 * @param int $objId
	 * @param ilForumTopic $thread
	 */
	public function ensureThreadBelongsToForum(int $objId, \ilForumTopic $thread)
	{
		$forumId = \ilObjForum::lookupForumIdByObjId($objId);
		if ((int)$thread->getForumId() !== (int)$forumId) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}
	}

	/**
	 * @param \ilPropertyFormGUI $form
	 */
	private function decorateWithAutosave(\ilPropertyFormGUI $form)
	{
		if (\ilForumPostDraft::isAutoSavePostDraftAllowed()) {
			$interval = ilForumPostDraft::lookupAutosaveInterval();

			$this->tpl->addJavascript('./Modules/Forum/js/autosave.js');
			$autosave_cmd = 'autosaveDraftAsync';
			if ($this->objCurrentPost->getId() == 0 && $this->objCurrentPost->getThreadId() == 0) {
				$autosave_cmd = 'autosaveThreadDraftAsync';
			}
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
			$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
			$draft_id = (int)$_GET['draft_id'] > 0 ?  (int)$_GET['draft_id'] : 0;
			$this->ctrl->setParameter($this, 'draft_id',  $draft_id );
			$this->ctrl->setParameter($this, 'action', \ilUtil::stripSlashes($_GET['action']));
			$this->tpl->addOnLoadCode(
				"il.Language.setLangVar('saving', " . json_encode($this->lng->txt('saving')) . ");"
			);

			$this->tpl->addOnLoadCode('il.ForumDraftsAutosave.init(' . json_encode(array(
					'loading_img_src' => \ilUtil::getImagePath('loader.svg'),
					'draft_id' => $draft_id,
					'interval'        => $interval * 1000,
					'url'             => $this->ctrl->getFormAction($this, $autosave_cmd, '', true, false),
					'selectors'       => array(
						'form' => '#form_' . $form->getId()
					)
				)) . ');');
		}
	}

	/**
	 * @return bool
	 */
	private function isHierarchicalView(): bool 
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
	private function isTopLevelReplyCommand(): bool
	{
		return in_array(
			strtolower($this->ctrl->getCmd()),
			array_map('strtolower', array('createTopLevelPost', 'quoteTopLevelPost', 'saveTopLevelPost'))
		);
	}

	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$exclude_cmds = array(
			'viewThread', 'markPostUnread','markPostRead', 'showThreadNotification',
			'performPostActivation', 
			'askForPostActivation', 'askForPostDeactivation',
			'toggleThreadNotification', 'toggleThreadNotificationTab',
			'toggleStickiness', 'cancelPost', 'savePost', 'saveTopLevelPost', 'createTopLevelPost', 'quoteTopLevelPost', 'quotePost', 'getQuotationHTMLAsynch',
			'autosaveDraftAsync', 'autosaveThreadDraftAsync',
			'saveAsDraft', 'editDraft', 'updateDraft', 'deliverDraftZipFile', 'deliverZipFile', 'cancelDraft',
			'deleteThreadDrafts',
			'deletePosting', 'deletePostingDraft', 'revokeCensorship', 'addCensorship',
		);

		if (!in_array($cmd, $exclude_cmds)) {
			$this->prepareOutput();
		}

		if (!$this->getCreationMode() && !$this->ctrl->isAsynch() && $this->access->checkAccess('read', '', $_GET['ref_id'])) {
			$this->ilNavigationHistory->addItem(
				(int)$_GET['ref_id'], \ilLink::_getLink((int)$_GET['ref_id'], 'frm'), 'frm'
			);
		}

		switch ($next_class) {
			case 'ilforumsettingsgui':
				$forum_settings_gui = new ilForumSettingsGUI($this);
				$this->ctrl->forwardCommand($forum_settings_gui);
				break;

			case 'ilrepositoryobjectsearchgui':
				$this->addHeaderAction();
				$this->setSideBlocks();
				$this->tabs->activateTab("forums_threads");
				$this->ctrl->setReturn($this,'view');
				$search_gui = new ilRepositoryObjectSearchGUI(
					$this->object->getRefId(),
					$this,
					'view'
				);
				$this->ctrl->forwardCommand($search_gui);
				break;

			case 'ilpermissiongui':
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilforumexportgui':
				$fex_gui = new ilForumExportGUI();
				$this->ctrl->forwardCommand($fex_gui);
				exit();
				break;
			
			case 'ilforummoderatorsgui':
				$fm_gui = new ilForumModeratorsGUI();
				$this->ctrl->forwardCommand($fm_gui);
				break;
				
			case 'ilinfoscreengui':
				$this->infoScreen();
				break;
				
			case 'ilcolumngui':
				$this->showThreadsObject();
				break;

			case 'ilpublicuserprofilegui':				
				$profile_gui = new ilPublicUserProfileGUI((int)$_GET['user']);
				$add = $this->getUserProfileAdditional((int)$_GET['ref_id'], (int)$_GET['user']);
				$profile_gui->setAdditional($add);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				$this->tpl->setContent($ret);
				break;
				
			case 'ilobjectcopygui':
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('frm');
				$this->ctrl->forwardCommand($cp);
				break;

			case 'ilexportgui':
				$this->tabs->activateTab('export');
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;

			case "ilratinggui":
				if (!$this->objProperties->isIsThreadRatingEnabled() || $this->user->isAnonymous()) {
					$this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
				}

				if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}

				$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentTopic);

				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->object->getId(), $this->object->getType(), $this->objCurrentTopic->getId(), 'thread');

				$this->ctrl->setParameter($this, 'thr_pk', (int)$this->objCurrentTopic->getId());
				$this->ctrl->forwardCommand($rating_gui);

				$avg = ilRating::getOverallRatingForObject($this->object->getId(), $this->object->getType(), (int)$this->objCurrentTopic->getId(), 'thread');
				$this->objCurrentTopic->setAverageRating($avg['avg']);
				$this->objCurrentTopic->update();

				$this->ctrl->redirect($this, "showThreads");
				break;
			
			case 'ilcommonactiondispatchergui':
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case "ilcontainernewssettingsgui":
				$forum_settings_gui = new ilForumSettingsGUI($this);
				$forum_settings_gui->settingsTabs();

				$this->lng->loadLanguageModule('cont');
				$this->tabs_gui->activateSubTab('cont_news_settings');
				$news_set_gui = new ilContainerNewsSettingsGUI($this);
				$news_set_gui->setNewsBlockForced(true);
				$news_set_gui->setPublicNotification(true);
				$this->ctrl->forwardCommand($news_set_gui);
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
		if ($cmd != 'viewThreadObject' && $cmd != 'showUserObject') {
			$this->addHeaderAction();
		}
	}

	/**
	 * 
	 */
	public function infoScreenObject()
	{
		$this->ctrl->setCmd('showSummary');
		$this->ctrl->setCmdClass('ilinfoscreengui');
		$this->infoScreen();
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		$this->forum_settings_gui = new ilForumSettingsGUI($this);
		$this->forum_settings_gui->getCustomForm($a_form);
	}
	
	/**
	 * @param array $a_values
	 */
	protected function getEditFormCustomValues(Array &$a_values)
	{
		$this->forum_settings_gui->getCustomValues($a_values); 
	}
	
	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->forum_settings_gui->updateCustomValues($a_form);
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
	 * @param int $threadId
	 * @param ilPropertyFormGUI $form
	 */
	public function editThreadObject($threadId, ilPropertyFormGUI $form = null)
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$thread = new \ilForumTopic($threadId);
		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $thread);

		$this->tabs->activateTab('forums_threads');

		if(!($form instanceof \ilPropertyFormGUI)) {
			$form = $this->getThreadEditingForm($threadId);
			$form->setValuesByArray(array(
				'title' => $thread->getSubject()
			));
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * 
	 */
	public function updateThreadObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->objCurrentTopic->getId()) {
			$this->showThreadsObject();
			return;
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentTopic);

		$form = $this->getThreadEditingForm($this->objCurrentTopic->getId());
		if (!$form->checkInput()) {
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
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->object->markAllThreadsRead($this->user->getId());
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
		$this->getCenterColumnHTML();
	}

	public function getSubTabs($subtab = 'showThreads')
	{
		if($this->objProperties->getThreadSorting() == 1 && $this->is_moderator)
		{
			$this->tabs->addSubTabTarget('show', $this->ctrl->getLinkTarget($this, 'showThreads'), 'showThreads', get_class($this), '', $subtab=='showThreads'? true : false );
			$this->tabs->addSubTabTarget('sorting_header', $this->ctrl->getLinkTarget($this, 'sortThreads'), 'sortThreads', get_class($this), '', $subtab=='sortThreads'? true : false );
		}
	}
	
	public function getContent()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
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
		if($this->access->checkAccess('add_thread', '', $this->object->getRefId()) && !$this->hideToolbar())
		{
			$btn = ilLinkButton::getInstance();
			$btn->setUrl($this->ctrl->getLinkTarget($this, 'createThread'));
			$btn->setCaption('forums_new_thread');
			$this->toolbar->addStickyItem($btn);
		}

		// Mark all topics as read button
		if($this->user->getId() != ANONYMOUS_USER_ID && !(int)strlen($this->confirmation_gui_html))
		{
			$this->toolbar->addButton(
				$this->lng->txt('forums_mark_read'),
				$this->ctrl->getLinkTarget($this, 'markAllRead'),
				'',
				ilAccessKey::MARK_ALL_READ
			);
			$this->ctrl->clearParameters($this);
		}

		if (\ilForumPostDraft::isSavePostDraftAllowed()) {
			$drafts = \ilForumPostDraft::getThreadDraftData(
				$this->user->getId(), ilObjForum::lookupForumIdByObjId($this->object->getId())
			);
			if (count($drafts) > 0) {
				$draftsTable = new ilForumDraftsTableGUI(
					$this, $cmd, $this->access->checkAccess('add_thread', '', $this->object->getRefId())
				);
				$draftsTable->setData($drafts);
				$this->tpl->setVariable('THREADS_DRAFTS_TABLE', $draftsTable->getHTML());
			}
		}

		// Import information: Topic (variable $topicData) means frm object, not thread
		$topicData = $frm->getOneTopic();
		if($topicData)
		{
			// Visit-Counter
			$frm->setDbTable('frm_data');
			$frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topicData['top_pk']));
			$frm->updateVisits($topicData['top_pk']);

			if(!in_array($cmd, array('showThreads', 'sortThreads') ))
			{
				$cmd = 'showThreads';
			}

			$tbl = new ilForumTopicTableGUI(
				$this, $cmd, '', (int) $_GET['ref_id'],
				$topicData, $this->is_moderator, $this->settings->get('forum_overview')
			);
			$tbl->init();
			$tbl->setMapper($frm)->fetchData();
			$this->tpl->setVariable('THREADS_TABLE', $tbl->getHTML());
		}

		// Permanent link
		$permalink = new ilPermanentLinkGUI('frm', $this->object->getRefId());
		$this->tpl->setVariable('PRMLINK', $permalink->getHTML());
	}

	/**
	 * @param string $action
	 * @param      $render_drafts
	 * @param      $node
	 * @param null $edit_draft_id
	 * @return bool
	 * @throws ilSplitButtonException
	 */
	protected function renderDraftContent(string $action, $render_drafts, $node, $edit_draft_id = NULL)
	{
		$frm = $this->object->Forum;
		
		$draftsObjects = ilForumPostDraft::getInstancesByUserIdAndThreadId($this->user->getId(), $this->objCurrentTopic->getId());
		$drafts         = $draftsObjects[$node->getId()];
		
		if($render_drafts && is_array($drafts))
		{
			foreach($drafts as $draft)
			{
				if(!$draft instanceof ilForumPostDraft)
				{
					continue 1;
				}
				
				if(isset($edit_draft_id) && $edit_draft_id == $node->getId())
				{
					// do not render a draft that is in 'edit'-mode
					return false;
				}
				
				$tmp_file_obj = new ilFileDataForumDrafts($this->object->getId(), $draft->getDraftId());
				$filesOfDraft = $tmp_file_obj->getFilesOfPost();
				ksort($filesOfDraft);

				if (count($filesOfDraft)) {
					if ($action !== 'showdraft') {
						foreach ($filesOfDraft as $file) {
							$this->tpl->setCurrentBlock('attachment_download_row');
							$this->ctrl->setParameter($this, 'draft_id', $tmp_file_obj->getDraftId());
							$this->ctrl->setParameter($this, 'file', $file['md5']);
							$this->tpl->setVariable('HREF_DOWNLOAD', $this->ctrl->getLinkTarget($this, 'viewThread'));
							$this->tpl->setVariable('TXT_FILENAME', $file['name']);
							$this->ctrl->setParameter($this, 'file', '');
							$this->ctrl->setParameter($this, 'draft_id', '');
							$this->ctrl->clearParameters($this);
							$this->tpl->parseCurrentBlock();
						}

						$this->tpl->setCurrentBlock('attachments');
						$this->tpl->setVariable('TXT_ATTACHMENTS_DOWNLOAD', $this->lng->txt('forums_attachments'));
						$this->tpl->setVariable('DOWNLOAD_IMG', ilGlyphGUI::get(ilGlyphGUI::ATTACHMENT, $this->lng->txt('forums_download_attachment')));
						if(count($filesOfDraft) > 1)
						{
							$download_zip_button = ilLinkButton::getInstance();
							$download_zip_button->setCaption($this->lng->txt('download'), false);
							$this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
							$download_zip_button->setUrl($this->ctrl->getLinkTarget($this, 'deliverDraftZipFile'));
							$this->ctrl->setParameter($this, 'draft_id', '');
							$this->tpl->setVariable('DOWNLOAD_ZIP', $download_zip_button->render());
						}
						$this->tpl->parseCurrentBlock();
					}
				}

				// render splitButton for drafts
				$this->renderSplitButton($action, false, $node, (int)$_GET['offset'], $draft);

				// highlight drafts
				$rowCol = 'tblrowmarked';
				// set row color
				$this->tpl->setVariable('ROWCOL', ' ' . $rowCol);
				
				// Author
				$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
				$this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
				
				$backurl = urlencode($this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
				
				$this->ctrl->setParameter($this, 'backurl', $backurl);
				$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
				$this->ctrl->setParameter($this, 'user', $draft->getPostDisplayUserId());
				
				$authorinfo = new ilForumAuthorInformation(
					$draft->getPostAuthorId(),
					$draft->getPostDisplayUserId(),
					$draft->getPostUserAlias(),
					'',
					array(
						'href' => $this->ctrl->getLinkTarget($this, 'showUser')
					)
				);
				
				$this->ctrl->clearParameters($this);
				
				if($authorinfo->hasSuffix())
				{
					$this->tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
					$this->tpl->setVariable('USR_NAME', $draft->getPostUserAlias());
				}
				else
				{
					$this->tpl->setVariable('AUTHOR', $authorinfo->getLinkedAuthorShortName());
					if($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized())
					{
						$this->tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
					}
				}
				$this->tpl->setVariable('DRAFT_ANCHOR', 'draft_' . $draft->getDraftId());
				
				$this->tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
				if($authorinfo->getAuthor()->getId() && ilForum::_isModerator((int)$_GET['ref_id'], $draft->getPostAuthorId()))
				{
					if($authorinfo->getAuthor()->getGender() == 'f')
					{
						$this->tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
					}
					else if($authorinfo->getAuthor()->getGender() == 'm')
					{
						$this->tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
					}
					else if($authorinfo->getAuthor()->getGender() == 'n')
					{
						$this->tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_n'));
					}
				}
				
				// get create- and update-dates
				if($draft->getUpdateUserId() > 0)
				{
					$spanClass = 'small';
					
					if(ilForum::_isModerator($this->ref_id, $node->getUpdateUserId()))
					{
						$spanClass = 'moderator_small';
					}	
					
					$draft->setPostUpdate($draft->getPostUpdate());
					
					$this->ctrl->setParameter($this, 'backurl', $backurl);
					$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
					$this->ctrl->setParameter($this, 'user', $node->getUpdateUserId());
					$this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());

					$authorinfo = new ilForumAuthorInformation(
						$draft->getPostAuthorId(),
						$draft->getUpdateUserId(),
						$draft->getPostUserAlias(),
						'',
						array(
							'href' => $this->ctrl->getLinkTarget($this, 'showUser')
						)
					);
					
					$this->ctrl->clearParameters($this);
					
					$this->tpl->setVariable('POST_UPDATE_TXT', $this->lng->txt('edited_on') . ': ' . $frm->convertDate($draft->getPostUpdate()) . ' - ' . strtolower($this->lng->txt('by')));
					$this->tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
					if($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized() && !$authorinfo->hasSuffix())
					{
						$this->tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
					}
				}
				// Author end
				
				// prepare post
				$draft->setPostMessage($frm->prepareText($draft->getPostMessage()));
				
				$this->tpl->setVariable('SUBJECT', $draft->getPostSubject());
				$this->tpl->setVariable('POST_DATE', $frm->convertDate($draft->getPostDate()));
				
				if(!$node->isCensored() || ($this->objCurrentPost->getId() == $node->getId() && $action === 'censor')) {
					$spanClass = "";
					
					if(ilForum::_isModerator($this->ref_id, $draft->getPostDisplayUserId()))
					{
						$spanClass = 'moderator';
					}
					
					if($draft->getPostMessage() == strip_tags($draft->getPostMessage()))
					{
						// We can be sure, that there are not html tags
						$draft->setPostMessage(nl2br($draft->getPostMessage()));
					}
					
					if($spanClass != "")
					{
						$this->tpl->setVariable('POST', "<span class=\"" . $spanClass . "\">" . ilRTE::_replaceMediaObjectImageSrc($draft->getPostMessage(), 1) . "</span>");
					}
					else
					{
						$this->tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($draft->getPostMessage(), 1));
					}
				}

				if (!$this->objCurrentTopic->isClosed() && $action === 'deletedraft') {
					if ($this->user->getId() != ANONYMOUS_USER_ID && $draft->getDraftId() == (int)$_GET['draft_id']) {
						// confirmation: delete
						$this->tpl->setVariable('FORM', $this->getDeleteDraftFormHTML());
					}
				} elseif ($action === 'editdraft' && (int)$draft->getDraftId() == (int)$_GET['draft_id']) {
					$oEditReplyForm = $this->getReplyEditForm();
					$this->tpl->setVariable('EDIT_DRAFT_ANCHOR', 'draft_edit_' . $draft->getDraftId());
					$this->tpl->setVariable('DRAFT_FORM', $oEditReplyForm->getHTML() . $this->modal_history);
				}

				$this->tpl->parseCurrentBlock();
			}
			return true;
		}
		return true;
	}

	/**
	 * @param ilForumPost $node
	 * @param string $action
	 * @param $Start
	 * @param $z
	 * @return bool
	 * @throws ilSplitButtonException
	 */
	protected function renderPostContent(ilForumPost $node, string $action, $Start, $z)
	{
		$forumObj = $this->object;
		$frm = $this->object->Forum;
		
		// download post attachments
		$tmp_file_obj = new ilFileDataForum($forumObj->getId(), $node->getId());
		
		$filesOfPost = $tmp_file_obj->getFilesOfPost();
		ksort($filesOfPost);
		if (count($filesOfPost)) {
			if ($node->getId() != $this->objCurrentPost->getId() || $action !== 'showedit') {
				foreach ($filesOfPost as $file) {
					$this->tpl->setCurrentBlock('attachment_download_row');
					$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
					$this->ctrl->setParameter($this, 'file', $file['md5']);
					$this->tpl->setVariable('HREF_DOWNLOAD', $this->ctrl->getLinkTarget($this, 'viewThread'));
					$this->tpl->setVariable('TXT_FILENAME', $file['name']);
					$this->ctrl->clearParameters($this);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock('attachments');
				$this->tpl->setVariable('TXT_ATTACHMENTS_DOWNLOAD', $this->lng->txt('forums_attachments'));
				$this->tpl->setVariable('DOWNLOAD_IMG', ilGlyphGUI::get(ilGlyphGUI::ATTACHMENT, $this->lng->txt('forums_download_attachment')));
				if(count($filesOfPost) > 1)
				{
					$download_zip_button = ilLinkButton::getInstance();
					$download_zip_button->setCaption($this->lng->txt('download'), false);
					$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
					$download_zip_button->setUrl($this->ctrl->getLinkTarget($this, 'deliverZipFile'));
					
					$this->tpl->setVariable('DOWNLOAD_ZIP', $download_zip_button->render());
				}
				
				$this->tpl->parseCurrentBlock();
			}
		}
		// render splitbutton for posts
		$this->renderSplitButton($action, true, $node, $Start);
		
		// anker for every post					
		$this->tpl->setVariable('POST_ANKER', $node->getId());
		
		//permanent link for every post																
		$this->tpl->setVariable('TXT_PERMA_LINK', $this->lng->txt('perma_link'));
		$this->tpl->setVariable('PERMA_TARGET', '_top');
		
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

		if (
			(!in_array($action, ['delete', 'censor']) && !$this->displayConfirmPostActivation()) ||
			$this->objCurrentPost->getId() != $node->getId()
		) {
			$this->tpl->setVariable('ROWCOL', ' ' . $rowCol);
		}
		else
		{
			// highlight censored posts
			$rowCol = 'tblrowmarked';
		}
		
		// post is censored
		if($node->isCensored())
		{
			// display censorship advice
			if ($action !== 'censor') {
				$this->tpl->setVariable('TXT_CENSORSHIP_ADVICE', $this->lng->txt('post_censored_comment_by_moderator'));
			}
			
			// highlight censored posts
			$rowCol = 'tblrowmarked';
		}
		
		// set row color
		$this->tpl->setVariable('ROWCOL', ' ' . $rowCol);
		// if post is not activated display message for the owner
		if(!$node->isActivated() && $node->isOwner($this->user->getId()))
		{
			$this->tpl->setVariable('POST_NOT_ACTIVATED_YET', $this->lng->txt('frm_post_not_activated_yet'));
		}
		
		// Author
		$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
		$backurl = urlencode($this->ctrl->getLinkTarget($this, 'viewThread', $node->getId()));
		$this->ctrl->clearParameters($this);
		
		$this->ctrl->setParameter($this, 'backurl', $backurl);
		$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
		$this->ctrl->setParameter($this, 'user', $node->getDisplayUserId());
		
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
			$this->tpl->setVariable('AUTHOR', $authorinfo->getSuffix());
			$this->tpl->setVariable('USR_NAME', $node->getUserAlias());
		}
		else
		{
			$this->tpl->setVariable('AUTHOR', $authorinfo->getLinkedAuthorShortName());
			if($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized())
			{
				$this->tpl->setVariable('USR_NAME', $authorinfo->getAuthorName(true));
			}
		}
		
		$this->tpl->setVariable('USR_IMAGE', $authorinfo->getProfilePicture());
		if($authorinfo->getAuthor()->getId() && ilForum::_isModerator((int)$_GET['ref_id'], $node->getPosAuthorId()))
		{
			if($authorinfo->getAuthor()->getGender() == 'f')
			{
				$this->tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_f'));
			}
			else if($authorinfo->getAuthor()->getGender() == 'm')
			{
				$this->tpl->setVariable('ROLE', $this->lng->txt('frm_moderator_m'));
			}
		}
		
		// get create- and update-dates
		if($node->getUpdateUserId() > 0)
		{
			$spanClass = 'small';
			
			if(ilForum::_isModerator($this->ref_id, $node->getUpdateUserId()))
			{
				$spanClass = 'moderator_small';
			}
			
			$node->setChangeDate($node->getChangeDate());
			
			$this->ctrl->setParameter($this, 'backurl', $backurl);
			$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
			$this->ctrl->setParameter($this, 'user', $node->getUpdateUserId());
			
			$update_user_id = $node->getUpdateUserId();
			if($node->getPosAuthorId() == $node->getUpdateUserId()
			&& $node->getDisplayUserId() == 0)
			{
				$update_user_id = $node->getDisplayUserId();
			}

			$authorinfo = new ilForumAuthorInformation(
				$node->getPosAuthorId(),
				$update_user_id,
				$node->getUserAlias(),
				$node->getImportName(),
				array(
					'href' => $this->ctrl->getLinkTarget($this, 'showUser')
				)
			);
			
			$this->ctrl->clearParameters($this);
			
			$this->tpl->setVariable('POST_UPDATE_TXT', $this->lng->txt('edited_on') . ': ' . $frm->convertDate($node->getChangeDate()) . ' - ' . strtolower($this->lng->txt('by')));
			$this->tpl->setVariable('UPDATE_AUTHOR', $authorinfo->getLinkedAuthorShortName());
			if($authorinfo->getAuthorName(true) && !$this->objProperties->isAnonymized() && !$authorinfo->hasSuffix())
			{
				$this->tpl->setVariable('UPDATE_USR_NAME', $authorinfo->getAuthorName(true));
			}
			
		} // if ($node->getUpdateUserId() > 0)*/
		// Author end
		
		// prepare post
		$node->setMessage($frm->prepareText($node->getMessage()));
		
		if($this->user->getId() == ANONYMOUS_USER_ID ||
			$node->isPostRead()
		)
		{
			$this->tpl->setVariable('SUBJECT', $node->getSubject());
		}
		else
		{
			$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
			$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
			$this->ctrl->setParameter($this, 'offset', $Start);
			$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
			$this->ctrl->setParameter($this, 'viewmode', $_SESSION['viewmode']);
			$mark_post_target = $this->ctrl->getLinkTarget($this, 'markPostRead', $node->getId());
			
			$this->tpl->setVariable('SUBJECT', "<a href=\"" . $mark_post_target . "\"><b>" . $node->getSubject() . "</b></a>");
		}
		
		$this->tpl->setVariable('POST_DATE', $frm->convertDate($node->getCreateDate()));
		
		if(!$node->isCensored() ||
			($this->objCurrentPost->getId() == $node->getId() && $action === 'censor')
		)
		{
			$spanClass = "";
			if(ilForum::_isModerator($this->ref_id, $node->getDisplayUserId()))
			{
				$spanClass = 'moderator';
			}
			// possible bugfix for mantis #8223
			if($node->getMessage() == strip_tags($node->getMessage()))
			{
				// We can be sure, that there are not html tags
				$node->setMessage(nl2br($node->getMessage()));
			}
			
			if($spanClass != "")
			{
				$this->tpl->setVariable('POST', "<span class=\"" . $spanClass . "\">" . ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1) . "</span>");
			}
			else
			{
				$this->tpl->setVariable('POST', ilRTE::_replaceMediaObjectImageSrc($node->getMessage(), 1));
			}
		}
		else
		{
			$this->tpl->setVariable('POST', "<span class=\"moderator\">" . nl2br($node->getCensorshipComment()) . "</span>");
		}
		
		$this->tpl->parseCurrentBlock();
		return true;
	}

	/**
	 * @param ilObject|ilObjForum $a_new_object
	 */
	protected function afterSave(ilObject $a_new_object)
	{
		\ilUtil::sendSuccess($this->lng->txt('frm_added'), true);
		$this->ctrl->setParameter($this, 'ref_id', $a_new_object->getRefId());
		$this->ctrl->redirect($this, 'createThread');
	}

	protected function getTabs()
	{
		$this->ilHelp->setScreenIdComponent("frm");

		$this->ctrl->setParameter($this, 'ref_id', $this->ref_id);

		$active = array(
			'', 'showThreads', 'view', 'markAllRead', 
			'enableForumNotification', 'disableForumNotification', 'moveThreads', 'performMoveThreads',
			'cancelMoveThreads', 'performThreadsAction', 'createThread', 'addThread',
			'showUser', 'confirmDeleteThreads',
			'merge','mergeThreads', 'performMergeThreads'
		);

		(in_array($this->ctrl->getCmd(), $active)) ? $force_active = true : $force_active = false;
		$this->tabs->addTarget('forums_threads', $this->ctrl->getLinkTarget($this,'showThreads'), $this->ctrl->getCmd(), get_class($this), '', $force_active);

		// info tab
		if($this->access->checkAccess('visible', '', $this->ref_id) || $this->access->checkAccess('read', '', $this->ref_id))
		{
			$force_active = ($this->ctrl->getNextClass() == 'ilinfoscreengui' || strtolower($_GET['cmdClass']) == 'ilnotegui') ? true : false;
			$this->tabs->addTarget('info_short',
				 $this->ctrl->getLinkTargetByClass(array('ilobjforumgui', 'ilinfoscreengui'), 'showSummary'),
				 array('showSummary', 'infoScreen'),
				 '', '', $force_active);
		}
		
		if($this->access->checkAccess('write', '', $this->ref_id))
		{
			$force_active = ($this->ctrl->getCmd() == 'edit') ? true	: false;
			$this->tabs->addTarget('settings', $this->ctrl->getLinkTarget($this, 'edit'), 'edit', get_class($this), '', $force_active);
		}
		
		if($this->access->checkAccess('write', '', $this->ref_id))
		{
			$this->tabs->addTarget('frm_moderators', $this->ctrl->getLinkTargetByClass('ilForumModeratorsGUI', 'showModerators'), 'showModerators', get_class($this));			
		}

		if($this->settings->get('enable_fora_statistics', false) &&
		   ($this->objProperties->isStatisticEnabled() || $this->access->checkAccess('write', '', $this->ref_id))) 
		{
			$force_active = ($this->ctrl->getCmd() == 'showStatistics') ? true	: false;
			$this->tabs->addTarget('frm_statistics', $this->ctrl->getLinkTarget($this, 'showStatistics'), 'showStatistics', get_class($this), '', $force_active); //false
		}

		if($this->access->checkAccess('write', '', $this->object->getRefId()))
		{
			$this->tabs->addTarget('export', $this->ctrl->getLinkTargetByClass('ilexportgui', ''), '', 'ilexportgui');
		}

		if($this->access->checkAccess('edit_permission', '', $this->ref_id))
		{
			$this->tabs->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');							
		}
	}
	
	public function showStatisticsObject() 
	{
		/// if globally deactivated, skip!!! intrusion detected
		if (!$this->settings->get('enable_fora_statistics', false)) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		// if no read access -> intrusion detected
		if (!$this->access->checkAccess('read', '', (int)$_GET['ref_id'])) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		// if read access and statistics disabled -> intrusion detected
		if (!$this->objProperties->isStatisticEnabled()) {
			// if write access and statistics disabled -> ok, for forum admin
			if ($this->access->checkAccess('write', '', (int)$_GET['ref_id'])) {
				ilUtil::sendInfo($this->lng->txt('frm_statistics_disabled_for_participants'));
			} else {
				$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
			}
		}

		$this->object->Forum->setForumId($this->object->getId());

		$tbl = new ilForumStatisticsTableGUI($this, 'showStatistics');
		$tbl->setId('il_frm_statistic_table_'.(int) (int)$_GET['ref_id']);
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
		global $DIC;
		
		$ilAccess = $DIC->access();
		$lng = $DIC->language();
		$ilErr = $DIC['ilErr'];
		
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
			include_once('ilias.php');
			exit();
		}

		$ilErr->raiseError($lng->txt('msg_no_perm_read'), $ilErr->FATAL);
	}
	
	public function performDeleteThreadsObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!isset($_POST['thread_ids']) || !is_array($_POST['thread_ids'])) {
	 		ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
	 		return $this->showThreadsObject();
	 	}

		$forumObj = new ilObjForum($this->object->getRefId());
		$this->objProperties->setObjId($forumObj->getId());

		$frm = new ilForum();

		$success_message = "forums_thread_deleted";
		if (count($_POST['thread_ids']) > 1) {
			$success_message = "forums_threads_deleted";
		}

		$threadIds = [];
		if (isset($_POST['thread_ids']) && is_array($_POST['thread_ids'] )) {
			$threadIds = $_POST['thread_ids'];
		}

		$threads = [];
		array_walk($threadIds, function($threadId) use (&$threads) {
			$thread = new \ilForumTopic($threadId);
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $thread);

			$threads[] = $thread;
		});

		foreach ($threads as $thread) {
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());

			$first_node = $frm->getFirstPostNode($thread->getId());
			if ((int)$first_node['pos_pk']) {
				$frm->deletePost($first_node['pos_pk']);
				ilUtil::sendInfo($this->lng->txt($success_message), true);
			}
		}
		$this->ctrl->redirect($this, 'showThreads');
	}
	
	public function confirmDeleteThreads()
	{
		if (!isset($_POST['thread_ids']) || !is_array($_POST['thread_ids'])) {
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
			return $this->showThreadsObject();
		}

		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threads = [];
		array_walk($_POST['thread_ids'], function($threadId) use (&$threads) {
			$thread = new \ilForumTopic($threadId);
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $thread);

			$threads[] = $thread;
		});

		$c_gui = new ilConfirmationGUI();
		
		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteThreads'));
		$c_gui->setHeaderText($this->lng->txt('frm_sure_delete_threads'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteThreads');

		foreach ($threads as $thread) {
			$c_gui->addItem('thread_ids[]', $thread->getId(), $thread->getSubject());
		}

		$this->confirmation_gui_html = $c_gui->getHTML();
		
		$this->hideToolbar(true);

		return $this->tpl->setContent($c_gui->getHTML());
	}

	protected function confirmDeleteThreadDraftsObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$draftIds = array_filter((array)($this->httpRequest->getParsedBody()['draft_ids'] ?? []));
		if (0 === count($draftIds)) {
			\ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'));
			$this->showThreadsObject();
			return;
		}

		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteThreadDrafts'));
		$confirmation->setHeaderText($this->lng->txt('sure_delete_drafts'));
		$confirmation->setCancel($this->lng->txt('cancel'), 'showThreads');
		$confirmation->setConfirm($this->lng->txt('confirm'), 'deleteThreadDrafts');
		$instances = \ilForumPostDraft::getDraftInstancesByUserId($this->user->getId());
		foreach ($draftIds as $draftId) {
			if (array_key_exists($draftId, $instances)) {
				$confirmation->addItem('draft_ids[]', $draftId, $instances[$draftId]->getPostSubject());
			}
		}

		$this->tpl->setContent($confirmation->getHTML());
	}

	public function prepareThreadScreen(ilObjForum $a_forum_obj)
	{
		$this->ilHelp->setScreenIdComponent("frm");

		$this->tpl->getStandardTemplate();
		ilUtil::sendInfo();
		ilUtil::infoPanel();

		$this->tpl->setTitleIcon(ilObject::_getIcon("", "big", "frm"));

 		$this->tabs->setBackTarget($this->lng->txt('all_topics'),'ilias.php?baseClass=ilRepositoryGUI&amp;ref_id='.$_GET['ref_id']);

		// by answer view
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'viewmode', ilForumProperties::VIEW_TREE);
		$this->tabs->addTarget('sort_by_posts', $this->ctrl->getLinkTarget($this, 'viewThread'));
	
		// by date view
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'viewmode', ilForumProperties::VIEW_DATE);
		$this->tabs->addTarget('order_by_date',	$this->ctrl->getLinkTarget($this, 'viewThread'));

		$this->ctrl->clearParameters($this);

		if($this->isHierarchicalView())
		{
			$this->tabs->activateTab('sort_by_posts');
		}
		else
		{
			$this->tabs->activateTab('order_by_date');
		}

		/**
		 * @var $frm ilForum
		 */
		$frm = $a_forum_obj->Forum;
		$frm->setForumId($a_forum_obj->getId());
	}
	
	public function performPostActivationObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

		$this->objCurrentPost->activatePost();
		$GLOBALS['ilAppEventHandler']->raise(
			'Modules/Forum',
			'activatedPost',
			array(
				'ref_id'            => $this->object->getRefId(),
				'post'              => $this->objCurrentPost
			)
		);
		ilUtil::sendInfo($this->lng->txt('forums_post_was_activated'), true);

		$this->viewThreadObject();
	}

	private function deletePostingObject()
	{
		if (
			!$this->objCurrentTopic->isClosed() && (
				$this->is_moderator ||
				($this->objCurrentPost->isOwner($this->user->getId()) && !$this->objCurrentPost->hasReplies())
			) &&
			!$this->user->isAnonymous()
		) {
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

			$oForumObjects = $this->getForumObjects();
			/** @var $forumObj ilObjForum */
			$forumObj = $oForumObjects['forumObj'];

			$frm = new ilForum();
			$frm->setForumId($forumObj->getId());
			$frm->setForumRefId($forumObj->getRefId());
			$dead_thr = $frm->deletePost($this->objCurrentPost->getId());

			// if complete thread was deleted ...
			if ($dead_thr == $this->objCurrentTopic->getId()) {
				$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($forumObj->getId()));
				$topicData = $frm->getOneTopic();
				ilUtil::sendInfo($this->lng->txt('forums_post_deleted'), true);
				if ($topicData['top_num_threads'] > 0) {
					$this->ctrl->redirect($this, 'showThreads');
				} else {
					$this->ctrl->redirect($this, 'createThread');
				}
			}
			ilUtil::sendInfo($this->lng->txt('forums_post_deleted'), true);
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			$this->ctrl->redirect($this, 'viewThread');
		}

		$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
	}

	private function deletePostingDraftObject()
	{
		$this->deleteSelectedDraft();
	}

	private function revokeCensorshipObject()
	{
		$this->handleCensorship(true);
	}
	
	private function addCensorshipObject()
	{
		$this->handleCensorship();
	}

	private function handleCensorship($wasRevoked = false)
	{
		if (!$this->objCurrentTopic->isClosed() && $this->is_moderator) {
			$message = $this->handleFormInput($_POST['formData']['cens_message']);
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

			$oForumObjects = $this->getForumObjects();
			/** @var $frm ilForum */
			$frm = $oForumObjects['frm'];

			if ($wasRevoked) {
				$frm->postCensorship($message, $this->objCurrentPost->getId());
				ilUtil::sendSuccess($this->lng->txt('frm_censorship_revoked'));
			} else {
				$frm->postCensorship($message, $this->objCurrentPost->getId(), 1);
				ilUtil::sendSuccess($this->lng->txt('frm_censorship_applied'));
			}

			$this->viewThreadObject();
			return;
		}

		$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
	}

	public function askForPostActivationObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->setDisplayConfirmPostActivation(true);

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

	protected function toggleThreadNotificationObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentTopic);

		if ($this->objCurrentTopic->isNotificationEnabled($this->user->getId())) {
			$this->objCurrentTopic->disableNotification($this->user->getId());
			\ilUtil::sendInfo($this->lng->txt('forums_notification_disabled'));
		} else {
			$this->objCurrentTopic->enableNotification($this->user->getId());
			\ilUtil::sendInfo($this->lng->txt('forums_notification_enabled'));
		}

		$this->viewThreadObject();
	}

	protected function toggleStickinessObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentTopic);

		if ($this->objCurrentTopic->isSticky()) {
			$this->objCurrentTopic->unmakeSticky();
		} else {
			$this->objCurrentTopic->makeSticky();
		}

		$this->viewThreadObject();
	}

	public function cancelPostObject()
	{
		$_GET['action'] = '';
		if(isset($_POST['draft_id']) && (int)$_POST['draft_id'] > 0)
		{
			$draft = ilForumPostDraft::newInstanceByDraftId((int)$_POST['draft_id']);
			$draft->deleteDraftsByDraftIds(array( (int)$_POST['draft_id']));
		}
		
		$this->viewThreadObject();
	}
	
	public function cancelDraftObject()
	{
		$_GET['action'] = '';
		if(isset($_GET['draft_id']) && (int)$_GET['draft_id'] > 0)
		{
			if(ilForumPostDraft::isAutoSavePostDraftAllowed())
			{
				$history_obj = new ilForumDraftsHistory();
				$history_obj->getFirstAutosaveByDraftId((int)$_GET['draft_id']);
				$draft = ilForumPostDraft::newInstanceByDraftId((int)$_GET['draft_id']);
				$draft->setPostSubject($history_obj->getPostSubject());
				$draft->setPostMessage($history_obj->getPostMessage());
				
				ilForumUtil::moveMediaObjects($history_obj->getPostMessage(), 
					ilForumDraftsHistory::MEDIAOBJECT_TYPE, $history_obj->getHistoryId(), 
					ilForumPostDraft::MEDIAOBJECT_TYPE, $draft->getDraftId());
			
				$draft->updateDraft();
				
				$history_obj->deleteHistoryByDraftIds(array($draft->getDraftId()));
			}
		}
		$this->ctrl->clearParameters($this);
		$this->viewThreadObject();
	}

	public function getDeleteFormHTML()
	{
		/** @var $form_tpl ilTemplate */
		$form_tpl = new ilTemplate('tpl.frm_delete_post_form.html', true, true, 'Modules/Forum');
		$form_tpl->setVariable('ANKER', $this->objCurrentPost->getId());
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('TXT_DELETE', $this->lng->txt('forums_info_delete_post'));
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', ilUtil::stripSlashes($_GET['orderby']));
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('CMD_CONFIRM', 'deletePosting');
		$form_tpl->setVariable('CMD_CANCEL', 'viewThread');
		$form_tpl->setVariable('CANCEL_BUTTON', $this->lng->txt('cancel'));
		$form_tpl->setVariable('CONFIRM_BUTTON', $this->lng->txt('confirm'));

		return $form_tpl->get(); 
	}
	public function getDeleteDraftFormHTML()
	{
		/** @var $form_tpl ilTemplate */
		$form_tpl = new ilTemplate('tpl.frm_delete_post_form.html', true, true, 'Modules/Forum');
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('TXT_DELETE', $this->lng->txt('forums_info_delete_draft'));
		$this->ctrl->setParameter($this, 'draft_id', (int)$_GET['draft_id']);
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', ilUtil::stripSlashes($_GET['orderby']));
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('CMD_CONFIRM', 'deletePostingDraft');
		$form_tpl->setVariable('CMD_CANCEL', 'viewThread');
		$form_tpl->setVariable('CANCEL_BUTTON', $this->lng->txt('cancel'));
		$form_tpl->setVariable('CONFIRM_BUTTON', $this->lng->txt('confirm'));

		return $form_tpl->get();
	}

	public function getActivationFormHTML()
	{
		$form_tpl = new ilTemplate('tpl.frm_activation_post_form.html', true, true, 'Modules/Forum');
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', ilUtil::stripSlashes($_GET['orderby']));
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPostActivation'));
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('TXT_ACT', $this->lng->txt('activate_post_txt'));								
		$form_tpl->setVariable('CONFIRM_BUTTON', $this->lng->txt('activate_only_current'));
		$form_tpl->setVariable('CMD_CONFIRM', 'performPostActivation');
		$form_tpl->setVariable('CANCEL_BUTTON', $this->lng->txt('cancel'));
		$form_tpl->setVariable('CMD_CANCEL', 'viewThread');
		$this->ctrl->clearParameters($this);

		return $form_tpl->get(); 
	}
	
	public function getCensorshipFormHTML()
	{
		$frm = $this->object->Forum;
		$form_tpl = new ilTemplate('tpl.frm_censorship_post_form.html', true, true, 'Modules/Forum');

		$form_tpl->setVariable('ANCHOR', $this->objCurrentPost->getId());
		$form_tpl->setVariable('SPACER', '<hr noshade="noshade" width="100%" size="1" align="center" />');
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'orderby', ilUtil::stripSlashes($_GET['orderby']));
		$form_tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'viewThread'));
		$this->ctrl->clearParameters($this);
		$form_tpl->setVariable('TXT_CENS_MESSAGE', $this->lng->txt('forums_the_post'));
		$form_tpl->setVariable('TXT_CENS_COMMENT', $this->lng->txt('forums_censor_comment').':');
		$form_tpl->setVariable('CENS_MESSAGE', $frm->prepareText($this->objCurrentPost->getCensorshipComment(), 2));

		if($this->objCurrentPost->isCensored())
		{
			$form_tpl->setVariable('TXT_CENS', $this->lng->txt('forums_info_censor2_post'));
			$form_tpl->setVariable('YES_BUTTON', $this->lng->txt('confirm'));
			$form_tpl->setVariable('NO_BUTTON', $this->lng->txt('cancel'));
			$form_tpl->setVariable('CMD_REVOKE_CENSORSHIP', 'revokeCensorship');
			$form_tpl->setVariable('CMD_CANCEL_REVOKE_CENSORSHIP', 'viewThread');
		}
		else
		{
			$form_tpl->setVariable('TXT_CENS', $this->lng->txt('forums_info_censor_post'));
			$form_tpl->setVariable('CANCEL_BUTTON', $this->lng->txt('cancel'));
			$form_tpl->setVariable('CONFIRM_BUTTON', $this->lng->txt('confirm'));
			$form_tpl->setVariable('CMD_ADD_CENSORSHIP', 'addCensorship');
			$form_tpl->setVariable('CMD_CANCEL_ADD_CENSORSHIP', 'viewThread');
		}

  		return $form_tpl->get(); 
	}

	/**
	 * @throws ilHtmlPurifierNotFoundException
	 */
	private function initReplyEditForm()
	{
		/**
		 * @var $oFDForum ilFileDataForum
		 */

		// init objects
		$oForumObjects = $this->getForumObjects();
		$frm = $oForumObjects['frm'];
		$oFDForum = $oForumObjects['file_obj'];

		$this->replyEditForm = new ilPropertyFormGUI();
		$this->replyEditForm->setId('id_showreply');
		$this->replyEditForm->setTableWidth('100%');
		$cancel_cmd = 'cancelPost';
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
		{
			$this->ctrl->setParameter($this, 'action', 'ready_showreply');
		}
		else if($_GET['action'] == 'showdraft' || $_GET['action'] == 'editdraft')
		{
			$this->ctrl->setParameter($this, 'action', $_GET['action']);
			$this->ctrl->setParameter($this, 'draft_id', (int)$_GET['draft_id']);
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
		else if($_GET['action'] == 'publishDraft' || $_GET['action'] == 'editdraft')
		{
			$this->replyEditForm->setFormAction($this->ctrl->getFormAction($this, 'publishDraft'), $this->objCurrentPost->getId());
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
		elseif($_GET['action'] == 'showdraft' || $_GET['action'] == 'editdraft')
		{
			$this->replyEditForm->setTitle($this->lng->txt('forums_edit_draft'));
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
		$oSubjectGUI->setMaxLength(255);
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
		$oPostGUI->setRows(15);
		$oPostGUI->setUseRte(true);
		$oPostGUI->addPlugin('latex');
		$oPostGUI->addButton('latex');
		$oPostGUI->addButton('pastelatex');
		$oPostGUI->addPlugin('ilfrmquote');

		//$oPostGUI->addPlugin('code'); 
		if($_GET['action'] == 'showreply' || $_GET['action'] == 'showdraft')
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

		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply' || $_GET['action'] == 'showdraft' || $_GET['action'] == 'editdraft')
		{
			$oPostGUI->setRTESupport($this->user->getId(), 'frm~', 'frm_post', 'tpl.tinymce_frm_post.html', false, '3.5.11');
		}
		else
		{
			$oPostGUI->setRTESupport($this->objCurrentPost->getId(), 'frm', 'frm_post', 'tpl.tinymce_frm_post.html', false, '3.5.11');
		}
		// purifier
		$oPostGUI->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));

		$this->replyEditForm->addItem($oPostGUI);

		// notification only if gen. notification is disabled and forum isn't anonymous
		$umail = new ilMail($this->user->getId());
		if($this->rbac->system()->checkAccess('internal_mail', $umail->getMailObjectReferenceId()) &&
		   !$frm->isThreadNotificationEnabled($this->user->getId(), $this->objCurrentPost->getThreadId()) &&
		   !$this->objProperties->isAnonymized())
		{
			$oNotificationGUI = new ilCheckboxInputGUI($this->lng->txt('forum_direct_notification'), 'notify');
			$oNotificationGUI->setInfo($this->lng->txt('forum_notify_me'));
			
			$this->replyEditForm->addItem($oNotificationGUI);
		}

		if($this->objProperties->isFileUploadAllowed())
		{
			$oFileUploadGUI = new ilFileWizardInputGUI($this->lng->txt('forums_attachments_add'), 'userfile');
			$oFileUploadGUI->setFilenames(array(0 => ''));
			$this->replyEditForm->addItem($oFileUploadGUI);
		}

		if(
			$this->user->isAnonymous() &&
			!$this->user->isCaptchaVerified() &&
			ilCaptchaUtil::isActiveForForum()
		)
		{
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

		if(ilForumPostDraft::isAutoSavePostDraftAllowed())
		{
			if($_GET['action'] == 'showdraft' || $_GET['action'] == 'editdraft')
			{
				$draftInfoGUI = new ilNonEditableValueGUI('','autosave_info', true);
				$draftInfoGUI->setValue(sprintf($this->lng->txt('autosave_draft_info'), ilForumPostDraft::lookupAutosaveInterval()));
				$this->replyEditForm->addItem($draftInfoGUI);
			}
			else if($_GET['action'] != 'showedit' && $_GET['action'] != 'ready_showedit')
			{
				$draftInfoGUI = new ilNonEditableValueGUI('','autosave_info', true);
				$draftInfoGUI->setValue(sprintf($this->lng->txt('autosave_post_draft_info'), ilForumPostDraft::lookupAutosaveInterval()));
				$this->replyEditForm->addItem($draftInfoGUI);
			}
			
			$selected_draft_id = (int)$_GET['draft_id'];
			$draftObj = new ilForumPostDraft($this->user->getId(), $this->objCurrentPost->getId(), $selected_draft_id);
			if($draftObj->getDraftId() > 0)
			{
				$oFDForumDrafts = new ilFileDataForumDrafts(0, $draftObj->getDraftId());
				if(count($oFDForumDrafts->getFilesOfPost()))
				{
					$oExistingAttachmentsGUI = new ilCheckboxGroupInputGUI($this->lng->txt('forums_delete_file'), 'del_file');
					foreach($oFDForumDrafts->getFilesOfPost() as $file)
					{
						$oAttachmentGUI = new ilCheckboxInputGUI($file['name'], 'del_file');
						$oAttachmentGUI->setValue($file['md5']);
						$oExistingAttachmentsGUI->addOption($oAttachmentGUI);
					}
					$this->replyEditForm->addItem($oExistingAttachmentsGUI);
				}
			}
		}

		if($this->isTopLevelReplyCommand())
		{
			$this->replyEditForm->addCommandButton('saveTopLevelPost', $this->lng->txt('create'));
		}
		else if(ilForumPostDraft::isSavePostDraftAllowed() && $_GET['action'] == 'editdraft')
		{
			$this->replyEditForm->addCommandButton('publishDraft', $this->lng->txt('publish'));
		}
		else
		{
			$this->replyEditForm->addCommandButton('savePost', $this->lng->txt('save'));
		}
		$hidden_draft_id= new ilHiddenInputGUI('draft_id');
		if(isset($_GET['draft_id']) && (int)$_GET['draft_id']> 0)
		{
			$auto_save_draft_id = (int)$_GET['draft_id'];
		}
		$hidden_draft_id->setValue($auto_save_draft_id);
		$this->replyEditForm->addItem($hidden_draft_id);

		if($_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply' || $_GET['action'] == 'editdraft')
		{
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
			
			if(!$this->user->isAnonymous() 
				&& ($_GET['action'] == 'editdraft' || $_GET['action'] == 'showreply' || $_GET['action'] == 'ready_showreply')
				&& ilForumPostDraft::isSavePostDraftAllowed()
			)
			{
				if(ilForumPostDraft::isAutoSavePostDraftAllowed())
				{
					$this->decorateWithAutosave($this->replyEditForm);	
				}

				if($_GET['action'] == 'editdraft')
				{
					$this->replyEditForm->addCommandButton('updateDraft', $this->lng->txt('save_message'));
				}
				else
				{
					$this->replyEditForm->addCommandButton('saveAsDraft', $this->lng->txt('save_message'));	
				}
				
				$cancel_cmd = 'cancelDraft';
			}
		}
		$this->replyEditForm->addCommandButton($cancel_cmd, $this->lng->txt('cancel'));

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
	public function createTopLevelPostObject()
	{
		if(isset($_GET['draft_id']) && (int)$_GET['draft_id'] > 0 && !$this->user->isAnonymous()
			&& ilForumPostDraft::isSavePostDraftAllowed())
		{
			$draft_obj = new ilForumPostDraft($this->user->getId(), $this->objCurrentPost->getId(), (int)$_GET['draft_id']);
		}
			
		if($draft_obj instanceof ilForumPostDraft && $draft_obj->getDraftId() > 0)
		{
			$this->ctrl->setParameter($this, 'action',  'editdraft');
			$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
			$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
			$this->ctrl->setParameter($this, 'draft_id',  $draft_obj->getDraftId());
			$this->ctrl->setParameter($this, 'offset', 0);
			$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
			$this->ctrl->redirect($this, 'editDraft');
		}
		else
		{
			$this->viewThreadObject();
		}
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

	public function publishSelectedDraftObject()
	{ 
		if(isset($_GET['draft_id']) && (int)$_GET['draft_id'] > 0)
		{
			$this->publishDraftObject(false);
		}	
	}	
	
	public function publishDraftObject($use_replyform = true)
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('add_reply', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->objCurrentTopic->getId()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_deleted'), true);
			$this->ctrl->redirect($this);
		}

		if ($this->objCurrentTopic->isClosed()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_closed'), true);
			$this->ctrl->redirect($this);
		}

		if (!$this->objCurrentPost->getId()) {
			$_GET['action'] = '';
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_parent_deleted'));
			$this->viewThreadObject();
			return;
		}

		$post_id = $this->objCurrentPost->getId();
		
		$draft_obj = new ilForumPostDraft($this->user->getId(), $post_id, (int)$_GET['draft_id']);
		
		if($use_replyform)
		{
			$oReplyEditForm = $this->getReplyEditForm();
			if(!$oReplyEditForm->checkInput() && !$draft_obj instanceof ilForumPostDraft)
			{
				$oReplyEditForm->setValuesByPost();
				return $this->viewThreadObject();
			}
			$post_subject = $oReplyEditForm->getInput('subject');
			$post_message = $oReplyEditForm->getInput('message');
			$mob_direction = 0;
		}
		else
		{
			$post_subject = $draft_obj->getPostSubject();
			$post_message = $draft_obj->getPostMessage();
			$mob_direction = 1;
		}
		
		if($draft_obj->getDraftId() > 0)
		{
			// init objects
			$oForumObjects = $this->getForumObjects();
			$frm = $oForumObjects['frm'];
			$frm->setMDB2WhereCondition(' top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
			
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
			
			$newPost = $frm->generatePost(
				$draft_obj->getForumId(),
				$draft_obj->getThreadId(),
				$this->user->getId(),
				$draft_obj->getPostDisplayUserId(),
				ilRTE::_replaceMediaObjectImageSrc($post_message, $mob_direction),
				$draft_obj->getPostId(),
				(int)$draft_obj->getNotify(),
				$this->handleFormInput($post_subject , false),
				$draft_obj->getPostUserAlias(),
				'',
				$status,
				$send_activation_mail
			);
			
			$this->object->markPostRead($this->user->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());
			
			$uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());
			
			foreach($uploadedObjects as $mob)
			{
				ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
				ilObjMediaObject::_saveUsage($mob,'frm:html', $newPost);
			}
			ilForumUtil::saveMediaObjects($post_message,  'frm:html', $newPost, $mob_direction);
			
			if($this->objProperties->isFileUploadAllowed())
			{
				$file = $_FILES['userfile'];
				if(is_array($file) && !empty($file))
				{
					$tmp_file_obj = new ilFileDataForum($this->object->getId(), $newPost);
					$tmp_file_obj->storeUploadedFile($file);
				}
				
				//move files of draft to posts directory
				$oFDForum = new ilFileDataForum($this->object->getId(), $newPost);
				$oFDForumDrafts = new ilFileDataForumDrafts($this->object->getId(), $draft_obj->getDraftId());
				
				$oFDForumDrafts->moveFilesOfDraft($oFDForum->getForumPath(), $newPost);
				$oFDForumDrafts->delete();
			}
			
			if(ilForumPostDraft::isSavePostDraftAllowed())
			{
				$GLOBALS['ilAppEventHandler']->raise(
					'Modules/Forum',
					'publishedDraft',
					array('draftObj' => $draft_obj,
					      'obj_id' => $this->object->getId(),
					      'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed())
				);
			}
			$draft_obj->deleteDraft();
			
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
				$message .= $this->lng->txt('forums_post_needs_to_be_activated');
			}
			else
			{
				$message .= $this->lng->txt('forums_post_new_entry');
			}
			
			$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'][] = (int)$this->objCurrentPost->getId();
	
			$this->ctrl->clearParameters($this);
			ilUtil::sendSuccess($message, true);
			$this->ctrl->setParameter($this, 'pos_pk', $newPost);
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());

			$this->ctrl->redirect($this, 'viewThread');	
		}
	}
	
	/**
	 * @return bool
	 */
	public function savePostObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->objCurrentTopic->getId()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_deleted'), true);
			$this->ctrl->redirect($this);
		}

		if ($this->objCurrentTopic->isClosed()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_closed'), true);
			$this->ctrl->redirect($this);
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentTopic);

		if(!isset($_POST['del_file']) || !is_array($_POST['del_file'])) $_POST['del_file'] = array();

		$oReplyEditForm = $this->getReplyEditForm();
		if ($oReplyEditForm->checkInput()) {
			if (!$this->objCurrentPost->getId()) {
				$_GET['action'] = '';
				\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_parent_deleted'));
				$this->viewThreadObject();
				return;
			}

			$this->doCaptchaCheck();

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
				if (!$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id'])) {
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
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
					$user_alias = $this->user->getLogin();
					$display_user_id = $this->user->getId();
				}
				
				$newPost = $frm->generatePost(
					$topicData['top_pk'], 
					$this->objCurrentTopic->getId(),
					$this->user->getId(),
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

				if(ilForumPostDraft::isSavePostDraftAllowed())
				{
					$draft_id = 0;
					if(ilForumPostDraft::isAutoSavePostDraftAllowed())
					{
						$draft_id = $_POST['draft_id']; // info aus dem autosave?
					}	
					$draft_obj = new ilForumPostDraft($this->user->getId(), $this->objCurrentPost->getId(), $draft_id);
					if($draft_obj instanceof ilForumPostDraft)
					{
						$draft_obj->deleteDraft();
					}
				}

				// mantis #8115: Mark parent as read
				$this->object->markPostRead($this->user->getId(), (int) $this->objCurrentTopic->getId(), (int) $this->objCurrentPost->getId());

				// copy temporary media objects (frm~)
				ilForumUtil::moveMediaObjects($oReplyEditForm->getInput('message'), 'frm~:html', $this->user->getId(), 'frm:html', $newPost);

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
					$message .= $this->lng->txt('forums_post_needs_to_be_activated');
				}
				else
				{
					$message .= $this->lng->txt('forums_post_new_entry');
				}

				ilUtil::sendSuccess($message, true);
				$this->ctrl->clearParameters($this);
				$this->ctrl->setParameter($this, 'post_created_below', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'pos_pk', $newPost);
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
				$this->ctrl->redirect($this, 'viewThread');
			}
			else
			{
				if((!$this->is_moderator &&
				   !$this->objCurrentPost->isOwner($this->user->getId())) || $this->objCurrentPost->isCensored() ||
				   $this->user->getId() == ANONYMOUS_USER_ID)
				{
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}

				$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

				// remove usage of deleted media objects
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
				
				// save old activation status for send_notification decision
				$old_status_was_active = $this->objCurrentPost->isActivated();
				
				// if active post has been edited posting mus be activated again by moderator
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
				$this->objCurrentPost->setUpdateUserId($this->user->getId());
				
				// edit: update post
				if($this->objCurrentPost->update())
				{
					$this->objCurrentPost->reload();
					
					// Change news item accordingly
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
						
						if($this->objCurrentPost->getMessage() != strip_tags($this->objCurrentPost->getMessage()))
						{
							$news_item->setContentHtml(true);
						}
						else
						{
							$news_item->setContentHtml(false);
						}
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
							'ref_id'                => $this->object->getRefId(),
							'post'                  => $this->objCurrentPost,
							'notify_moderators'     => (bool)$send_activation_mail,
							'old_status_was_active' => (bool)$old_status_was_active
						)
					);
	
					ilUtil::sendSuccess($this->lng->txt('forums_post_modified'), true);
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
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

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
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

		$oForumObjects = $this->getForumObjects();
		/**
		 * @var $frm ilForum
		 */
		$frm = $oForumObjects['frm'];

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
		$bottom_toolbar                    = clone $this->toolbar;
		$bottom_toolbar_split_button_items = array();
		

		$this->tpl->addCss('./Modules/Forum/css/forum_tree.css');
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

		if(!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
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

		$selected_draft_id = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		$action = (string)($this->httpRequest->getQueryParams()['action'] ?? '');

		if (isset($this->httpRequest->getQueryParams()['file'])) {
			$file_obj_for_delivery = $file_obj;
			if (ilForumPostDraft::isSavePostDraftAllowed() && $selected_draft_id > 0) {
				$file_obj_for_delivery = new ilFileDataForumDrafts($forumObj->getId(), $selected_draft_id);
			}
			$file_obj_for_delivery->deliverFile(\ilUtil::stripSlashes($this->httpRequest->getQueryParams()['file']));
		}

		if (!$this->objCurrentTopic->getId()) {
			$this->ctrl->redirect($this, 'showThreads');
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentTopic);

		// Set context for login
		$append = '_'.$this->objCurrentTopic->getId().
			($this->objCurrentPost->getId() ? '_'.$this->objCurrentPost->getId() : '');
		$this->tpl->setLoginTargetPar('frm_'.$_GET['ref_id'].$append);

		// delete temporary media object (not in case a user adds media objects and wants to save an invalid form)
		if (!in_array($action, ['showreply', 'showedit'])) {
			try {
				$mobs = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());
				foreach ($mobs as $mob) {					
					if (ilObjMediaObject::_exists($mob)) {
						ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
						$mob_obj = new ilObjMediaObject($mob);
						$mob_obj->delete();
					}
				}
			} catch(Exception $e) {}
		}

		if ($this->isHierarchicalView()) {
			$exp = new ilForumExplorerGUI('frm_exp_' . $this->objCurrentTopic->getId(), $this, 'viewThread');
			$exp->setThread($this->objCurrentTopic);
			if (!$exp->handleCommand()) {
				$this->tpl->setLeftNavContent($exp->getHTML());
			}
		}

		$this->lng->loadLanguageModule('forum');

		if (!$this->getCreationMode() && $this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->ilNavigationHistory->addItem(
				(int)$this->object->getRefId(), \ilLink::_getLink((int)$this->object->getRefId(), 'frm'), 'frm'
			);
		}

		// save last access
		$forumObj->updateLastAccess($this->user->getId(), (int) $this->objCurrentTopic->getId());
		
		$this->prepareThreadScreen($forumObj);
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_view.html', 'Modules/Forum');

		if (isset($this->httpRequest->getQueryParams()['anchor'])) {
			$this->tpl->setVariable('JUMP2ANCHOR_ID', (int)$this->httpRequest->getQueryParams()['anchor']);
		}

		if ($this->isHierarchicalView()) {
			$orderField = 'frm_posts_tree.rgt';
			$this->objCurrentTopic->setOrderDirection('DESC');
		} else {
			$orderField = 'frm_posts.pos_date';
			$this->objCurrentTopic->setOrderDirection(
				in_array($this->objProperties->getDefaultView(), array(ilForumProperties::VIEW_DATE_ASC, ilForumProperties::VIEW_TREE))
				? 'ASC' : 'DESC'
			);
		}

		$posNum = 0;

		// get forum- and thread-data
		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));

		if (is_array($topicData = $frm->getOneTopic())) {
			// Visit-Counter for topic
			$this->objCurrentTopic->updateVisits();
			
			$this->tpl->setTitle($this->lng->txt('forums_thread')." \"".$this->objCurrentTopic->getSubject()."\"");			
		
			// build location-links
			$this->locator->addRepositoryItems();
			$this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "_top");
			$this->tpl->setLocator();
																		 
			// set tabs					
			// menu template (contains linkbar)
			/** @var $menutpl ilTemplate */
			$menutpl = new ilTemplate('tpl.forums_threads_menu.html', true, true, 'Modules/Forum');

			// mark all as read
			if (
				!$this->user->isAnonymous() &&
				$forumObj->getCountUnread($this->user->getId(), (int) $this->objCurrentTopic->getId(), true)
			) {
				$this->ctrl->setParameter($this, 'mark_read', '1');
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());

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

			
			$print_thr_button = ilLinkButton::getInstance();
			$print_thr_button->setCaption('forums_print_thread');
			$print_thr_button->setUrl($this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printThread'));

			$bottom_toolbar_split_button_items[] = $print_thr_button;

			$this->ctrl->clearParametersByClass('ilforumexportgui');

			$this->addHeaderAction();

			if (isset($this->httpRequest->getQueryParams()['mark_read'])) {
				$forumObj->markThreadRead($this->user->getId(), (int)$this->objCurrentTopic->getId());
				ilUtil::sendInfo($this->lng->txt('forums_thread_marked'), true);
			}

			// get complete tree of thread
			$first_node = $this->objCurrentTopic->getFirstPostNode();
			$this->objCurrentTopic->setOrderField($orderField);
			$subtree_nodes = $this->objCurrentTopic->getPostTree($first_node);

			if (!$this->isTopLevelReplyCommand() &&
				$first_node instanceof ilForumPost &&
				!$this->objCurrentTopic->isClosed() &&
				$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id'])
			) {
				$reply_button = ilLinkButton::getInstance();
				$reply_button->setPrimary(true);
				$reply_button->setCaption('add_new_answer');
				$this->ctrl->setParameter($this, 'action',  'showreply');
				$this->ctrl->setParameter($this, 'pos_pk', $first_node->getId());
				$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
				$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
				$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);

				$reply_button->setUrl($this->ctrl->getLinkTarget($this, 'createTopLevelPost', 'frm_page_bottom'));

				$this->ctrl->clearParameters($this);
				array_unshift($bottom_toolbar_split_button_items, $reply_button);
			}

			// no posts
			if (!$posNum = count($subtree_nodes)) {
				ilUtil::sendInfo($this->lng->txt('forums_no_posts_available'));
			}

			$pageHits = $frm->getPageHits();

			$z = 0;
		
			// navigation to browse
			if ($posNum > $pageHits) {
				$params = array(
					'ref_id'		=> $_GET['ref_id'],
					'thr_pk'		=> $this->objCurrentTopic->getId(),
					'orderby'		=> $_GET['orderby']
				);

				if (!isset($_GET['offset'])) {
					$Start = 0;
				} else {
					$Start = (int)$_GET['offset'];
				}

				$linkbar = ilUtil::Linkbar($this->ctrl->getLinkTarget($this, 'viewThread'), $posNum, $pageHits, $Start, $params);

				if ($linkbar != '') {
					$menutpl->setCurrentBlock('linkbar');
					$menutpl->setVariable('LINKBAR', $linkbar);
					$menutpl->parseCurrentBlock();
				}
			}

			$this->tpl->setVariable('THREAD_MENU', $menutpl->get());
		
			// assistance val for anchor-links
			$jump = 0;
			$render_drafts = false;
			$draftsObjects = NULL;

			if (ilForumPostDraft::isSavePostDraftAllowed() && !$this->user->isAnonymous()) {
				$draftsObjects = ilForumPostDraft::getInstancesByUserIdAndThreadId($this->user->getId(), $this->objCurrentTopic->getId());
				if (count($draftsObjects) > 0) {
					$render_drafts = true;
				}
			}

			foreach($subtree_nodes as $node) {
				/** @var $node ilForumPost */
				$this->ctrl->clearParameters($this);
				
				if ($this->objCurrentPost->getId() && $this->objCurrentPost->getId() == $node->getId()) {
					$jump++;
				}

				if ($posNum > $pageHits && $z >= ($Start + $pageHits)) {
					// if anchor-link was not found ...
					if ($this->objCurrentPost->getId() && $jump < 1) {
						$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
						$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
						$this->ctrl->setParameter($this, 'offset', ($Start + $pageHits));
						$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
						$this->ctrl->redirect($this, 'viewThread', $this->objCurrentPost->getId());
					} else {
						break;
					}
				}

				if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits) {
					if (!$this->isTopLevelReplyCommand() && $this->objCurrentPost->getId() == $node->getId()) {
						# actions for "active" post
						if ($this->is_moderator || $node->isActivated() || $node->isOwner($this->user->getId())) {
							if(!$this->objCurrentTopic->isClosed() && in_array($action, [
								'showreply', 'showedit', 'showdraft', 'editdraft'
							])) {
								$this->renderPostingForm($frm, $node, $action);
							} elseif (!$this->objCurrentTopic->isClosed() && $action === 'delete') {
								if (
									$this->is_moderator ||
									($node->isOwner($this->user->getId()) && !$node->hasReplies()) &&
									$this->user->getId() != ANONYMOUS_USER_ID
								) {
									$this->tpl->setVariable('FORM', $this->getDeleteFormHTML());
								}
							} elseif (!$this->objCurrentTopic->isClosed() && $action === 'censor') {
								if ($this->is_moderator) {
									$this->tpl->setVariable('FORM', $this->getCensorshipFormHTML());
								}
							} elseif (!$this->objCurrentTopic->isClosed() && $this->displayConfirmPostActivation()) {
								if ($this->is_moderator) {
									$this->tpl->setVariable('FORM', $this->getActivationFormHTML());
								}
							} 
						}
					}
					$this->renderPostContent($node, $action, $Start, $z);
					$this->renderDraftContent($action, $render_drafts, $node, $selected_draft_id);
				}
				$z++;
			}

			if ($first_node instanceof \ilForumPost) {
				if (!$this->objCurrentTopic->isClosed() && ($action === 'showdraft'|| $action === 'editdraft')) {
					$this->renderPostingForm($frm, $first_node, $action);
				}
				$this->renderDraftContent($action, $render_drafts, $first_node, $selected_draft_id);
			}

			if(
				$first_node instanceof ilForumPost &&
				in_array($this->ctrl->getCmd(), array('createTopLevelPost', 'saveTopLevelPost', 'quoteTopLevelPost')) &&
				!$this->objCurrentTopic->isClosed() &&
				$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id']))
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
				$jsTpl->setVariable('IL_FRM_QUOTE_CALLBACK_SRC', $this->ctrl->getLinkTarget($this, 'getQuotationHTMLAsynch', '', true));
				$this->ctrl->clearParameters($this);
				$this->tpl->setVariable('BOTTOM_FORM_ADDITIONAL_JS', $jsTpl->get());;
				$this->tpl->setVariable('BOTTOM_FORM', $form->getHTML());
			}
		}
		else
		{
			$this->tpl->setCurrentBlock('posts_no');
			$this->tpl->setVariable('TXT_MSG_NO_POSTS_AVAILABLE', $this->lng->txt('forums_posts_not_available'));
			$this->tpl->parseCurrentBlock();
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
			$this->toolbar->addStickyItem($bottom_split_button);
		}
		
		if($bottom_toolbar_split_button_items)
		{
			$bottom_toolbar->addSeparator();
		}

		$to_top_button = ilLinkButton::getInstance();
		$to_top_button->setCaption('top_of_page');
		$to_top_button->setUrl('#frm_page_top');
		$bottom_toolbar->addButtonInstance($to_top_button);
		if ($posNum > 0) {
			$this->tpl->setVariable('TOOLBAR_BOTTOM', $bottom_toolbar->getHTML());
		}

		$permalink = new ilPermanentLinkGUI('frm', $this->object->getRefId(), '_'.$this->objCurrentTopic->getId());
		$this->tpl->setVariable('PRMLINK', $permalink->getHTML());

		$this->tpl->addOnLoadCode('$(".ilFrmPostContent img").each(function() {
			var $elm = $(this);
			$elm.css({
				maxWidth: $elm.attr("width") + "px", 
				maxHeight: $elm.attr("height")  + "px"
			});
			$elm.removeAttr("width");
			$elm.removeAttr("height");
		});');

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
		$profile_gui = new ilPublicUserProfileGUI((int)$_GET['user']);
		$add = $this->getUserProfileAdditional((int)$_GET['ref_id'], (int)$_GET['user']);
		$profile_gui->setAdditional($add);
		$profile_gui->setBackUrl(\ilUtil::stripSlashes($_GET['backurl']));
		$this->tpl->setContent($this->ctrl->getHTML($profile_gui));
	}
	
	protected function getUserProfileAdditional($a_forum_ref_id, $a_user_id)
	{
		if(!$this->access->checkAccess('read', '', $a_forum_ref_id))
		{
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}
		
		$this->lng->loadLanguageModule('forum');
		
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
		if($this->access->checkAccess('moderate_frm', '', $a_forum_ref_id))
		{
			$numPosts = $frm->countUserArticles(addslashes($a_user_id));
		}
		else
		{
			$numPosts = $frm->countActiveUserArticles(addslashes($a_user_id));
		}
		
		return array($this->lng->txt('forums_posts') => $numPosts);
	}

	public function performThreadsActionObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

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
			else if($_POST['selected_cmd'] == 'enable_notifications' && $this->settings->get('forum_notification') != 0)
			{
				for($i = 0; $i < count($_POST['thread_ids']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
					$this->ensureThreadBelongsToForum((int)$this->object->getId(), $tmp_obj);
					$tmp_obj->enableNotification($this->user->getId());
				}
	
				$this->ctrl->redirect($this, 'showThreads');
			}
			else if($_POST['selected_cmd'] == 'disable_notifications' && $this->settings->get('forum_notification') != 0)
			{
				for($i = 0; $i < count($_POST['thread_ids']); $i++)
				{
					$tmp_obj = new ilForumTopic($_POST['thread_ids'][$i]);
					$this->ensureThreadBelongsToForum((int)$this->object->getId(), $tmp_obj);
					$tmp_obj->disableNotification($this->user->getId());
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
						$this->ensureThreadBelongsToForum((int)$this->object->getId(), $tmp_obj);
						$tmp_obj->close();
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
						$this->ensureThreadBelongsToForum((int)$this->object->getId(), $tmp_obj);
						$tmp_obj->reopen();
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
						$this->ensureThreadBelongsToForum((int)$this->object->getId(), $tmp_obj);
						$makeSticky =  $tmp_obj->makeSticky();

						if(!$makeSticky)
						{
							$message = $this->lng->txt('sel_threads_already_sticky');
						}
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
						$this->ensureThreadBelongsToForum((int)$this->object->getId(), $tmp_obj);
						$unmakeSticky = $tmp_obj->unmakeSticky();
						if(!$unmakeSticky)
						{
							$message = $this->lng->txt('sel_threads_already_unsticky');
						}
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
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threads2move = $_SESSION['threads2move'];
		if (!is_array($threads2move) || !count($threads2move)) {
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}

		if (!$this->access->checkAccess('read', '', (int)$_POST['frm_ref_id'])) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threads = [];
		array_walk($threads2move, function($threadId) use (&$threads) {
			$thread = new \ilForumTopic($threadId);
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $thread);

			$threads[] = $threadId;
		});

		if (isset($_POST['frm_ref_id']) && (int)$_POST['frm_ref_id']) {
            $errorMessages = $this->object->Forum->moveThreads(
                (array) $_SESSION['threads2move'],
                $this->object->getRefId(),
                $this->ilObjDataCache->lookupObjId($_POST['frm_ref_id'])
            );

            if (array() !== $errorMessages) {
                \ilUtil::sendFailure(
                    implode("<br><br>", $errorMessages),
                    true
                );
                return $this->ctrl->redirectByClass('ilObjForumGUI', 'showThreads');
            }

			unset($_SESSION['threads2move']);
			ilUtil::sendInfo($this->lng->txt('threads_moved_successfully'), true);
			$this->ctrl->redirect($this, 'showThreads');
		} else {
			ilUtil::sendInfo($this->lng->txt('no_forum_selected'));
			$this->moveThreadsObject();
		}
	}
	
	public function cancelMoveThreadsObject()
	{
		unset($_SESSION['threads2move']);
		$this->ctrl->redirect($this, 'showThreads');
	}

	public function moveThreadsObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threads2move = $_SESSION['threads2move'];
		if(!is_array($threads2move) || !count($threads2move))
		{
			ilUtil::sendInfo($this->lng->txt('select_at_least_one_thread'), true);
			$this->ctrl->redirect($this, 'showThreads');
		}

		$threads = [];
		$isModerator = $this->is_moderator;
		array_walk($threads2move, function($threadId) use (&$threads, $isModerator) {
			$thread = new \ilForumTopic($threadId, $isModerator);
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $thread);

			$threads[] = $thread;
		});

		$exp = new ilForumMoveTopicsExplorer($this, 'moveThreads');
		$exp->setPathOpen($this->object->getRefId());
		$exp->setNodeSelected(isset($_POST['frm_ref_id']) && (int)$_POST['frm_ref_id'] ? (int)$_POST['frm_ref_id'] : 0);
		$exp->setCurrentFrmRefId($this->object->getRefId());
		$exp->setHighlightedNode($this->object->getRefId());
		if(!$exp->handleCommand())
		{
			$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_move.html', 'Modules/Forum');

			if(!$this->hideToolbar())
				$this->toolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this));

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
			foreach ($threads as $thread) {
				$result[$counter]['num'] = $counter + 1;
				$result[$counter]['thr_subject'] = $thread->getSubject();
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

	private function isWritingWithPseudonymAllowed(): bool
	{
		if ($this->objProperties->isAnonymized() && (!$this->is_moderator || !$this->objProperties->getMarkModeratorPosts())) {
			return true;
		}
		return false;
	}

	protected function deleteThreadDraftsObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$draftIds = array_filter((array)($this->httpRequest->getParsedBody()['draft_ids'] ?? []));
		if (0 === count($draftIds)) {
			$draftIds = array_filter([(int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0)]);
		}

		$instances = \ilForumPostDraft::getDraftInstancesByUserId($this->user->getId());
		$checkedDraftIds = [];
		foreach ($draftIds as $draftId) {
			if (array_key_exists($draftId, $instances)) {
				$checkedDraftIds[] = $draftId;
				$draft = $instances[$draftId];

				$this->deleteMobsOfDraft($draft->getDraftId(), $draft->getPostMessage());
				
				$draftFileData = new \ilFileDataForumDrafts(0, $draft->getDraftId());
				$draftFileData->delete();

				$GLOBALS['ilAppEventHandler']->raise(
					'Modules/Forum', 'deletedDraft', [
					'draftObj' => $draft,
					'obj_id' => $this->object->getId(),
					'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed(),
				]);

				$draft->deleteDraft();
			}
		}

		if (count($checkedDraftIds) > 1) {
			\ilUtil::sendInfo($this->lng->txt('delete_drafts_successfully'), true);
		} else {
			\ilUtil::sendInfo($this->lng->txt('delete_draft_successfully'), true);
		}
		$this->ctrl->redirect($this, 'showThreads');
	}

	/**
	 * @param bool $isDraft
	 * @return \ilPropertyFormGUI
	 */
	private function buildThreadForm($isDraft = false): \ilPropertyFormGUI
	{
		$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		$allowNotification = !$this->objProperties->isAnonymized();

		$mail = new \ilMail($this->user->getId());
		if (!$this->rbac->system()->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
			$allowNotification = false;
		}

		$form = new \ilForumThreadFormGUI(
			$this, $this->objProperties, $this->isWritingWithPseudonymAllowed(),
			$allowNotification, $isDraft, $draftId
		);

		$this->decorateWithAutosave($form);

		return $form;
	}

	protected function createThreadObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('add_thread', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$templateForWidthHandling = new \ilTemplate('tpl.create_thread_form.html', true, true, 'Modules/Forum');
		$templateForWidthHandling->setVariable('CREATE_FORM', $this->buildThreadForm()->getHTML());
		$templateForWidthHandling->parseCurrentBlock();

		$this->tpl->setContent($templateForWidthHandling->get());
	}

	/**
	 * Refactored thread creation to method, refactoring to a separate class should be done in next refactoring steps
	 * @param ilForumPostDraft $draft
	 * @param bool $createFromDraft
	 */
	private function createThread(\ilForumPostDraft $draft, bool $createFromDraft = false)
	{
		if (
			!$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
			!$this->access->checkAccess('read', '', $this->object->getRefId())
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		$topicData = $frm->getOneTopic();

		$form = $this->buildThreadForm($createFromDraft);
		if ($form->checkInput()) {
			$this->doCaptchaCheck();

			$userIdForDisplayPurposes = $this->user->getId();
			if ($this->isWritingWithPseudonymAllowed()) {
				$userIdForDisplayPurposes = 0;
			}

			$status = 1;
			if (
				($this->objProperties->isPostActivationEnabled() && !$this->is_moderator) ||
				$this->objCurrentPost->isAnyParentDeactivated()
			) {
				$status = 0;
			}

			if ($createFromDraft) {
				$newThread = new \ilForumTopic(0, true, true);
				$newThread->setForumId($topicData['top_pk']);
				$newThread->setThrAuthorId($draft->getPostAuthorId());
				$newThread->setDisplayUserId($draft->getPostDisplayUserId());
				$newThread->setSubject($this->handleFormInput($form->getInput('subject'), false));
				$newThread->setUserAlias($draft->getPostUserAlias());

				$newPost = $frm->generateThread(
					$newThread,
					\ilRTE::_replaceMediaObjectImageSrc($form->getInput('message'), 0),
					$draft->getNotify(),
					$draft->getPostNotify(),
					$status
				);
			} else {
				$userAlias = \ilForumUtil::getPublicUserAlias($form->getInput('alias'), $this->objProperties->isAnonymized());
				$newThread = new \ilForumTopic(0, true, true);
				$newThread->setForumId($topicData['top_pk']);
				$newThread->setThrAuthorId($this->user->getId());
				$newThread->setDisplayUserId($userIdForDisplayPurposes);
				$newThread->setSubject($this->handleFormInput($form->getInput('subject'), false));
				$newThread->setUserAlias($userAlias);

				$newPost = $frm->generateThread(
					$newThread,
					\ilRTE::_replaceMediaObjectImageSrc($form->getInput('message'), 0),
					$form->getItemByPostVar('notify') ? (int)$form->getInput('notify') : 0,
					0, // #19980
					$status
				);
			}

			if ($this->objProperties->isFileUploadAllowed()) {
				$file = $_FILES['userfile'];
				if (is_array($file) && !empty($file)) {
					$fileData = new \ilFileDataForum($this->object->getId(), $newPost);
					$fileData->storeUploadedFile($file);
				}
			}

			$frm->setDbTable('frm_data');
			$frm->setMDB2WhereCondition('top_pk = %s ', array('integer'), array($topicData['top_pk']));
			$frm->updateVisits($topicData['top_pk']);

			if ($createFromDraft) {
				$mediaObjects = \ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());
			} else {
				$mediaObjects = \ilRTE::_getMediaObjects($form->getInput('message'), 0);
			}
			foreach ($mediaObjects as $mob) {
				if (\ilObjMediaObject::_exists($mob)) {
					\ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
					\ilObjMediaObject::_saveUsage($mob, 'frm:html', $newPost);
				}
			}

			if ($draft->getDraftId() > 0) {
				$draftHistory = new \ilForumDraftsHistory();
				$draftHistory->deleteHistoryByDraftIds([$draft->getDraftId()]);
				if ($this->objProperties->isFileUploadAllowed()) {
					$forumFileData = new \ilFileDataForum($this->object->getId(), $newPost);
					$draftFileData = new \ilFileDataForumDrafts($this->object->getId(), $draft->getDraftId());
					$draftFileData->moveFilesOfDraft($forumFileData->getForumPath(), $newPost);
				}
				$draft->deleteDraft();
			}

			$GLOBALS['ilAppEventHandler']->raise(
				'Modules/Forum', 'createdPost', [
					'ref_id'            => $this->object->getRefId(),
					'post'              => new \ilForumPost($newPost),
					'notify_moderators' => !$status
			]);

			\ilUtil::sendSuccess($this->lng->txt('forums_thread_new_entry'), true);
			$this->ctrl->redirect($this);
		}

		$form->setValuesByPost();
		if (!$this->objProperties->isAnonymized()) {
			$form->getItemByPostVar('alias')->setValue($this->user->getLogin());
		}

		$this->tpl->setContent($form->getHTML());
	}

	protected function publishThreadDraftObject()
	{
		if (!ilForumPostDraft::isSavePostDraftAllowed()) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		$draft = \ilForumPostDraft::newInstanceByDraftId($draftId);

		if ((int)$draft->getDraftId() <= 0) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->createThread($draft, true);
	}

	protected function addThreadObject()
	{
		$draft = new \ilForumPostDraft();
		if (\ilForumPostDraft::isSavePostDraftAllowed()) {
			$draftId = (int)($this->httpRequest->getParsedBody()['draft_id'] ?? 0);
			if ($draftId > 0) {
				$draft = \ilForumPostDraft::newInstanceByDraftId($draftId);
			}
		}

		$this->createThread($draft, false);
	}

	protected function enableForumNotificationObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->enableForumNotification($this->user->getId());

		if ((int)$this->objCurrentTopic->getId() > 0) {
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			\ilUtil::sendInfo($this->lng->txt('forums_forum_notification_enabled'), true);
			$this->ctrl->redirect($this, 'viewThread');
		}

		\ilUtil::sendInfo($this->lng->txt('forums_forum_notification_enabled'));
		$this->showThreadsObject();
	}

	protected function disableForumNotificationObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->disableForumNotification($this->user->getId());

		if ((int)$this->objCurrentTopic->getId() > 0) {
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
			\ilUtil::sendInfo($this->lng->txt('forums_forum_notification_disabled'), true);
			$this->ctrl->redirect($this, 'viewThread');
		}

		\ilUtil::sendInfo($this->lng->txt('forums_forum_notification_disabled'));
		$this->showThreadsObject();
	}

	/**
	 * @inheritdoc
	 */
	protected function setColumnSettings(ilColumnGUI $column_gui)
	{
		$column_gui->setBlockProperty('news', 'title', $this->lng->txt('frm_latest_postings'));
		$column_gui->setBlockProperty('news', 'prevent_aggregation', true);
		$column_gui->setRepositoryMode(true);
		
		if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
			$news_set = new \ilSetting('news');
			if ($news_set->get('enable_rss_for_internal')) {
				$column_gui->setBlockProperty('news', 'settings', true);
				$column_gui->setBlockProperty('news', 'public_notifications_option', true);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function addLocatorItems()
	{
		if ($this->object instanceof \ilObjForum) {
			$this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this), '', $this->object->getRefId());
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

	protected function infoScreen()
	{
		if(
			!$this->access->checkAccess('visible', '', $this->object->getRefId()) &&
			!$this->access->checkAccess('read', '', $this->object->getRefId())
		) {
			$this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
		}

		$info = new \ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		$this->ctrl->forwardCommand($info);
	}

	/**
	 * 
	 */
	protected function markPostUnreadObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if ((int)$this->objCurrentPost->getId() > 0) {
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

			$this->object->markPostUnread($this->user->getId(), (int)$this->objCurrentPost->getId());
		}
		$this->viewThreadObject();
	}

	/**
	 * 
	 */
	protected function markPostReadObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if ((int)$this->objCurrentTopic->getId() > 0 && (int)$this->objCurrentPost->getId() > 0) {
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

			$this->object->markPostRead(
				$this->user->getId(), (int)$this->objCurrentTopic->getId(), (int)$this->objCurrentPost->getId()
			);
		}
		$this->viewThreadObject();
	}

	/**
	 * @inheritdoc
	 */
	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
	{
		$lg = parent::initHeaderAction();

		if ((int)$this->objCurrentTopic->getId() > 0) {
			$container_obj = null; // Workaround: Do not show "desktop actions" in thread view
			$lg->setContainerObject($container_obj);
		}

		if (!($lg instanceof \ilObjForumListGUI) || !$this->settings->get('forum_notification')) {
			return $lg;
		}

		if ($this->user->isAnonymous() || !$this->access->checkAccess('read', '', $this->object->getRefId())) {
			return $lg;
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setMDB2Wherecondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));

		$isForumNotificationEnabled = $frm->isForumNotificationEnabled($this->user->getId());
		$userMayDisableNotifications = $this->isUserAllowedToDeactivateNotification();

		if ((int)$this->objCurrentTopic->getId() > 0) {
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentTopic->getId());
		}

		if ($this->isParentObjectCrsOrGrp()) {
			// special behaviour for CRS/GRP-Forum notification!!
			if ($isForumNotificationEnabled && $userMayDisableNotifications) {
				$lg->addCustomCommand(
					$this->ctrl->getLinkTarget($this, 'disableForumNotification'), 'forums_disable_forum_notification'
				);
			} elseif (!$isForumNotificationEnabled) {
				$lg->addCustomCommand(
					$this->ctrl->getLinkTarget($this, 'enableForumNotification'), 'forums_enable_forum_notification'
				);
			}
		} elseif ($isForumNotificationEnabled) {
			$lg->addCustomCommand(
				$this->ctrl->getLinkTarget($this, 'disableForumNotification'), 'forums_disable_forum_notification'
			);
		} else {
			$lg->addCustomCommand(
				$this->ctrl->getLinkTarget($this, 'enableForumNotification'), 'forums_enable_forum_notification'
			);
		}

		$isThreadNotificationEnabled = false;
		if ((int)$this->objCurrentTopic->getId() > 0) {
			$isThreadNotificationEnabled = $this->objCurrentTopic->isNotificationEnabled($this->user->getId());
			if ($isThreadNotificationEnabled) {
				$lg->addCustomCommand(
					$this->ctrl->getLinkTarget($this, 'toggleThreadNotification'), 'forums_disable_notification'
				);
			} else {
				$lg->addCustomCommand(
					$this->ctrl->getLinkTarget($this, 'toggleThreadNotification'), 'forums_enable_notification'
				);
			}
		}
		$this->ctrl->setParameter($this, 'thr_pk', '');

		if ($isForumNotificationEnabled || $isThreadNotificationEnabled) {
			$lg->addHeaderIcon(
				'not_icon',
				ilUtil::getImagePath('notification_on.svg'),
				$this->lng->txt('frm_notification_activated')
			);
		} else {
			$lg->addHeaderIcon(
				'not_icon',
				ilUtil::getImagePath('notification_off.svg'),
				$this->lng->txt('frm_notification_deactivated')
			);
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
		
		if($this->isParentObjectCrsOrGrp())
		{

			$frm_noti = new ilForumNotification((int) $_GET['ref_id']);
			$frm_noti->setUserId($this->user->getId());
			
			$user_toggle = (int)$frm_noti->isUserToggleNotification();
			if($user_toggle == 0 && $this->objProperties->isUserToggleNoti() == 0) 
			{	
				return true;
			}
		}
		
		return false;
	}

	public function isParentObjectCrsOrGrp(): bool
	{
		$grpRefId = $this->repositoryTree->checkForParentType($this->object->getRefId(), 'grp');
		$crsRefId = $this->repositoryTree->checkForParentType($this->object->getRefId(), 'crs');

		return ($grpRefId > 0 || $crsRefId > 0);
	}

	/**
	 * @inheritdoc
	 */
	public function addToDeskObject()
	{
		if (!(int)$this->settings->get('disable_my_offers')) {
			\ilDesktopItemGUI::addToDesktop();
			\ilUtil::sendSuccess($this->lng->txt('added_to_desktop'));
		}

		$this->showThreadsObject();
	}

	/**
	 * @inheritdoc
	 */
	public function removeFromDeskObject()
	{
		if (!(int)$this->settings->get('disable_my_offers')) {
			\ilDesktopItemGUI::removeFromDesktop();
			\ilUtil::sendSuccess($this->lng->txt('removed_from_desktop'));
		}

		$this->showThreadsObject();
	}

	protected function saveThreadSortingObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threadIdToSortValueMap = (array)($this->httpRequest->getParsedBody()['thread_sorting'] ?? []);

		array_walk($threadIdToSortValueMap, function($sortValue, $threadId) {
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), new \ilForumTopic($threadId));
		});

		foreach ($threadIdToSortValueMap as $threadId => $sortValue) {
			$sortValue = str_replace(',', '.', $sortValue);
			$sortValue =  (float)$sortValue * 100;
			$this->object->setThreadSorting((int)$threadId, $sortValue);
		}

		\ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
		$this->ctrl->redirect($this, 'showThreads');
	}

	/**
	 * 
	 */
	public function mergeThreadsObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threadIdToMerge = (int)($this->httpRequest->getQueryParams()['merge_thread_id'] ?? 0);
		if (!($threadIdToMerge > 0)) {
			$threadIds = array_values(
				array_filter(array_map('intval', (array)$this->httpRequest->getParsedBody()['thread_ids'] ?? []))
			);
			if (1 === count($threadIds)) {
				$threadIdToMerge = current($threadIds);
			} else {
				\ilUtil::sendInfo($this->lng->txt('select_one'));
				$this->showThreadsObject();
				return;
			}
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());

		$threadToMerge = new ilForumTopic($threadIdToMerge);

		if (\ilForum::_lookupObjIdForForumId($threadToMerge->getForumId()) != $frm->getForumId()) {
			\ilUtil::sendFailure($this->lng->txt('not_allowed_to_merge_into_another_forum'));
			$this->showThreadsObject();
			return;
		}

		$frm->setMDB2Wherecondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forums_threads_liste.html', 'Modules/Forum');

		$topicData = $frm->getOneTopic();
		if ($topicData) {
			$this->ctrl->setParameter($this, 'merge_thread_id', $threadIdToMerge);
			$tbl = new \ilForumTopicTableGUI(
				$this, 'mergeThreads', '', (int)$this->httpRequest->getQueryParams()['ref_id'],
				$topicData, $this->is_moderator, $this->settings->get('forum_overview')
			);
			$tbl->setSelectedThread($threadToMerge);
			$tbl->setMapper($frm)->fetchData();
			$tbl->init();
			$this->tpl->setVariable('THREADS_TABLE', $tbl->getHTML());
		} else {
			\ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showThreadsObject();
		}
	}

	/**
	 *
	 */
	public function confirmMergeThreadsObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$sourceThreadId = (int)($this->httpRequest->getQueryParams()['merge_thread_id'] ?? 0);
		$targetThreadIds = array_values(
			array_filter(array_map('intval', (array)$this->httpRequest->getParsedBody()['thread_ids'] ?? []))
		);

		if (!($sourceThreadId > 0) || 1 !== count($targetThreadIds)) {
			\ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->mergeThreadsObject();
			return;
		}

		$targetThreadId = current($targetThreadIds);
		if ($sourceThreadId == $targetThreadId) {
			\ilUtil::sendFailure($this->lng->txt('error_same_thread_ids'));
			$this->showThreadsObject();
			return;
		}

		if (ilForumTopic::lookupForumIdByTopicId($sourceThreadId) != ilForumTopic::lookupForumIdByTopicId($targetThreadId)) {
			\ilUtil::sendFailure($this->lng->txt('not_allowed_to_merge_into_another_forum'));
			$this->ctrl->clearParameters($this);
			$this->showThreadsObject();
			return;
		}

		if (\ilForumTopic::_lookupDate($sourceThreadId) < ilForumTopic::_lookupDate($targetThreadId)) {
			\ilUtil::sendInfo($this->lng->txt('switch_threads_for_merge'));
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), new \ilForumTopic((int)$sourceThreadId));
		$this->ensureThreadBelongsToForum((int)$this->object->getId(), new \ilForumTopic((int)$targetThreadId));

		$c_gui = new ilConfirmationGUI();

		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performMergeThreads'));
		$c_gui->setHeaderText($this->lng->txt('frm_sure_merge_threads'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showThreads');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performMergeThreads');

		$c_gui->addItem(
			'thread_ids[]', $sourceThreadId,
			sprintf($this->lng->txt('frm_merge_src'), ilForumTopic::_lookupTitle($sourceThreadId))
		);
		$c_gui->addItem(
			'thread_ids[]', $targetThreadId,
			sprintf($this->lng->txt('frm_merge_target'), ilForumTopic::_lookupTitle($targetThreadId))
		);
		$this->tpl->setContent($c_gui->getHTML());
	}

	/**
	 * 
	 */
	public function performMergeThreadsObject()
	{
		if (!$this->is_moderator) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$threadIds = array_values(
			array_filter(array_map('intval', (array)$this->httpRequest->getParsedBody()['thread_ids'] ?? []))
		);
		if (2 !== count($threadIds)) {
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showThreadsObject();
			return;
		}

		if ((int)$threadIds[0] === (int)$threadIds[1]) {
			ilUtil::sendFailure($this->lng->txt('error_same_thread_ids'));
			$this->showThreadsObject();
			return;
		}

		try {
			$frm = new \ilForum();
			$frm->setForumId($this->object->getId());
			$frm->setForumRefId($this->object->getRefId());

			$this->ensureThreadBelongsToForum((int)$this->object->getId(), new \ilForumTopic((int)$threadIds[0]));
			$this->ensureThreadBelongsToForum((int)$this->object->getId(), new \ilForumTopic((int)$threadIds[1]));

			$frm->mergeThreads((int)$threadIds[0], (int)$threadIds[1]);
			\ilUtil::sendSuccess($this->lng->txt('merged_threads_successfully'));
		} catch (\ilException $e) {
			\ilUtil::sendFailure($this->lng->txt($e->getMessage()));
		}

		$this->showThreadsObject();
	}

	/**
	 * 
	 */
	protected function setSideBlocks()
	{
		$content = $this->getRightColumnHTML();
		if (!$this->ctrl->isAsynch()) {
			$content = implode('', [
				\ilRepositoryObjectSearchGUI::getSearchBlockHTML($this->lng->txt('frm_search')),
				$content,
			]);
		}
		$this->tpl->setRightContent($content);
	}

	/**
	 *
	 */
	protected function deliverDraftZipFileObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$draftId = $this->httpRequest->getQueryParams()['draft_id'] ?? 0;
		$draft = \ilForumPostDraft::newInstanceByDraftId((int)$draftId);
		if ($draft->getPostAuthorId() == $this->user->getId()) {
			$fileData = new \ilFileDataForumDrafts(0, $draft->getDraftId());
			if (!$fileData->deliverZipFile()) {
				$this->ctrl->redirect($this);
			}
		}
	}

	/**
	 * 
	 */
	protected function deliverZipFileObject()
	{
		if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->ensureThreadBelongsToForum((int)$this->object->getId(), $this->objCurrentPost->getThread());

		$fileData = new \ilFileDataForum($this->object->getId(), $this->objCurrentPost->getId());
		if (!$fileData->deliverZipFile()) {
			$this->ctrl->redirect($this);
		}
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function editThreadDraftObject(\ilPropertyFormGUI $form = null)
	{
		if (
			!$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
			!$this->access->checkAccess('read', '', $this->object->getRefId()) ||
			!\ilForumPostDraft::isSavePostDraftAllowed()
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());

		$draft = new \ilForumPostDraft();
		$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		if ($draftId > 0) {
			$draft = $draft->newInstanceByDraftId($draftId);
		}

		$historyCheck = (int)($this->httpRequest->getQueryParams()['hist_check'] ?? 1);
		if (!($form instanceof \ilPropertyFormGUI) && $historyCheck  > 0) {
			$this->doHistoryCheck($draft->getDraftId());
		}

		if (!$form instanceof \ilPropertyFormGUI) {
			$form = $this->buildThreadForm(true);
			$form->setValuesByArray([ 
				'alias' => $draft->getPostUserAlias(),
				'subject' => $draft->getPostSubject(),
				'message' => \ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($draft->getPostMessage(), 2), 1),
				'notify' => $draft->getNotify() ? true : false,
				'userfile' => '',
				'del_file' =>  []
			]);
		} else {
			$this->ctrl->setParameter($this, 'draft_id', $draftId);
		}

		$this->tpl->setContent($form->getHTML() . $this->modal_history);
	}

	protected function restoreFromHistoryObject()
	{
		$historyId = (int)($this->httpRequest->getQueryParams()['history_id'] ?? 0);
		$history = new \ilForumDraftsHistory($historyId);

		$draft = $history->rollbackAutosave();
		if ($draft->getThreadId() == 0 && $draft->getPostId() == 0) {
			$this->ctrl->setParameter($this, 'draft_id', $history->getDraftId());
			$this->ctrl->redirect($this, 'editThreadDraft');
		}

		$this->ctrl->clearParameters($this);
		$this->ctrl->setParameter($this, 'pos_pk', $draft->getPostId());
		$this->ctrl->setParameter($this, 'thr_pk', $draft->getThreadId());
		$this->ctrl->setParameter($this, 'draft_id',$draft->getDraftId());
		$this->ctrl->setParameter($this, 'action', 'editdraft');

		// create draft backup before redirect!
		\ilForumPostDraft::createDraftBackup((int)$draft->getDraftId());

		$this->ctrl->redirect($this, 'viewThread');
	}
	
	protected function saveThreadAsDraftObject()
	{
		if (
			!$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
			!$this->access->checkAccess('read', '', $this->object->getRefId()) ||
			!\ilForumPostDraft::isSavePostDraftAllowed()
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$autoSavedDraftId = (int)($this->httpRequest->getParsedBody()['draft_id'] ?? 0);
		if ($autoSavedDraftId <= 0) {
			$autoSavedDraftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		}

		$frm = $this->object->Forum;
		$frm->setForumId($this->object->getId());
		$frm->setForumRefId($this->object->getRefId());
		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		$topicData = $frm->getOneTopic();

		$form = $this->buildThreadForm();
		if ($form->checkInput()) {
			$this->doCaptchaCheck();

			if (0 === $autoSavedDraftId) {
				$draft = new \ilForumPostDraft();
			} else {
				$draft = \ilForumPostDraft::newInstanceByDraftId($autoSavedDraftId);
			}

			$draft->setForumId($topicData['top_pk']);
			$draft->setThreadId(0);
			$draft->setPostId(0);
			$draft->setPostSubject($this->handleFormInput($form->getInput('subject'), false));
			$draft->setPostMessage(\ilRTE::_replaceMediaObjectImageSrc($form->getInput('message'), 0));
			$userAlias = \ilForumUtil::getPublicUserAlias($form->getInput('alias'), $this->objProperties->isAnonymized());
			$draft->setPostUserAlias($userAlias);
			$draft->setNotify((int)$form->getInput('notify'));
			$draft->setPostAuthorId($this->user->getId());
			$draft->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));

			if (0 === $autoSavedDraftId) {
				$draftId = $draft->saveDraft();
			} else {
				$draft->updateDraft();
				$draftId = $draft->getDraftId();
			}

			$GLOBALS['ilAppEventHandler']->raise(
				'Modules/Forum', 'savedAsDraft', [
				'draftObj' => $draft,
				'obj_id' => $this->object->getId(),
				'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed(),
			]);

			\ilForumUtil::moveMediaObjects($form->getInput('message'), 'frm~d:html', $draftId, 'frm~d:html', $draftId);

			if ($this->objProperties->isFileUploadAllowed()) {
				$draftFileData = new \ilFileDataForumDrafts($this->object->getId(), $draftId);
				$file = $_FILES['userfile'];
				if (is_array($file) && !empty($file)) {
					$draftFileData->storeUploadedFile($file);
				}

				$files2delete = $form->getInput('del_file');
				if(is_array($files2delete) && count($files2delete) > 0) {
					$draftFileData->unlinkFilesByMD5Filenames($files2delete);
				}
			}

			\ilUtil::sendSuccess($this->lng->txt('save_draft_successfully'), true);
			$this->ctrl->clearParameters($this);
			$this->ctrl->redirect($this, 'showThreads');
		}

		$_GET['action'] = substr($_GET['action'], 6); // @nmatuschek: Why this, I don't get it???
		$form->setValuesByPost();
		$this->ctrl->setParameter($this, 'draft_id', $autoSavedDraftId);
		$this->tpl->setContent($form->getHTML());
	}

	protected function updateThreadDraftObject()
	{
		if (
			!$this->access->checkAccess('add_thread', '', $this->object->getRefId()) ||
			!$this->access->checkAccess('read', '', $this->object->getRefId()) ||
			!\ilForumPostDraft::isSavePostDraftAllowed()
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		if ($draftId <= 0) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$form = $this->buildThreadForm(true);
		if ($form->checkInput()) {
			$this->doCaptchaCheck();

			$userAlias = \ilForumUtil::getPublicUserAlias($form->getInput('alias'), $this->objProperties->isAnonymized());

			$draft = \ilForumPostDraft::newInstanceByDraftId($draftId);
			$draft->setPostSubject($this->handleFormInput($form->getInput('subject'), false));
			$draft->setPostMessage(\ilRTE::_replaceMediaObjectImageSrc($form->getInput('message'), 0));
			$draft->setPostUserAlias($userAlias);
			$draft->setNotify((int)$form->getInput('notify'));
			$draft->setPostAuthorId($this->user->getId());
			$draft->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));
			$draft->updateDraft();

			$GLOBALS['ilAppEventHandler']->raise(
				'Modules/Forum', 'updatedDraft', [
					'draftObj' => $draft,
					'obj_id' => $this->object->getId(),
					'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed(),
			]);

			\ilForumUtil::moveMediaObjects(
				$form->getInput('message'), 'frm~d:html', $draft->getDraftId(), 'frm~d:html', $draft->getDraftId()
			);

			if ($this->objProperties->isFileUploadAllowed()) {
				$draftFileData = new \ilFileDataForumDrafts($this->object->getId(), $draft->getDraftId());
				$file = $_FILES['userfile'];
				if (is_array($file) && !empty($file)) {
					$draftFileData->storeUploadedFile($file);
				}

				$files2delete = $form->getInput('del_file');
				if (is_array($files2delete) && count($files2delete) > 0) {
					$draftFileData->unlinkFilesByMD5Filenames($files2delete);
				}
			}

			\ilUtil::sendSuccess($this->lng->txt('save_draft_successfully'), true);
			$this->ctrl->clearParameters($this);
			$this->ctrl->redirect($this, 'showThreads');
		}

		$form->setValuesByPost();
		$this->ctrl->setParameter($this, 'hist_check', 0);
		$this->ctrl->setParameter($this, 'draft_id',  $draftId);
		$this->editThreadDraftObject($form);
	}
	
	public function saveAsDraftObject()
	{
		if (!$this->objCurrentTopic->getId()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_deleted'), true);
			$this->ctrl->redirect($this);
		}

		if ($this->objCurrentTopic->isClosed()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_closed'), true);
			$this->ctrl->redirect($this);
		}

		if(!isset($_POST['del_file']) || !is_array($_POST['del_file'])) $_POST['del_file'] = array();

		$autosave_draft_id = 0;
		if(ilForumPostDraft::isAutoSavePostDraftAllowed() && isset($_POST['draft_id']))
		{
			$autosave_draft_id = (int)$_POST['draft_id'];
		}	
		$oReplyEditForm = $this->getReplyEditForm();
		if($oReplyEditForm->checkInput())
		{
			if (!$this->objCurrentPost->getId()) {
				$_GET['action'] = '';
				\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_parent_deleted'));
				$this->viewThreadObject();
				return;
			}

			$this->doCaptchaCheck();

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
				if(!$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id']))
				{
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}
				
				$user_alias = ilForumUtil::getPublicUserAlias($oReplyEditForm->getInput('alias'), $this->objProperties->isAnonymized());
				
				if($autosave_draft_id == 0)
				{
					$draftObj = new ilForumPostDraft();
				}
				else
				{
					$draftObj = ilForumPostDraft::newInstanceByDraftId($autosave_draft_id);
				}
					$draftObj->setForumId($topicData['top_pk']);
					$draftObj->setThreadId($this->objCurrentTopic->getId());
					$draftObj->setPostId($this->objCurrentPost->getId());
					
					$draftObj->setPostSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
					$draftObj->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message'), 0));
					$draftObj->setPostUserAlias($user_alias);
					$draftObj->setNotify((int)$oReplyEditForm->getInput('notify'));
					$draftObj->setPostNotify((int)$oReplyEditForm->getInput('notify_post'));
				
					$draftObj->setPostAuthorId($this->user->getId());
					$draftObj->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));
				
				if($autosave_draft_id == 0)
				{
					$draft_id = $draftObj->saveDraft();
				}
				else
				{
					$draftObj->updateDraft();
					$draft_id = $draftObj->getDraftId();
				}
					
					
					if(ilForumPostDraft::isSavePostDraftAllowed())
					{
						$GLOBALS['ilAppEventHandler']->raise(
							'Modules/Forum',
							'savedAsDraft',
							array('draftObj'               => $draftObj,
							      'obj_id'                 => $this->object->getId(),
							      'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed())
						);
					}
				
				if($this->objProperties->isFileUploadAllowed())
				{
					$file = $_FILES['userfile'];
					if(is_array($file) && !empty($file))
					{
						$oFDForumDrafts = new ilFileDataForumDrafts($this->object->getId(), $draftObj->getDraftId());
						$oFDForumDrafts->storeUploadedFile($file);
					}
				}		

				// copy temporary media objects (frm~)
				ilForumUtil::moveMediaObjects($oReplyEditForm->getInput('message'),'frm~d:html', $draft_id, 'frm~d:html', $draft_id);
				
				$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'][] = (int)$this->objCurrentPost->getId();

				ilUtil::sendSuccess($this->lng->txt('save_draft_successfully'), true);
				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
				$this->ctrl->redirect($this, 'viewThread');
			}
		}
		else
		{
			$oReplyEditForm->setValuesByPost();
			$_GET['action'] = substr($_GET['action'], 6);
		}
		return $this->viewThreadObject();
	}

	protected function editDraftObject()
	{
		if (\ilForumPostDraft::isAutoSavePostDraftAllowed()) {
			$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
			if ($this->checkDraftAccess($draftId)) {
				$this->doHistoryCheck($draftId);
			}
		}

		$this->viewThreadObject();
	}

	/**
	 * 
	 */
	public function updateDraftObject()
	{
		if (!$this->objCurrentTopic->getId()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_deleted'), true);
			$this->ctrl->redirect($this);
		}

		if ($this->objCurrentTopic->isClosed()) {
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_thr_closed'), true);
			$this->ctrl->redirect($this);
		}

		if (!$this->objCurrentPost->getId()) {
			$_GET['action'] = '';
			\ilUtil::sendFailure($this->lng->txt('frm_action_not_possible_parent_deleted'));
			$this->viewThreadObject();
			return;
		}

		if(!isset($_POST['del_file']) || !is_array($_POST['del_file'])) $_POST['del_file'] = array();

		$oReplyEditForm = $this->getReplyEditForm();
		if($oReplyEditForm->checkInput())
		{
			$this->doCaptchaCheck();

			// init objects
			$oForumObjects = $this->getForumObjects();
			/**
			 * @var $forumObj ilObjForum
			 */
			$forumObj = $oForumObjects['forumObj'];
		
				if( !$this->user->isAnonymous() &&
				($_GET['action'] == 'showdraft' || $_GET['action'] == 'editdraft'))
			{
				if(!$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id']))
				{
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}

				$user_alias = ilForumUtil::getPublicUserAlias($oReplyEditForm->getInput('alias'), $this->objProperties->isAnonymized());	

				// generateDraft
				$update_draft = new ilForumPostDraft($this->user->getId(),$this->objCurrentPost->getId(), (int)$_GET['draft_id']);

				$update_draft->setPostSubject($this->handleFormInput($oReplyEditForm->getInput('subject'), false));
				$update_draft->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($oReplyEditForm->getInput('message'), 0));
				$update_draft->setPostUserAlias($user_alias);
				$update_draft->setNotify((int)$oReplyEditForm->getInput('notify'));
				$update_draft->setUpdateUserId($this->user->getId());
				$update_draft->setPostAuthorId($this->user->getId());
				$update_draft->setPostDisplayUserId(($this->objProperties->isAnonymized() ? 0 : $this->user->getId()));
				
				$update_draft->updateDraft();
				
				if(ilForumPostDraft::isSavePostDraftAllowed())
				{
					$GLOBALS['ilAppEventHandler']->raise(
						'Modules/Forum',
						'updatedDraft',
						array('draftObj' => $update_draft,
						      'obj_id' => $this->object->getId(),
						      'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed())
					);
				}
				
				$uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->user->getId());
				
				foreach($uploadedObjects as $mob)
				{
					ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->user->getId());
					ilObjMediaObject::_saveUsage($mob,'frm~d:html', $update_draft->getDraftId());
				}
				ilForumUtil::saveMediaObjects($oReplyEditForm->getInput('message'), 'frm~d:html', $update_draft->getDraftId());
				
				if($this->objProperties->isFileUploadAllowed())
				{
					$oFDForumDrafts = new ilFileDataForumDrafts($forumObj->getId(), $update_draft->getDraftId());
					$file     = $_FILES['userfile'];
					if(is_array($file) && !empty($file))
					{
						$oFDForumDrafts->storeUploadedFile($file);
					}
				}

				$file2delete = $oReplyEditForm->getInput('del_file');
				if(is_array($file2delete) && count($file2delete))
				{
					$oFDForumDrafts->unlinkFilesByMD5Filenames($file2delete);
				}

				$_SESSION['frm'][(int)$_GET['thr_pk']]['openTreeNodes'][] = (int)$this->objCurrentPost->getId();
				ilUtil::sendSuccess($this->lng->txt('save_draft_successfully'), true);
			}
			$this->ctrl->clearParameters($this);
			$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
			$this->ctrl->setParameter($this, 'draft_id', $update_draft->getDraftId());
		}
		else
		{
			$this->ctrl->clearParameters($this);
			$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
			$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
			$this->ctrl->setParameter($this, 'draft_id',(int)$_GET['draft_id']);
			$this->ctrl->setParameter($this, 'action', 'editdraft');
			$oReplyEditForm->setValuesByPost();
			return $this->viewThreadObject();
		}
		$this->ctrl->clearParameters($this);
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->redirect($this, 'viewThread');
	}
	
	/**
	 * todo: move to ilForumUtil
	 * @param $draft_id
	 * @param $message
	 */
	protected function deleteMobsOfDraft($draft_id, $message)
	{
		// remove usage of deleted media objects
		$oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~d:html', $draft_id);
		$curMediaObjects = ilRTE::_getMediaObjects($message, 0);
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
					ilObjMediaObject::_removeUsage($oldMob,'frm~d:html', $draft_id);
					$mob_obj = new ilObjMediaObject($oldMob);
					$mob_obj->delete();
				}
			}
		}
	}

	/**
	 * @param ilForumPostDraft|null $draft_obj
	 */
	protected function deleteSelectedDraft(ilForumPostDraft $draft_obj = null)
	{
		if(
			!$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id']) ||
			$this->user->isAnonymous() ||
			($draft_obj instanceof ilForumPostDraft && $this->user->getId() != $draft_obj->getPostAuthorId()))
		{
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$post_id  = $this->objCurrentPost->getId();
		if(!($draft_obj instanceof ilForumPostDraft))
		{
			$draft_id_to_delete = (int)$_GET['draft_id'];
			$draft_obj          = new ilForumPostDraft($this->user->getId(), $post_id, $draft_id_to_delete);
			
			if(!$draft_obj->getDraftId() || ($draft_obj->getDraftId() != $draft_id_to_delete))
			{
				$this->ctrl->clearParameters($this);
				$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
				$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
				$this->ctrl->redirect($this, 'viewThread');
			}
		}

		$this->deleteMobsOfDraft($draft_obj->getDraftId(), $draft_obj->getPostMessage());

		$objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_obj->getDraftId());
		$objFileDataForumDrafts->delete();
	
		if(ilForumPostDraft::isSavePostDraftAllowed())
		{
			$GLOBALS['ilAppEventHandler']->raise(
				'Modules/Forum',
				'deletedDraft',
				array('draftObj' => $draft_obj,
				      'obj_id' => $this->object->getId(),
				      'is_file_upload_allowed' => $this->objProperties->isFileUploadAllowed())
			);
		}
		$draft_obj->deleteDraft();

		ilUtil::sendSuccess($this->lng->txt('delete_draft_successfully'), true);	
		$this->ctrl->clearParameters($this);
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->redirect($this, 'viewThread');
	}

	protected function autosaveDraftAsyncObject()
	{
		$requestedAction = (string)($this->httpRequest->getQueryParams()['action'] ?? '');
		$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		if (
			$requestedAction !== 'ready_showreply' &&
			$this->access->checkAccess('read', '', $this->object->getRefId()) &&
			$this->access->checkAccess('add_reply', '', $this->object->getRefId())
		) {
			$action = new \ilForumAutoSaveAsyncDraftAction(
				$this->user,
				$this->getReplyEditForm(),
				$this->objProperties,
				$this->objCurrentTopic,
				$this->objCurrentPost,
				function(string $message): string {
					return $this->handleFormInput($message);
				},
				$draftId,
				(int)\ilObjForum::lookupForumIdByRefId($this->ref_id),
				\ilUtil::stripSlashes($requestedAction)
			);

			echo json_encode($action->executeAndGetResponseObject());
		}

		exit();
	}
	
	protected function autosaveThreadDraftAsyncObject()
	{
		$requestedAction = (string)($this->httpRequest->getQueryParams()['action'] ?? '');
		$draftId = (int)($this->httpRequest->getQueryParams()['draft_id'] ?? 0);
		if (
			$requestedAction !== 'ready_showreply' &&
			$this->access->checkAccess('read', '', $this->object->getRefId()) &&
			$this->access->checkAccess('add_thread', '', $this->object->getRefId())
		) {
			$action = new \ilForumAutoSaveAsyncDraftAction(
				$this->user,
				$this->buildThreadForm(),
				$this->objProperties,
				$this->objCurrentTopic,
				$this->objCurrentPost,
				function(string $message): string {
					return $this->handleFormInput($message, false);
				},
				$draftId,
				(int)\ilObjForum::lookupForumIdByRefId($this->ref_id),
				\ilUtil::stripSlashes($requestedAction)
			);

			echo json_encode($action->executeAndGetResponseObject());
		}

		exit();
	}

	/**
	 * @param string $action
	 * @param bool $is_post
	 * @param ilForumPost $node
	 * @param int $Start
	 * @param ilForumPostDraft|NULL $draft
	 * @throws ilSplitButtonException
	 */
	private function renderSplitButton(string $action, $is_post = true, ilForumPost $node, $Start = 0, ilForumPostDraft $draft = NULL)
	{
		$actions = array();
		if($is_post)
		{
			if ($this->objCurrentPost->getId() != $node->getId() || (
				!in_array($action, ['showreply', 'showedit', 'censor', 'delete']) && !$this->displayConfirmPostActivation()
			)) {
				if($this->is_moderator || $node->isActivated() || $node->isOwner($this->user->getId()))
				{
					// button: reply
					if(!$this->objCurrentTopic->isClosed() && $node->isActivated() &&
						$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id']) &&
						!$node->isCensored()
					)
					{
						$this->ctrl->setParameter($this, 'action', 'showreply');
						$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
						$this->ctrl->setParameter($this, 'offset', $Start);
						$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
						$this->ctrl->setParameter($this, 'thr_pk', $node->getThreadId());
						
						if(!isset($draftsObjects[$node->getId()]))
						{
							$actions['reply_to_postings'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());
						}
						
						$this->ctrl->clearParameters($this);
					}
					
					// button: edit article
					if(!$this->objCurrentTopic->isClosed() && 
						($node->isOwner($this->user->getId()) || $this->is_moderator) &&
						!$node->isCensored() &&
						$this->user->getId() != ANONYMOUS_USER_ID
					)
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
					if($this->user->getId() != ANONYMOUS_USER_ID && !$node->isPostRead())
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
					if($this->user->getId() != ANONYMOUS_USER_ID &&
						$node->isPostRead()
					)
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
					if(!$node->isCensored())
					{
						$this->ctrl->setParameterByClass('ilforumexportgui', 'print_post', $node->getId());
						$this->ctrl->setParameterByClass('ilforumexportgui', 'top_pk', $node->getForumId());
						$this->ctrl->setParameterByClass('ilforumexportgui', 'thr_pk', $node->getThreadId());
						
						$actions['print'] = $this->ctrl->getLinkTargetByClass('ilforumexportgui', 'printPost');
						
						$this->ctrl->clearParameters($this);
					}
					
					# buttons for every post except the "active"
					if(!$this->objCurrentTopic->isClosed() &&
						($this->is_moderator ||
							($node->isOwner($this->user->getId()) && !$node->hasReplies())) &&
						$this->user->getId() != ANONYMOUS_USER_ID
					)
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
					
					if(!$this->objCurrentTopic->isClosed() && $this->is_moderator)
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
						
						if(!$node->isActivated())
						{
							$actions['activate_post'] = $this->ctrl->getLinkTarget($this, 'askForPostActivation', $node->getId());
						}
						
						$this->ctrl->clearParameters($this);
					}
				}
			} 
		}
		else
		{
			if(!isset($draft))
			{
				$draftsObjects = ilForumPostDraft::getInstancesByUserIdAndThreadId($this->user->getId(), $this->objCurrentTopic->getId());
				$draft         = $draftsObjects[$node->getId()];
			}
			// get actions for drafts
			$this->ctrl->setParameter($this, 'action',  'publishdraft');
			$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
			$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
			$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
			$this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
			$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
			$actions['publish'] = $this->ctrl->getLinkTarget($this, 'publishSelectedDraft', $node->getId());
			$this->ctrl->clearParameters($this);

			$this->ctrl->setParameter($this, 'action',  'editdraft');
			$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
			$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
			$this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
			$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
			$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
			$actions['edit'] = $this->ctrl->getLinkTarget($this, 'editDraft', 'draft_edit_' . $draft->getDraftId());
			$this->ctrl->clearParameters($this);

			$this->ctrl->setParameter($this, 'action',  'deletedraft');
			$this->ctrl->setParameter($this, 'pos_pk', $node->getId());
			$this->ctrl->setParameter($this, 'thr_pk',  $this->objCurrentTopic->getId());
			$this->ctrl->setParameter($this, 'draft_id', $draft->getDraftId());
			$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
			$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
			$actions['delete'] = $this->ctrl->getLinkTarget($this, 'viewThread', $node->getId());
			$this->ctrl->clearParameters($this);
			
			if(isset($_GET['draft_id']) && $action === 'editdraft')
			{
				$actions = array();
			}
		}

		$this->tpl->setCurrentBlock('posts_row');
		if(count($actions) > 0)
		{
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
					++$i;
				}
				else
				{
					$sb_item = ilLinkButton::getInstance();
					$sb_item->setCaption($lng_id);
					$sb_item->setUrl($url);
					
					$action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($sb_item));
				}
			}
			
			if ($is_post) {
				$this->tpl->setVariable('COMMANDS', $action_button->render());
			} elseif (!in_array($action, ['deletedraft', 'editdraft']) && !$this->objCurrentTopic->isClosed()) {
				$this->tpl->setVariable('COMMANDS', $action_button->render());
			}
		}
	}
	
	/**
	 * @param int $draftId
	 * @return bool
	 */
	public function checkDraftAccess(int $draftId): bool 
	{
		$draft = \ilForumPostDraft::newInstanceByDraftId($draftId);
		if(
			!$this->access->checkAccess('add_reply', '', $this->object->getRefId()) || $this->user->isAnonymous() ||
			($draft instanceof \ilForumPostDraft && $this->user->getId() != $draft->getPostAuthorId())
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		return true;
	}
	
	/**
	 * @param $draftId
	 */
	private function doHistoryCheck($draftId)
	{
		if (!\ilForumPostDraft::isAutoSavePostDraftAllowed()) {
			return;
		}

		\iljQueryUtil::initjQuery();
		$draftsFromHistory = \ilForumDraftsHistory::getInstancesByDraftId($draftId);
		if (is_array($draftsFromHistory) && sizeof($draftsFromHistory) > 0) {
			$modal = \ilModalGUI::getInstance();
			$modal->setHeading($this->lng->txt('restore_draft_from_autosave'));
			$modal->setId('frm_autosave_restore');
			$form_tpl = new \ilTemplate('tpl.restore_thread_draft.html', true, true, 'Modules/Forum');

			foreach ($draftsFromHistory as $key => $history_instance) {
				$accordion = new ilAccordionGUI();
				$accordion->setId('acc_'.$history_instance->getHistoryId());

				$form_tpl->setCurrentBlock('list_item');
				$message = \ilRTE::_replaceMediaObjectImageSrc($history_instance->getPostMessage(), 1);

				$history_date = ilDatePresentation::formatDate(new ilDateTime($history_instance->getDraftDate(), IL_CAL_DATETIME));
				$this->ctrl->setParameter($this, 'history_id', $history_instance->getHistoryId());
				$header = $history_date . ' - ' . $history_instance->getPostSubject();
				$accordion->addItem($header, $message . $this->uiRenderer->render(
					$this->uiFactory->button()->standard(
						$this->lng->txt('restore'),
						$this->ctrl->getLinkTarget($this, 'restoreFromHistory')
					)
				));

				$form_tpl->setVariable('ACC_AUTO_SAVE', $accordion->getHtml());
				$form_tpl->parseCurrentBlock();
			}

			$form_tpl->setVariable('RESTORE_DATA_EXISTS', 'found_threat_history_to_restore');
			$modal->setBody($form_tpl->get());
			$modal->initJS();
			$this->modal_history = $modal->getHTML();
		} else {
			ilForumPostDraft::createDraftBackup($draftId);
		}
	}

	/**
	 * Performs a CAPTCHA check for anonymous users if the CAPTCHA should be used for forums in the public area
	 */
	protected function doCaptchaCheck()
	{
		if ($this->user->isAnonymous() && !$this->user->isCaptchaVerified() && \ilCaptchaUtil::isActiveForForum()) {
			$this->user->setCaptchaVerified(true);
		}
	}

	/**
	 * @param ilForum $frm
	 * @param ilForumPost $node
	 * @param string $action
	 * @throws ilTemplateException
	 */
	private function renderPostingForm(ilForum $frm, ilForumPost $node, string $action)
	{
		if (
			$action == 'showedit' && (
				(!$this->is_moderator && !$node->isOwner($this->user->getId()) || $this->user->isAnonymous()) || $node->isCensored()
			)
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->getMessage());
		} elseif ($action == 'showreply' && !$this->access->checkAccess('add_reply', '', (int)$_GET['ref_id'])) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->getMessage());
		}

		$this->tpl->setVariable('REPLY_ANKER', $this->objCurrentPost->getId());
		$oEditReplyForm = $this->getReplyEditForm();
		if ($action !== 'editdraft') {
			switch ($this->objProperties->getSubjectSetting()) {
				case 'add_re_to_subject':
					$subject = $this->getModifiedReOnSubject(true);
					break;

				case 'preset_subject':
					$subject = $this->objCurrentPost->getSubject();
					break;

				case 'empty_subject':
				default:
					$subject = null;
					break;
			}
		}

		switch ($action) {
			case 'showreply':
				if ($this->ctrl->getCmd() == 'savePost' || $this->ctrl->getCmd() == 'saveAsDraft') {
					$oEditReplyForm->setValuesByPost();
				} else {
					if ($this->ctrl->getCmd() == 'quotePost') {
						$authorinfo = new ilForumAuthorInformation(
							$node->getPosAuthorId(),
							$node->getDisplayUserId(),
							$node->getUserAlias(),
							$node->getImportName()
						);

						$oEditReplyForm->setValuesByPost();
						$oEditReplyForm->getItemByPostVar('message')->setValue(
							ilRTE::_replaceMediaObjectImageSrc(
								$frm->prepareText($node->getMessage(), 1,
									$authorinfo->getAuthorName()) . "\n" . $oEditReplyForm->getInput('message'), 1
							)
						);
					} else {
						$oEditReplyForm->setValuesByArray(array(
							'alias' => '',
							'subject' => $subject,
							'message' => '',
							'notify' => 0,
							'userfile' => '',
							'del_file' => array()
						));
					}
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
				if ($this->ctrl->getCmd() == 'savePost') {
					$oEditReplyForm->setValuesByPost();
				} else {
					$oEditReplyForm->setValuesByArray(array(
						'alias' => '',
						'subject' => $this->objCurrentPost->getSubject(),
						'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($this->objCurrentPost->getMessage(),
							2), 1),
						'notify' => $this->objCurrentPost->isNotificationEnabled() ? true : false,
						'userfile' => '',
						'del_file' => array()
					));
				}
				break;

			case 'editdraft':
				if (in_array($this->ctrl->getCmd(), array('saveDraft', 'updateDraft', 'publishDraft'))) {
					$oEditReplyForm->setValuesByPost();
				} else {

					if (isset($_GET['draft_id']) && (int)$_GET['draft_id'] > 0) {
						/**
						 * @var object $draftObjects ilForumPost
						 */
						$draftObject = new ilForumPostDraft($this->user->getId(), $this->objCurrentPost->getId(),
							(int)$_GET['draft_id']);
						$oEditReplyForm->setValuesByArray(array(
							'alias' => $draftObject->getPostUserAlias(),
							'subject' => $draftObject->getPostSubject(),
							'message' => ilRTE::_replaceMediaObjectImageSrc($frm->prepareText($draftObject->getPostMessage(),
								2), 1),
							'notify' => $draftObject->getNotify() ? true : false,
							'userfile' => '',
							'del_file' => array()
						));
					}
				}
				break;
		}
		$this->ctrl->setParameter($this, 'pos_pk', $this->objCurrentPost->getId());
		$this->ctrl->setParameter($this, 'thr_pk', $this->objCurrentPost->getThreadId());
		$this->ctrl->setParameter($this, 'offset', (int)$_GET['offset']);
		$this->ctrl->setParameter($this, 'orderby', $_GET['orderby']);
		$this->ctrl->setParameter($this, 'action', $_GET['action']);
		if ($action !== 'editdraft') {
			$this->tpl->setVariable('FORM', $oEditReplyForm->getHTML());
		}
		$this->ctrl->clearParameters($this);
	}
}
