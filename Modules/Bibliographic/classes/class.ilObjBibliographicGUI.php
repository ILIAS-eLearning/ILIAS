<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBibliographicGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Gabriel Comte <gc@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilInfoScreenGUI, ilNoteGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilPermissionGUI, ilObjectCopyGUI, ilExportGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilObjUserGUI, ilBiblEntryPresentationGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilBiblEntryTableGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilBiblFieldFilterGUI
 * @ilCtrl_isCalledBy ilObjBibliographicGUI: ilRepositoryGUI
 */
class ilObjBibliographicGUI extends ilObject2GUI implements ilDesktopItemHandling {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const P_ENTRY_ID = 'entry_id';
	const CMD_SHOW_CONTENT = 'showContent';
	const CMD_SEND_FILE = "sendFile";
	const TAB_CONTENT = "content";
	const SUB_TAB_FILTER = "filter";
	const CMD_VIEW = "view";
	const TAB_EXPORT = "export";
	const TAB_SETTINGS = self::SUBTAB_SETTINGS;
	const TAB_ID_RECORDS = "id_records";
	const TAB_ID_PERMISSIONS = "id_permissions";
	const TAB_ID_INFO = "id_info";
	const CMD_SHOW_DETAILS = "showDetails";
	const CMD_EDIT = "edit";
	const SUBTAB_SETTINGS = "settings";
	const CMD_EDIT_OBJECT = 'editObject';
	const CMD_UPDATE_OBJECT = 'updateObject';
	/**
	 * @var ilObjBibliographic
	 */
	public $object;
	/**
	 * @var \ilBiblFactoryFacade
	 */
	protected $facade;
	/**
	 * @var \ilBiblTranslationFactory
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactory
	 */
	protected $field_factory;
	/**
	 * @var \ilBiblFieldFilterFactory
	 */
	protected $filter_factory;
	/**
	 * @var \ilBiblTypeFactory
	 */
	protected $type_factory;
	/**
	 * @var string
	 */
	protected $cmd = self::CMD_SHOW_CONTENT;


	/**
	 * @param int $a_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0) {
		global $DIC;

		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
		$DIC->language()->loadLanguageModule('bibl');

		if (is_object($this->object)) {
			$this->facade = new ilBiblFactoryFacade($this->object);
		}
	}


	/**
	 * getStandardCmd
	 *
	 * @return String
	 */
	public function getStandardCmd() {
		return self::CMD_VIEW;
	}


	/**
	 * getType
	 *
	 * @return String
	 * @deprecated REFACTOR use type factory via Facade
	 *
	 */
	public function getType() {
		return "bibl";
	}


	/**
	 * executeCommand
	 */
	public function executeCommand() {
		global $DIC;
		$ilNavigationHistory = $DIC['ilNavigationHistory'];

		// Navigation History
		$link = $this->dic()->ctrl()->getLinkTarget($this, $this->getStandardCmd());
		if ($this->object != null) {
			$ilNavigationHistory->addItem($this->object->getRefId(), $link, "bibl");
			$this->addHeaderAction();
		}

		// general Access Check, especially for single entries not matching the object
		if ($this->object instanceof ilObjBibliographic && !$DIC->access()->checkAccess('visible', "", $this->object->getRefId())) {
			$this->handleNonAccess();
		}

		$next_class = $this->dic()->ctrl()->getNextClass($this);
		$this->cmd = $this->dic()->ctrl()->getCmd();
		switch ($next_class) {
			case strtolower(ilInfoScreenGUI::class):
				$this->prepareOutput();
				$this->dic()->tabs()->activateTab(self::TAB_ID_INFO);
				$this->infoScreenForward();
				break;
			case strtolower(ilCommonActionDispatcherGUI::class):
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case strtolower(ilPermissionGUI::class):
				$this->prepareOutput();
				$this->dic()->tabs()->activateTab(self::TAB_ID_PERMISSIONS);
				$this->ctrl->forwardCommand(new ilPermissionGUI($this));
				break;
			case strtolower(ilObjectCopyGUI::class):
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('bibl');
				$this->dic()['tpl']->getStandardTemplate();
				$this->ctrl->forwardCommand($cp);
				break;
			case strtolower(ilObjFileGUI::class):
				$this->prepareOutput();
				$this->dic()->tabs()->setTabActive(self::TAB_ID_RECORDS);
				$this->ctrl->forwardCommand(new ilObjFile($this));
				break;
			case strtolower(ilExportGUI::class):
				$this->prepareOutput();
				$this->dic()->tabs()->setTabActive(self::TAB_EXPORT);
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$this->ctrl->forwardCommand($exp_gui);
				break;
			case strtolower(ilBiblFieldFilterGUI::class):
				$this->prepareOutput();
				$this->dic()->tabs()->setTabActive(self::TAB_SETTINGS);
				$this->initSubTabs();
				$this->tabs_gui->activateSubTab(self::SUB_TAB_FILTER);
				$this->ctrl->forwardCommand(new ilBiblFieldFilterGUI($this->facade));
				break;
			default:
				$this->prepareOutput();
				$cmd = $this->ctrl->getCmd(self::CMD_SHOW_CONTENT);
				switch ($cmd) {
					case 'edit':
					case 'update':
					case self::CMD_EDIT_OBJECT:
					case self::CMD_UPDATE_OBJECT:
						$this->initSubTabs();
						$this->tabs_gui->activateSubTab(self::SUBTAB_SETTINGS);
						$this->{$cmd}();
						break;
					default:
						$this->{$cmd}();
						break;
				}
				break;
		}

		return true;
	}


