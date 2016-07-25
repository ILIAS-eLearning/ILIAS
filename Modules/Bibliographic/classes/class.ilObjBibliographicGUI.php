<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Modules/Bibliographic/classes/class.ilBibliographicDetailsGUI.php";
require_once("./Services/Export/classes/class.ilExportGUI.php");
require_once('./Services/News/classes/class.ilNewsItem.php');
require_once('./Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php');

/**
 * Class ilObjBibliographicGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Gabriel Comte <gc@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilPermissionGUI, ilObjectCopyGUI, ilExportGUI
 * @ilCtrl_Calls      ilObjBibliographicGUI: ilObjUserGUI, ilBibliographicDetailsGUI, ilDataBibliographicRecordListTableGUI
 * @ilCtrl_isCalledBy ilObjBibliographicGUI: ilRepositoryGUI
 *
 * @extends           ilObject2GUI
 */
class ilObjBibliographicGUI extends ilObject2GUI implements ilDesktopItemHandling {

	const P_ENTRY_ID = 'entry_id';
	/**
	 * @var ilObjBibliographic
	 */
	protected $bibl_obj;


	/**
	 * @param int $a_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0) {
		global $DIC;
		$lng = $DIC['lng'];
		$ilias = $DIC['ilias'];
		$this->lng = $lng;
		$this->ilias = $ilias;
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
		$lng->loadLanguageModule('bibl');
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
		return "view";
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
		$lng = $DIC['lng'];
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
				$ilTabs->activateTab("id_info");
				$this->infoScreenForward();
				break;
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
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
				$ilTabs->setTabActive("id_records");
				include_once("./Modules/File/classes/class.ilObjFile.php");
				$file_gui = new ilObjFile($this);
				$this->ctrl->forwardCommand($file_gui);
				break;
			case "ilexportgui":
				$this->prepareOutput();
				$ilTabs->setTabActive("export");
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
		$ilTabs = $DIC['ilTabs'];
		$ilErr = $DIC['ilErr'];
		$lng = $DIC['lng'];
		if (!$this->checkPermissionBool("visible")) {
			ilUtil::sendFailure($lng->txt("msg_no_perm_read"), true);
			$this->ctrl->redirectByClass('ilPersonalDesktopGUI', '');
		}
		$ilTabs->activateTab("id_info");
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
		$ilAccess = $DIC['ilAccess'];
		$ilErr = $DIC['ilErr'];
		$ilCtrl = $DIC['ilCtrl'];
		$id = explode("_", $a_target);
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->initBaseClass("ilRepositoryGUI");
		$ilCtrl->setParameterByClass("ilobjbibliographicgui", "ref_id", $id[0]);
		//Detail-View
		if ($id[1]) {
			$ilCtrl->setParameterByClass("ilobjbibliographicgui", ilObjBibliographicGUI::P_ENTRY_ID, $id[1]);
			$ilCtrl->redirectByClass(array( "ilRepositoryGUI", "ilobjbibliographicgui" ), "showDetails");
		} else {
			$ilCtrl->redirectByClass(array( "ilRepositoryGUI", "ilobjbibliographicgui" ), "view");
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
		$in_file->setSuffixes(array( "ris", "bib", "bibtex" ));
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
		$this->ctrl->redirect($this, "edit");
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
			$ilTabs->addTab("content", $lng->txt("content"), $this->ctrl->getLinkTarget($this, "showContent"));
		}
		// info screen
		if ($ilAccess->checkAccess('visible', "", $this->object->getRefId())) {
			$ilTabs->addTab("id_info", $lng->txt("info_short"), $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}
		// settings
		if ($ilAccess->checkAccess('write', "", $this->object->getRefId())) {
			$ilTabs->addTab("settings", $lng->txt("settings"), $this->ctrl->getLinkTarget($this, "editObject"));
		}
		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
			$ilTabs->addTab("export", $lng->txt("export"), $this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}
		// edit permissions
		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
			$ilTabs->addTab("id_permissions", $lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}
	}


	public function initEditForm() {
		global $DIC;
		$lng = $DIC['lng'];
		$form = parent::initEditForm();
		// Add File-Upload
		$in_file = new ilFileInputGUI($lng->txt("bibliography file"), "bibliographic_file");
		$in_file->setSuffixes(array( "ris", "bib", "bibtex" ));
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
		$tpl = $DIC['tpl'];
		$lng = $DIC['lng'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		// if user has read permission and object is online OR user has write permissions
		if (($ilAccess->checkAccess('read', "", $this->object->getRefId()) && $this->object->getOnline())
		    || $ilAccess->checkAccess('write', "", $this->object->getRefId())
		) {
			$ilTabs->setTabActive("content");
			include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			$ilToolbar = new ilToolbarGUI();
			$ilToolbar->addButton($lng->txt("download_original_file"), $ilCtrl->getLinkTargetByClass("ilBibliographicDetailsGUI", "sendFile"));
			include_once "./Modules/Bibliographic/classes/class.ilBibliographicRecordListTableGUI.php";
			$table = new ilDataBibliographicRecordListTableGUI($this, $this->cmd);
			$html = $table->getHTML();
			$tpl->setContent($html);
			//Permanent Link
			$tpl->setPermanentLink("bibl", $this->object->getRefId());
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
		$ilAccess = $DIC['ilAccess'];
		$tpl = $DIC['tpl'];
		$lng = $DIC['lng'];
		if ($ilAccess->checkAccess('read', "", $this->object->getRefId())) {
			$file_path = $this->bibl_obj->getFileAbsolutePath();
			if ($file_path) {
				if (is_file($file_path)) {
					$path_array = explode(DIRECTORY_SEPARATOR, $file_path);
					$filename = $path_array[sizeof($path_array) - 1];
					require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');
					ilFileDelivery::deliverFileAttached($file_path, null, 'application/octet-stream');
					//					ilUtil::deliverFile($file_path, $filename);
				} else {
					ilUtil::sendFailure($lng->txt("file_not_found"));
					$this->showContent($this->bibl_obj);
				}
			}
		} else {
			ilUtil::sendFailure($this->lng->txt("no_permission"), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
	}


	public function showDetails() {
		global $DIC;
		$ilAccess = $DIC['ilAccess'];
		$tpl = $DIC['tpl'];
		$lng = $DIC['lng'];
		if ($ilAccess->checkAccess('read', "", $this->object->getRefId())) {
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
		$this->ctrl->redirect($this, 'view');
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
		$this->ctrl->redirect($this, 'view');
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
}

?>
