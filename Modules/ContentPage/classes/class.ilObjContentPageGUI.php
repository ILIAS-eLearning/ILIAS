<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjContentPageGUI
 * @ilCtrl_isCalledBy ilObjContentPageGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjContentPageGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilExportGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilContentPagePageGUI
 * @ilCtrl_Calls ilObjContentPageGUI: ilObjectCustomIconConfigurationGUI
 */
class ilObjContentPageGUI extends \ilObject2GUI implements \ilContentPageObjectConstants, \ilDesktopItemHandling
{
	/**
	 * @var \Psr\Http\Message\ServerRequestInterface
	 */
	protected $request;

	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var \ilAccessHandler
	 */
	protected $access;

	/**
	 * @var \ilSetting
	 */
	protected $settings;

	/**
	 * @var \ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var \ilObjUser
	 */
	protected $user;

	/**
	 * @var \ilNavigationHistory
	 */
	protected $navHistory;
	
	/**
	 * @var \ilErrorHandling
	 */
	protected $error;

	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;

	/**
	 * @var bool|string 
	 */
	protected $infoScreenEnabled = false;

	/**
	 * @inheritdoc
	 */
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $DIC;

		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$this->dic        = $DIC;
		$this->request    = $this->dic->http()->request();
		$this->settings   = $this->dic->settings();
		$this->access     = $this->dic->access();
		$this->ctrl       = $this->dic->ctrl();
		$this->tabs       = $this->dic->tabs();
		$this->user       = $this->dic->user();
		$this->navHistory = $this->dic['ilNavigationHistory'];
		$this->error      = $this->dic['ilErr'];

		$this->lng->loadLanguageModule('copa');

