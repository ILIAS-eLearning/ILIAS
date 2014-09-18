<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once("./Modules/Bibliographic/classes/Admin/class.ilObjBibliographicAdminLibrariesFormGUI.php");
require_once("./Modules/Bibliographic/classes/Admin/class.ilObjBibliographicAdminTableGUI.php");
require_once("./Modules/Bibliographic/classes/Admin/class.ilBibliographicSetting.php");
/**
 * Bibliographic Administration Settings.
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * @ingroup ModulesBibliographic
 */
class ilObjBibliographicAdminLibrariesGUI {

	/**
	 * @var ilObjBibliographicAdminGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;


	/**
	 * Constructor
	 *
	 * @param ilObjBibliographicAdminGUI $parent_gui
	 */
	public function __construct($parent_gui) {
		global $lng, $ilCtrl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->parent_gui = $parent_gui;
	}


	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand() {
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd();
		switch ($cmd) {
			case 'view':
				$this->view();
				break;
			case 'add':
				$this->add();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'create':
				$this->create();
				break;
			case 'update':
				$this->update();
				break;
			case 'cancel':
				$this->cancel();
				break;
		}
	}


	public function view() {
		$a_table = $this->initTable();
		$this->parent_gui->tpl->setContent($a_table->getHTML());

		return true;
	}


	/**
	 * Init Table with library entries
	 *
	 * @access protected
	 */
	protected function initTable() {
		$table = new ilObjBibliographicAdminTableGUI($this, 'library');
		$settings = ilBibliographicSetting::getAll();
		$result = array();
		foreach ($settings as $set) {
			$result[] = array(
				"id" => $set->getId(),
				"name" => $set->getName(),
				"url" => $set->getBaseUrl(),
				"img" => $set->getImageUrl()
			);
		}
		$table->setData($result);

		return $table;
	}


	/**
	 * add library
	 */
	public function add() {
		$form = new ilObjBibliographicAdminLibrariesFormGUI($this, new ilBibliographicSetting());
		$this->parent_gui->tpl->setContent($form->getHTML());
		$this->parent_gui->tabs_gui->setTabActive('settings');
	}


	/**
	 * delete library
	 */
	public function delete() {
		global $ilDB;
		$ilDB->manipulate("DELETE FROM il_bibl_settings WHERE id = " . $ilDB->quote($_REQUEST["lib_id"], "integer"));
		$this->ctrl->redirect($this, 'view');
	}


	/**
	 * cancel
	 */
	public function cancel() {
		$this->ctrl->redirect($this, 'view');
	}


	/**
	 * save changes in library
	 */
	public function update() {
		$form = new ilObjBibliographicAdminLibrariesFormGUI($this, new ilBibliographicSetting($_REQUEST["lib_id"]));
		$form->setValuesByPost();
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
			$this->ctrl->redirect($this, 'view');
		}
		$this->parent_gui->tpl->setContent($form->getHTML());
	}


	/**
	 * create library
	 */
	public function create() {
		$form = new ilObjBibliographicAdminLibrariesFormGUI($this, new ilBibliographicSetting());
		$form->setValuesByPost();
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
			$this->ctrl->redirect($this, 'view');
		}
		$this->parent_gui->tpl->setContent($form->getHTML());
	}


	/**
	 * edit library
	 */
	public function edit() {
		$this->ctrl->saveParameter($this, 'lib_id');
		$form = new ilObjBibliographicAdminLibrariesFormGUI($this, new ilBibliographicSetting($_REQUEST["lib_id"]));
		$this->parent_gui->tpl->setContent($form->getHTML());
	}
}

?>