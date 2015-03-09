<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeTreeExplorerGUI.php");
require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");

/**
 * Class ilTrainingProgrammeTreeGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjTrainingProgrammeTreeGUI {
	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @var ilTemplate
	 */
	public $tpl;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilObjTrainingProgramme
	 */
	public $object;
	/**
	 * @var ilLocatorGUI
	 */
	protected $locator;

	/**
	 * @var ilLog
	 */
	protected $log;

	/**
	 * @var Ilias
	 */
	public $ilias;

	/**
	 * @var ilLng
	 */
	public $lng;

	protected $ref_id;
	/**
	 * @var ilObjTrainingProgrammeTreeExplorerGUI
	 */
	protected $tree;

	/*
	 * @var ilToolbar
	 */
	public $toolbar;

	public function __construct($a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;

		$this->ref_id = $a_ref_id;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->access = $ilAccess;
		$this->locator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->log = $ilLog;
		$this->ilias = $ilias;
		$this->lng = $lng;

		$this->initTree();

		$lng->loadLanguageModule("prg");
	}

	protected function initTree() {
		$this->tree = new ilObjTrainingProgrammeTreeExplorerGUI($this->ref_id, "prg_tree", $this, 'view');
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();

		$this->getToolbar();

		if ($cmd == "") {
			$cmd = "view";
		}

		if($this->tree->handleCommand()) {
			exit();
		}

		switch ($cmd) {
			case "view":
			case "create":
				$cont = $this->$cmd();
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeTreeGUI: ".
					"Command not supported: $cmd");
		}

		$this->tpl->setContent($cont);
	}

	protected function view() {
		$output = $this->tree->getHTML();
		$output .= $this->getModals();

		return $output;
	}

	protected function create() {
		$create_form = new ilObjTrainingProgrammeGUI();
		$create_form = $create_form->getCreationForm();
		//$create_form->setFormAction($this->ctrl->getFormAction($this, "save", "", true));

		$content = $create_form->getHTML();

		if($this->ctrl->isAsynch()) {
			$output_handler = new ilAsyncOutputHandler();
			$output_handler->setHeading($this->lng->txt("async_".$this->ctrl->getCmd()));
			$output_handler->setContent($content);
			$output_handler->terminate();
		}
	}

	protected function getModals() {
		$settings_modal = ilModalGUI::getInstance();
		$settings_modal->setId('settings_modal');
		$settings_modal->setType(ilModalGUI::TYPE_LARGE);

		return $settings_modal->getHTML();
	}

	protected function getToolbar() {
		$save_order_btn = ilLinkButton::getInstance();
		$save_order_btn->setUrl('javascript: return false;');
		$save_order_btn->setCaption($this->lng->txt('save_tree_order'));

		$this->toolbar->addButtonInstance($save_order_btn);
	}


}