		if ($this->object instanceof \ilObjContentPage) {
			$this->infoScreenEnabled = \ilContainer::_lookupContainerSetting(
				$this->object->getId(),
				\ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
				true
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function afterSave(\ilObject $a_new_object)
	{
		\ilUtil::sendSuccess($this->lng->txt('object_added'), true);
		$this->ctrl->redirect($this, 'edit');
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return self::OBJ_TYPE;
	}

	/**
	 * @inheritdoc
	 */
	public function setTabs()
	{
		if ($this->checkPermissionBool('read')) {
			$this->tabs->addTab(
				self::UI_TAB_ID_CONTENT,
				$this->lng->txt('content'),
				$this->ctrl->getLinkTarget($this, self::UI_CMD_VIEW)
			);
		}

		if ($this->infoScreenEnabled && ($this->checkPermissionBool('visible') || $this->checkPermissionBool('read'))) {
			$this->tabs->addTab(
				self::UI_TAB_ID_INFO,
				$this->lng->txt('info_short'),
				$this->ctrl->getLinkTargetByClass('ilinfoscreengui', 'showSummary')
			);
		}

		if ($this->checkPermissionBool('write')) {
			$this->tabs->addTab(
				self::UI_TAB_ID_SETTINGS,
				$this->lng->txt('settings'),
				$this->ctrl->getLinkTarget($this, self::UI_CMD_EDIT)
			);
		}

		if (\ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
			$this->tabs->addTab(
				self::UI_TAB_ID_LP,
				$this->lng->txt('learning_progress'),
				$this->ctrl->getLinkTargetByClass('illearningprogressgui')
			);
		}

		if ($this->checkPermissionBool('write')) {
			$this->tabs->addTab(
				self::UI_TAB_ID_EXPORT,
				$this->lng->txt('export'),
				$this->ctrl->getLinkTargetByClass('ilexportgui')
			);
		}

		if ($this->checkPermissionBool('edit_permission')) {
			$this->tabs->addTab(
				self::UI_TAB_ID_PERMISSIONS,
				$this->lng->txt('perm_settings'),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm')
			);
		}
	}

	/**
	 * Sub tab configuration of the content area
	 */
	protected function setContentSubTabs()
	{
		if ($this->checkPermissionBool('write')) {
			$this->tabs->addSubTab(
				self::UI_TAB_ID_CONTENT,
				$this->lng->txt('view'),
				$this->ctrl->getLinkTarget($this, self::UI_CMD_VIEW)
			);

			if (!$this->user->isAnonymous()) {
				$this->lng->loadLanguageModule('cntr');
				$this->tabs->addSubTab(
					'page_editor',
					$this->lng->txt('cntr_text_media_editor'),
					$this->ctrl->getLinkTargetByClass('ilContentPagePageGUI', 'edit')
				);
			}
		}
	}

	/**
	 * Sub tab configuration of the settings area
	 * @param string $activeTab 
	 */
	protected function setSettingsSubTabs($activeTab)
	{
		if ($this->checkPermissionBool('write')) {
			$this->tabs->addSubTab(
				self::UI_TAB_ID_SETTINGS,
				$this->lng->txt('settings'),
				$this->ctrl->getLinkTarget($this, self::UI_CMD_EDIT)
			);

			if ($this->settings->get('custom_icons')) {
				$this->tabs_gui->addSubTab(
					self::UI_TAB_ID_ICON,
					$this->lng->txt('icon_settings'),
					$this->ctrl->getLinkTargetByClass('ilObjectCustomIconConfigurationGUI')
				);
			}

			$this->tabs->setSubTabActive($activeTab);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function setTitleAndDescription()
	{
		parent::setTitleAndDescription();

		$icon = ilObject::_getIcon($this->object->getId(), 'big', $this->object->getType());
		$this->tpl->setTitleIcon($icon, $this->lng->txt('obj_'.$this->object->getType()));
	}

	/**
	 * @inheritdoc
	 */
	public function executeCommand()
	{
		$nextClass = $this->ctrl->getNextClass($this); 
		$cmd       = $this->ctrl->getCmd(self::UI_CMD_VIEW);

		$this->addToNavigationHistory();

		switch (strtolower($nextClass)) {
			case 'ilcontentpagepagegui':
				if (!$this->checkPermissionBool('write') || $this->user->isAnonymous()) {
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}

				$this->prepareOutput();

				$forwarder = new \ilContentPagePageCommandForwarder($this->request, $this->ctrl, $this->tabs, $this->lng, $this->object);
				$forwarder->forward();
				break;

			case 'ilinfoscreengui':
				if (!$this->infoScreenEnabled) {
					return;
				}
				$this->prepareOutput();

				$this->infoScreenForward();
				break;

			case 'ilcommonactiondispatchergui':
				$this->ctrl->forwardCommand(\ilCommonActionDispatcherGUI::getInstanceFromAjaxCall());
				break;

			case 'ilpermissiongui':
				$this->checkPermission('edit_permission');

				$this->prepareOutput();
				$this->tabs->activateTab(self::UI_TAB_ID_PERMISSIONS);

				$this->ctrl->forwardCommand(new \ilPermissionGUI($this));
				break;

			case 'illearningprogressgui':
				if (!\ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}

				$this->prepareOutput();
				$this->tabs->activateTab(self::UI_TAB_ID_LP);

				$this->ctrl->forwardCommand(new \ilLearningProgressGUI(
					\ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
					$this->object->getRefId(),
					isset($this->request->getQueryParams()['user_id']) && is_numeric($this->request->getQueryParams()['user_id']) ?
						(int)$this->request->getQueryParams()['user_id'] :
						$this->user->getId()
				));
				break;

			case 'ilexportgui':
				$this->checkPermission('write');

				$this->prepareOutput();
				$this->tabs->activateTab(self::UI_TAB_ID_EXPORT);

				$gui = new \ilExportGUI($this);
				$gui->addFormat('xml');
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjectcustomiconconfigurationgui':
				if (!$this->checkPermissionBool('write') || !$this->settings->get('custom_icons')) {
					$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
				}

				$this->prepareOutput();
				$this->tabs->activateTab(self::UI_TAB_ID_SETTINGS);
				$this->setSettingsSubTabs(self::UI_TAB_ID_ICON);

				require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfigurationGUI.php';
				$gui = new \ilObjectCustomIconConfigurationGUI($this->dic, $this, $this->object);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjectcopygui':
				$this->tpl->getStandardTemplate();

				$gui = new \ilObjectCopyGUI($this);
				$gui->setType($this->object->getType());

				$this->ctrl->forwardCommand($gui);
				break;

			default:
				switch (true) {
					case in_array(strtolower($cmd), array_map('strtolower', [self::UI_CMD_EDIT, self::UI_CMD_UPDATE])):
						$this->setSettingsSubTabs(self::UI_TAB_ID_SETTINGS);
						break;
				}

				if (in_array(strtolower($cmd), array_map('strtolower', ['addToDesk', 'removeFromDesk']))) {
					$this->ctrl->setCmd($cmd . 'Object');
				}

				return parent::executeCommand();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function addLocatorItems()
	{
		if ($this->object instanceof \ilObject) {
			$this->locator->addItem(
				$this->object->getTitle(),
				$this->ctrl->getLinkTarget($this, self::UI_CMD_VIEW),
				'',
				$this->object->getRefId()
			);
		}
	}

	/**
	 * 
	 */
	public function addToNavigationHistory() {
		if(!$this->getCreationMode()) {
			if($this->checkPermissionBool('read')) {
				$this->navHistory->addItem(
					$this->object->getRefId(),
					\ilLink::_getLink($this->object->getRefId(), $this->object->getType()),
					$this->object->getType()
				);
				$this->addHeaderAction();
			}
		}
	}

	/**
	 *
	 */
	public function infoScreen()
	{
		$this->ctrl->setCmd('showSummary');
		$this->ctrl->setCmdClass('ilinfoscreengui');

		$this->infoScreenForward();
	}

	/**
	 * 
	 */
	public function infoScreenForward()
	{
		if (!$this->infoScreenEnabled) {
			return;
		}

		if (!$this->checkPermissionBool('visible') && !$this->checkPermissionBool('read')) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->tabs->activateTab(self::UI_TAB_ID_INFO);

		$info = new \ilInfoScreenGUI($this);
		$info->enableLearningProgress(true);
		$info->enablePrivateNotes();
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

		$this->ctrl->forwardCommand($info);
	}

	/**
	 * @inheritdoc
	 */
	protected function initEditCustomForm(\ilPropertyFormGUI $a_form)
	{
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt('obj_features'));
		$a_form->addItem($sh);

		\ilObjectServiceSettingsGUI::initServiceSettingsForm(
			$this->object->getId(),
			$a_form,
			array(
				\ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values[\ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY] = $this->infoScreenEnabled;
	}

	/**
	 * @inheritdoc
	 */
	protected function updateCustom(\ilPropertyFormGUI $a_form)
	{
		\ilObjectServiceSettingsGUI::updateServiceSettingsForm(
			$this->object->getId(),
			$a_form,
			array(
				\ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
			)
		);
	}

	/**
	 * Deep link
	 * @param string $target
	 */
	public static function _goto($target)
	{
		global $DIC;

		$targetAttributes = explode('_', $target);
		$refId            = (int)$targetAttributes[0];

		if ((int) $refId <= 0) {
			$DIC['ilErr']->raiseError($DIC->language()->txt('msg_no_perm_read'), $DIC['ilErr']->FATAL);
		}

		if ($DIC->access()->checkAccess('read', '', $refId)) {
			$DIC->ctrl()->setTargetScript('ilias.php');
			$DIC->ctrl()->initBaseClass('ilRepositoryGUI');
			$DIC->ctrl()->setParameterByClass(__CLASS__, 'ref_id', $refId);
			$DIC->ctrl()->redirectByClass(array(
				'ilRepositoryGUI',
				__CLASS__,
			), self::UI_CMD_VIEW);
		} else if($DIC->access()->checkAccess('read', '', ROOT_FOLDER_ID)) {
			\ilUtil::sendInfo(sprintf(
				$DIC->language()->txt('msg_no_perm_read_item'), \ilObject::_lookupTitle(\ilObject::_lookupObjId($refId))
			), true);

			$_GET['target'] = '';
			$_GET['ref_id'] = ROOT_FOLDER_ID;
			$_GET['baseClass'] = 'ilRepositoryGUI';

			include 'ilias.php';
			exit();
		}

		$DIC['ilErr']->raiseError($DIC->language()->txt('msg_no_perm_read'), $DIC['ilErr']->FATAL);
	}

	/**
	 * @inheritdoc
	 */
	public function addToDeskObject()
	{
		if ((int)$this->settings->get('disable_my_offers')) {
			$this->ctrl->redirect($this, self::UI_CMD_VIEW);
		}

		\ilDesktopItemGUI::addToDesktop();
		\ilUtil::sendSuccess($this->lng->txt('added_to_desktop'), true);
		$this->ctrl->redirect($this, self::UI_CMD_VIEW);
	}

	/**
	 * @inheritdoc
	 */
	public function removeFromDeskObject()
	{
		if ((int)$this->settings->get('disable_my_offers')) {
			$this->ctrl->redirect($this, self::UI_CMD_VIEW);
		}

		\ilDesktopItemGUI::removeFromDesktop();
		\ilUtil::sendSuccess($this->lng->txt('removed_from_desktop'), true);
		$this->ctrl->redirect($this, self::UI_CMD_VIEW);
	}

	/**
	 * @param string $ctrlLink
	 * @return string
	 */
	public function getContent($ctrlLink = '')
	{
		if (\ilContentPagePage::_exists($this->object->getType(), $this->object->getId()) && $this->checkPermissionBool('read')) {
			$pageGui = new \ilContentPagePageGUI($this->object->getId());
			$pageGui->setEnabledTabs(false);

			if (is_string($ctrlLink) && strlen($ctrlLink) > 0) {
				$pageGui->setFileDownloadLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_FILE);
				$pageGui->setFullscreenLink($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DISPLAY_FULLSCREEN);
				$pageGui->setSourcecodeDownloadScript($ctrlLink . '&cmd=' . self::UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH);
			}

			$html = $pageGui->getHTML();

			\ilChangeEvent::_recordReadEvent(
				$this->object->getType(),
				$this->object->getRefId(),
				$this->object->getId(),
				$this->user->getId()
			);

			return $html;
		}

		return '';
	}

	/**
	 * Shows the content of the object
	 */
	public function view()
	{
		$this->checkPermission('read');

		$this->setContentSubTabs();

		$this->tabs->activateTab(self::UI_TAB_ID_CONTENT);
		$this->tabs->activateSubTab(self::UI_TAB_ID_CONTENT);

		$this->tpl->setPermanentLink($this->object->getType(), $this->object->getRefId(), '', '_top');

		$this->tpl->setContent($this->getContent());
	}
}