	/**
	 * this one is called from the info button in the repository
	 * not very nice to set cmdClass/Cmd manually, if everything
	 * works through ilCtrl in the future this may be changed
	 */
	public function infoScreen() {
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass(ilInfoScreenGUI::class);
		$this->infoScreenForward();
	}


	/**
	 * show information screen
	 */
	public function infoScreenForward() {
		global $DIC;

		if (!$this->checkPermissionBoolAndReturn("visible") && !$this->checkPermissionBoolAndReturn('read')) {
			ilUtil::sendFailure($DIC['lng']->txt("msg_no_perm_read"), true);
			$this->ctrl->redirectByClass('ilPersonalDesktopGUI', '');
		}
		$DIC->tabs()->activateTab(self::TAB_ID_INFO);
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		$this->ctrl->forwardCommand($info);
	}


	/*
	 * addLocatorItems
	 */
	public function addLocatorItems() {
		global $DIC;
		$ilLocator = $DIC['ilLocator'];
		if (is_object($this->object)) {
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
		}
	}


	/**
	 * _goto
	 * Deep link
	 *
	 * @param string $a_target
	 */
	public static function _goto($a_target) {
		global $DIC;

		$id = explode("_", $a_target);
		$DIC->ctrl()->initBaseClass(ilRepositoryGUI::class);
		$DIC->ctrl()->setParameterByClass(ilObjBibliographicGUI::class, "ref_id", $id[0]);
		// Detail-View
		if ($id[1]) {
			$DIC->ctrl()
				->setParameterByClass(ilObjBibliographicGUI::class, ilObjBibliographicGUI::P_ENTRY_ID, $id[1]);
			$DIC->ctrl()->redirectByClass(
				array(
					ilRepositoryGUI::class,
					ilObjBibliographicGUI::class,
				), self::CMD_SHOW_DETAILS
			);
		} else {
			$DIC->ctrl()->redirectByClass(
				array(
					ilRepositoryGUI::class,
					ilObjBibliographicGUI::class,
				), self::CMD_VIEW
			);
		}
	}


	/**
	 * @param string $a_new_type
	 *
	 * @return array
	 */
	protected function initCreationForms($a_new_type) {
		global $DIC;

		$forms = parent::initCreationForms($a_new_type);
		// Add File-Upload
		$in_file = new ilFileInputGUI($DIC->language()->txt("bibliography_file"), "bibliographic_file");
		$in_file->setSuffixes(array("ris", "bib", "bibtex"));
		$in_file->setRequired(true);
		$forms[self::CFORM_NEW]->addItem($in_file);
		$this->ctrl->saveParameterByClass('ilobjrootfoldergui', 'new_type');
		$forms[self::CFORM_NEW]->setFormAction($this->ctrl->getFormActionByClass('ilobjrootfoldergui', "save"));

		return $forms;
	}


	public function save() {
		global $DIC;

		$form = $this->initCreationForms($this->getType());
		if ($form[self::CFORM_NEW]->checkInput()) {
			parent::save();
		} else {
			$form = $form[self::CFORM_NEW];
			$form->setValuesByPost();
			$DIC->ui()->mainTemplate()->setContent($form->getHtml());
		}
	}


	/**
	 * @param \ilObject $a_new_object
	 */
	protected function afterSave(ilObject $a_new_object) {
		/**
		 * @var $a_new_object ilObjBibliographic
		 */
		assert($a_new_object instanceof ilObjBibliographic);
		$a_new_object->doUpdate();
		$this->addNews($a_new_object->getId(), 'created');
		$this->ctrl->redirect($this, self::CMD_EDIT);
	}


