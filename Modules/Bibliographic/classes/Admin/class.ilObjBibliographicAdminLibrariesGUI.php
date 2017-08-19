<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		global $DIC;
		$lng = $DIC['lng'];
		$ilCtrl = $DIC['ilCtrl'];
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
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
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


	/**
	 * @global $ilToolbar ilToolbarGUI;
	 *
	 * @return bool
	 */
	public function view() {
		global $DIC;
		$ilToolbar = $DIC['ilToolbar'];
		/**
		 * @var $ilToolbar ilToolbarGUI;
		 */
		$b = ilLinkButton::getInstance();
		$b->setCaption('add');
		$b->setUrl($this->ctrl->getLinkTarget($this, 'add'));
		$b->setPrimary(true);
		$ilToolbar->addButtonInstance($b);
		$a_table = $this->initTable();
		$this->parent_gui->tpl->setContent($a_table->getHTML());

		return true;
	}


	/**
	 * Init Table with library entries
	 *
	 * @access protected
	 * @return ilObjBibliographicAdminTableGUI
	 */
	protected function initTable() {
		$table = new ilObjBibliographicAdminTableGUI($this, 'library');
		$settings = ilBibliographicSetting::getAll();
		$result = array();
		foreach ($settings as $set) {
			$result[] = array(
				"id"   => $set->getId(),
				"name" => $set->getName(),
				"url"  => $set->getUrl(),
				"img"  => $set->getImg(),
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
		$this->parent_gui->getTabsGui()->setTabActive('settings');
	}


	/**
	 * delete library
	 */
	public function delete() {
		$ilBibliographicSetting = new ilBibliographicSetting($_REQUEST["lib_id"]);
		$ilBibliographicSetting->delete();
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
