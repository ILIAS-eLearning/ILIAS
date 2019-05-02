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
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilObjUserGUI, ilBibliographicDetailsGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilBibliographicRecordListTableGUI
 * @ilCtrl_isCalledBy ilObjBibliographicGUI: ilRepositoryGUI
 *
 * @extends           ilObject2GUI
 */
class ilObjBibliographicGUI extends ilObject2GUI implements ilDesktopItemHandling {

	const P_ENTRY_ID = 'entry_id';
	const CMD_SHOW_CONTENT = 'showContent';
	const CMD_SEND_FILE = "sendFile";
	const TAB_CONTENT = "content";
	const CMD_VIEW = "view";
	const TAB_EXPORT = "export";
	const TAB_ID_RECORDS = "id_records";
	const TAB_ID_PERMISSIONS = "id_permissions";
	const TAB_ID_INFO = "id_info";
	const CMD_SHOW_DETAILS = "showDetails";
	const CMD_EDIT = "edit";
	/**
	 * @var ilObjBibliographic
	 */
	protected $bibl_obj;
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
		$this->lng = $DIC['lng'];
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
		$this->lng->loadLanguageModule('bibl');
		if ($a_id > 0) {
			$this->bibl_obj = $this->object;
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
	 */
	public function getType() {
		return "bibl";
	}


	/**
	 * executeCommand
	 */
	public function executeCommand() {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		$ilNavigationHistory = $DIC['ilNavigationHistory'];
		$tpl = $DIC['tpl'];

		// Navigation History
		$link = $ilCtrl->getLinkTarget($this, $this->getStandardCmd());
		if ($this->object != null) {
			$ilNavigationHistory->addItem($this->object->getRefId(), $link, "bibl");
			$this->addHeaderAction();
		}
		$next_class = $ilCtrl->getNextClass($this);
		$this->cmd = $ilCtrl->getCmd();
		switch ($next_class) {
			case "ilinfoscreengui":
				$this->prepareOutput();
				$ilTabs->activateTab(self::TAB_ID_INFO);
				$this->infoScreenForward();
				break;
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab(self::TAB_ID_PERMISSIONS);
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('bibl');
				$tpl->getStandardTemplate();
				$this->ctrl->forwardCommand($cp);
				break;
			case "ilobjfilegui":
				$this->prepareOutput();
				$ilTabs->setTabActive(self::TAB_ID_RECORDS);
				include_once("./Modules/File/classes/class.ilObjFile.php");
				$file_gui = new ilObjFile($this);
				$this->ctrl->forwardCommand($file_gui);
				break;
			case "ilexportgui":
				$this->prepareOutput();
				$ilTabs->setTabActive(self::TAB_EXPORT);
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$this->ctrl->forwardCommand($exp_gui);
				break;
			default:
				return parent::executeCommand();
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
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreenForward();
	}


	/**
	 * show information screen
	 */
	public function infoScreenForward() {
		global $DIC;

		if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool('read')) {
			ilUtil::sendFailure($DIC['lng']->txt("msg_no_perm_read"), true);
			$this->ctrl->redirectByClass('ilPersonalDesktopGUI', '');
		}
		$DIC['ilTabs']->activateTab(self::TAB_ID_INFO);
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
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
		$DIC['ilCtrl']->setTargetScript("ilias.php");
		$DIC['ilCtrl']->initBaseClass("ilRepositoryGUI");
		$DIC['ilCtrl']->setParameterByClass("ilobjbibliographicgui", "ref_id", $id[0]);
		// Detail-View
		if ($id[1]) {
			$DIC['ilCtrl']->setParameterByClass("ilobjbibliographicgui", ilObjBibliographicGUI::P_ENTRY_ID, $id[1]);
			$DIC['ilCtrl']->redirectByClass(
				array(
					"ilRepositoryGUI",
					"ilobjbibliographicgui",
				), self::CMD_SHOW_DETAILS
			);
		} else {
			$DIC['ilCtrl']->redirectByClass(
				array(
					"ilRepositoryGUI",
					"ilobjbibliographicgui",
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
		$lng = $DIC['lng'];
		$forms = parent::initCreationForms($a_new_type);
		// Add File-Upload
		$in_file = new ilFileInputGUI($lng->txt("bibliography file"), "bibliographic_file");
		$in_file->setSuffixes(array("ris", "bib", "bibtex"));
		$in_file->setRequired(true);
		$forms[self::CFORM_NEW]->addItem($in_file);
		$this->ctrl->saveParameterByClass('ilobjrootfoldergui', 'new_type');
		$forms[self::CFORM_NEW]->setFormAction($this->ctrl->getFormActionByClass('ilobjrootfoldergui', "save"));

		return $forms;
	}


	public function save() {
		global $DIC;
		$tpl = $DIC['tpl'];
		$form = $this->initCreationForms($this->getType());
		if ($form[self::CFORM_NEW]->checkInput()) {
			parent::save();
		} else {
			$form = $form[self::CFORM_NEW];
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
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
		$ilAccess = $DIC['ilAccess'];
		$ilTabs = $DIC['ilTabs'];
		$lng = $DIC['lng'];
		$ilHelp = $DIC['ilHelp'];
		/**
		 * @var $ilAccess    ilAccessHandler
		 * @var $ilTabs      ilTabsGUI
		 * @var $lng         ilLanguage
		 * @var $ilHelp      ilHelpGUI
		 */
		$ilHelp->setScreenIdComponent('bibl');
		// info screen
		if ($ilAccess->checkAccess('read', "", $this->object->getRefId())) {
			$ilTabs->addTab(self::TAB_CONTENT, $lng->txt(self::TAB_CONTENT), $this->ctrl->getLinkTarget($this, self::CMD_SHOW_CONTENT));
		}
		// info screen
		if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()) || $ilAccess->checkAccess('read', "", $this->object->getRefId())) {
			$ilTabs->addTab(self::TAB_ID_INFO, $lng->txt("info_short"), $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}
		// settings
		if ($ilAccess->checkAccess('write', "", $this->object->getRefId())) {
			$ilTabs->addTab("settings", $lng->txt("settings"), $this->ctrl->getLinkTarget($this, "editObject"));
		}
		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
			$ilTabs->addTab(self::TAB_EXPORT, $lng->txt(self::TAB_EXPORT), $this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}
		// edit permissions
		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
			$ilTabs->addTab(self::TAB_ID_PERMISSIONS, $lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}
	}


	public function initEditForm() {
		global $DIC;
		$lng = $DIC['lng'];
		$form = parent::initEditForm();
		// Add File-Upload
		$in_file = new ilFileStandardDropzoneInputGUI($lng->txt("bibliography file"), "bibliographic_file");
		$in_file->setSuffixes(array("ris", "bib", "bibtex"));
		$in_file->setRequired(false);
		$cb_override = new ilCheckboxInputGUI($this->lng->txt("override_entries"), "override_entries");
		$cb_override->addSubItem($in_file);

		$form->addItem($cb_override);
		$form->setFormAction($this->ctrl->getFormAction($this, "save"));

		return $form;
	}


	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	protected function initEditCustomForm(ilPropertyFormGUI $a_form) {
		global $DIC;
		$ilTabs = $DIC['ilTabs'];
		$ilTabs->activateTab("settings");
		// is_online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "is_online");
		$a_form->addItem($cb);
	}


	/**
	 * @param array $a_values
	 *
	 * @return array|void
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
		$ilAccess = $DIC['ilAccess'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		// if user has read permission and object is online OR user has write permissions
		if (($ilAccess->checkAccess('read', "", $this->object->getRefId())
				&& $this->object->getOnline())
			|| $ilAccess->checkAccess('write', "", $this->object->getRefId())
		) {
			$ilTabs->setTabActive(self::TAB_CONTENT);

			// With new UI service, currently not supported by ilToolbar
			//			$f = $DIC->ui()->factory()->button()
			//			         ->primary($lng->txt("download_original_file"), $ilCtrl->getLinkTargetByClass("ilBibliographicDetailsGUI", "sendFile"));
			//			$ilToolbar->addText($DIC->ui()->renderer()->render($f));

			$b = ilLinkButton::getInstance();
			$b->setCaption('download_original_file');
			$b->setUrl($ilCtrl->getLinkTargetByClass("ilBibliographicDetailsGUI", self::CMD_SEND_FILE));
			$b->setPrimary(true);
			$DIC['ilToolbar']->addButtonInstance($b);

			include_once "./Modules/Bibliographic/classes/class.ilBibliographicRecordListTableGUI.php";
			$table = new ilBibliographicRecordListTableGUI($this, self::CMD_SHOW_CONTENT);
			$html = $table->getHTML();
			$DIC['tpl']->setContent($html);

			//Permanent Link
			$DIC['tpl']->setPermanentLink("bibl", $this->object->getRefId());
		} else {
			$object_title = ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["ref_id"]));
			ilUtil::sendFailure(sprintf($this->lng->txt("msg_no_perm_read_item"), $object_title), true);
			//redirect to repository without any parameters
			unset($_GET);
			ilObjectGUI::_gotoRepositoryRoot();
		}
	}


	/**
	 * provide file as a download
	 */
	public function sendFile() {
		global $DIC;

		if ($DIC['ilAccess']->checkAccess('read', "", $this->object->getRefId())) {
			$file_path = $this->bibl_obj->getLegacyAbsolutePath();
			if ($file_path) {
				if (is_file($file_path)) {
					ilFileDelivery::deliverFileAttached($file_path, $this->bibl_obj->getFilename(), 'application/octet-stream');
				} else {
					ilUtil::sendFailure($DIC['lng']->txt("file_not_found"));
					$this->showContent();
				}
			}
		} else {
			ilUtil::sendFailure($DIC['lng']->txt("no_permission"), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
	}


	public function showDetails() {
		global $DIC;

		if ($DIC['ilAccess']->checkAccess('read', "", $this->object->getRefId())) {
			$bibGUI = ilBibliographicDetailsGUI::getInstance($this->bibl_obj, $_GET[self::P_ENTRY_ID]);
			$this->tpl->setContent($bibGUI->getHTML());
		} else {
			ilUtil::sendFailure($this->lng->txt("no_permission"), true);
			ilObjectGUI::_gotoRepositoryRoot();
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
		$ilAccess = $DIC['ilAccess'];
		if ($ilAccess->checkAccess('write', "", $this->object->getRefId())) {
			if ($this->object->getOnline() != $a_form->getInput("is_online")) {
				$this->object->setOnline($a_form->getInput("is_online"));
			}

			if (!empty($_FILES['bibliographic_file']['name'])) {
				$this->addNews($this->bibl_obj->getId(), 'updated');
			}
		} else {
			ilUtil::sendFailure($this->lng->txt("no_permission"), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
	}


	public function toggleNotification() {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$ilUser = $DIC['ilUser'];
		include_once "./Services/Notification/classes/class.ilNotification.php";
		switch ($_GET["ntf"]) {
			case 1:
				ilNotification::setNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->obj_id, false);
				break;
			case 2:
				ilNotification::setNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->obj_id, true);
				break;
		}
		$ilCtrl->redirect($this, "");
	}


	/**
	 * @param string $change
	 */
	public function addNews($obj_id, $change = 'created') {
		global $DIC;
		$lng = $DIC['lng'];
		$ilUser = $DIC['ilUser'];

		$ilNewsItem = new ilNewsItem();
		$ilNewsItem->setTitle($lng->txt('news_title_' . $change));
		$ilNewsItem->setPriority(NEWS_NOTICE);
		$ilNewsItem->setContext($obj_id, $this->getType());
		$ilNewsItem->setUserId($ilUser->getId());
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
		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::addToDesktop();
		ilUtil::sendSuccess($this->lng->txt("added_to_desktop"), true);
		$this->ctrl->redirect($this, self::CMD_VIEW);
	}


	/**
	 * Remove from desktop
	 *
	 * @access public
	 */
	public function removeFromDeskObject() {
		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
		ilDesktopItemGUI::removeFromDesktop();
		ilUtil::sendSuccess($this->lng->txt("removed_from_desktop"), true);
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
		$a_new_object->writeSourcefileEntriesToDb();
		parent::afterImport($a_new_object);
	}
}