	/**
	 * setTabs
	 * create tabs (repository/workspace switch)
	 *
	 * this had to be moved here because of the context-specific permission tab
	 */
	public function setTabs() {
		global $DIC;

		$ilHelp = $DIC['ilHelp'];
		/**
		 * @var $ilHelp      ilHelpGUI
		 */
		$ilHelp->setScreenIdComponent('bibl');
		// info screen
		if ($DIC->access()->checkAccess('read', "", $this->object->getRefId())) {
			$DIC->tabs()->addTab(
				self::TAB_CONTENT, $DIC->language()
				->txt(self::TAB_CONTENT), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_CONTENT)
			);
		}
		// info screen
		if ($DIC->access()->checkAccess('visible', "", $this->object->getRefId())
			|| $DIC->access()->checkAccess('read', "", $this->object->getRefId())
		) {
			$DIC->tabs()->addTab(
				self::TAB_ID_INFO, $DIC->language()
				->txt("info_short"), $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
			);
		}
		// settings
		if ($DIC->access()->checkAccess('write', "", $this->object->getRefId())) {
			$DIC->tabs()->addTab(
				self::SUBTAB_SETTINGS, $DIC->language()
				->txt(self::SUBTAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_EDIT_OBJECT)
			);
		}
		// export
		if ($DIC->access()->checkAccess("write", "", $this->object->getRefId())) {
			$DIC->tabs()->addTab(
				self::TAB_EXPORT, $DIC->language()
				->txt(self::TAB_EXPORT), $this->ctrl->getLinkTargetByClass("ilexportgui", "")
			);
		}
		// edit permissions
		if ($DIC->access()->checkAccess('edit_permission', "", $this->object->getRefId())) {
			$DIC->tabs()->addTab(
				self::TAB_ID_PERMISSIONS, $DIC->language()
				->txt("perm_settings"), $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm")
			);
		}
	}


	protected function initSubTabs() {
		global $DIC;
		$DIC->tabs()->addSubTab(
			self::SUBTAB_SETTINGS, $DIC->language()
			->txt(self::SUBTAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_EDIT_OBJECT)
		);
		$DIC->tabs()->addSubTab(
			self::SUB_TAB_FILTER, $DIC->language()
			->txt("bibl_filter"), $this->ctrl->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_STANDARD)
		);
	}


	public function initEditForm() {
		global $DIC;

		$form = parent::initEditForm();
		// Add File-Upload
		$in_file = new ilFileInputGUI(
			$DIC->language()
				->txt("bibliography_file"), "bibliographic_file"
		);
		$in_file->setSuffixes(array("ris", "bib", "bibtex"));
		$in_file->setRequired(false);
		$cb_override = new ilCheckboxInputGUI(
			$DIC->language()
				->txt("override_entries"), "override_entries"
		);
		$cb_override->addSubItem($in_file);

		$form->addItem($cb_override);
		$form->setFormAction($DIC->ctrl()->getFormAction($this, "save"));

		return $form;
	}


	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function initEditCustomForm(ilPropertyFormGUI $a_form) {
		global $DIC;

		$DIC->tabs()->activateTab(self::SUBTAB_SETTINGS);
		// is_online
		$cb = new ilCheckboxInputGUI($DIC->language()->txt("online"), "is_online");
		$a_form->addItem($cb);
	}


	/**
	 * @param array $a_values
	 *
	 * @return array
	 */
	public function getEditFormCustomValues(array &$a_values) {
		$a_values["is_online"] = $this->object->getOnline();

		return $a_values;
	}


	public function render() {
		$this->showContent();
	}


	/**
	 * shows the overview page with all entries in a table
	 */
	public function showContent() {
		global $DIC;

		// if user has read permission and object is online OR user has write permissions
		$read_access = $DIC->access()->checkAccess('read', "", $this->object->getRefId());
		$online = $this->object->getOnline();
		$write_access = $DIC->access()->checkAccess('write', "", $this->object->getRefId());
		if (($read_access && $online) || $write_access) {
			$DIC->tabs()->activateTab(self::TAB_CONTENT);

			$b = ilLinkButton::getInstance();
			$b->setCaption('download_original_file');
			$b->setUrl($DIC->ctrl()->getLinkTargetByClass(self::class, self::CMD_SEND_FILE));
			$b->setPrimary(true);
			$DIC->toolbar()->addButtonInstance($b);

			$table = new ilBiblEntryTableGUI($this, $this->facade);
			$html = $table->getHTML();
			$DIC->ui()->mainTemplate()->setContent($html);

			//Permanent Link
			$DIC->ui()->mainTemplate()->setPermanentLink("bibl", $this->object->getRefId());
		} else {
			$object_title = ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["ref_id"]));
			ilUtil::sendFailure(
				sprintf(
					$DIC->language()
						->txt("msg_no_perm_read_item"), $object_title
				), true
			);
			//redirect to repository without any parameters
			$this->handleNonAccess();
		}
	}


	protected function applyFilter() {
		$table = new ilBiblEntryTableGUI($this, $this->facade);
		$table->writeFilterToSession();
		$table->resetOffset();
		$this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
	}


	protected function resetFilter() {
		$table = new ilBiblEntryTableGUI($this, $this->facade);
		$table->resetFilter();
		$table->resetOffset();
		$this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
	}


	/**
	 * provide file as a download
	 */
	public function sendFile() {
		global $DIC;

		if ($DIC['ilAccess']->checkAccess('read', "", $this->object->getRefId())) {
			$file_path = $this->object->getLegacyAbsolutePath();
			if ($file_path) {
				if (is_file($file_path)) {
					ilFileDelivery::deliverFileAttached($file_path, $this->object->getFilename(), 'application/octet-stream');
				} else {
					ilUtil::sendFailure($DIC['lng']->txt("file_not_found"));
					$this->showContent();
				}
			}
		} else {
			$this->handleNonAccess();
		}
	}


	public function showDetails() {
		global $DIC;

		if ($DIC->access()->checkAccess('read', "", $this->object->getRefId())) {
			$id = $DIC->http()->request()->getQueryParams()[self::P_ENTRY_ID];
			$entry = $this->facade->entryFactory()
				->findByIdAndTypeString($id, $this->object->getFileTypeAsString());
			$bibGUI = new ilBiblEntryDetailPresentationGUI($entry, $this->facade);

			$DIC->ui()->mainTemplate()->setContent($bibGUI->getHTML());
		} else {
			$this->handleNonAccess();
		}
	}


	public function view() {
		$this->showContent();
	}


	/**
	 * updateSettings
	 */
	public function updateCustom(ilPropertyFormGUI $a_form) {
		global $DIC;

		if ($DIC->access()->checkAccess('write', "", $this->object->getRefId())) {
			if ($this->object->getOnline() != $a_form->getInput("is_online")) {
				$this->object->setOnline($a_form->getInput("is_online"));
			}

			if (!empty($_FILES['bibliographic_file']['name'])) {
				$this->addNews($this->object->getId(), 'updated');
			}
		} else {
			$this->handleNonAccess();
		}
	}


	public function toggleNotification() {
		global $DIC;

		switch ($_GET["ntf"]) {
			case 1:
				ilNotification::setNotification(
					ilNotification::TYPE_DATA_COLLECTION, $DIC->user()
					->getId(), $this->obj_id, false
				);
				break;
			case 2:
				ilNotification::setNotification(
					ilNotification::TYPE_DATA_COLLECTION, $DIC->user()
					->getId(), $this->obj_id, true
				);
				break;
		}
		$DIC->ctrl()->redirect($this, "");
	}


	/**
	 * @param string $change
	 */
	public function addNews($obj_id, $change = 'created') {
		global $DIC;

		$ilNewsItem = new ilNewsItem();
		$ilNewsItem->setTitle($DIC->language()->txt('news_title_' . $change));
		$ilNewsItem->setPriority(NEWS_NOTICE);
		$ilNewsItem->setContext($obj_id, $this->getType());
		$ilNewsItem->setUserId($DIC->user()->getId());
		$ilNewsItem->setVisibility(NEWS_USERS);
		$ilNewsItem->setContentTextIsLangVar(false);
		$ilNewsItem->create();
	}


	/**
	 * Add desktop item
	 *
	 * @access public
	 */
	public function addToDeskObject() {
		global $DIC;

		ilDesktopItemGUI::addToDesktop();
		ilUtil::sendSuccess($DIC->language()->txt("added_to_desktop"), true);
		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	/**
	 * Remove from desktop
	 *
	 * @access public
	 */
	public function removeFromDeskObject() {
		global $DIC;

		ilDesktopItemGUI::removeFromDesktop();
		ilUtil::sendSuccess($DIC->language()->txt("removed_from_desktop"), true);
		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	/**
	 * Add desktop item. Alias for addToDeskObject.
	 *
	 * @access public
	 */
	public function addToDesk() {
		$this->addToDeskObject();
	}


	/**
	 * Remove from desktop. Alias for removeFromDeskObject.
	 *
	 * @access public
	 */
	public function removeFromDesk() {
		$this->removeFromDeskObject();
	}


	/**
	 * @param \ilObject $a_new_object
	 */
	protected function afterImport(ilObject $a_new_object) {
		/**
		 * @var $a_new_object ilObjBibliographic
		 */
		$a_new_object->parseFileToDatabase();

		parent::afterImport($a_new_object);
	}


	private function handleNonAccess() {
		global $DIC;

		unset($_GET);
		ilUtil::sendFailure($DIC->language()->txt("no_permission"), true);
		ilObjectGUI::_gotoRepositoryRoot();
	}
}
