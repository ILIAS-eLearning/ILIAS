<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeTreeExplorerGUI.php");
require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
require_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
require_once("./Services/ContainerReference/classes/class.ilContainerSelectionExplorer.php");
require_once("./Modules/TrainingProgramme/classes/helpers/class.ilAsyncPropertyFormGUI.php");

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

	protected $modal_id;

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
		$this->modal_id = "tree_modal";

		$this->initTree();

		$lng->loadLanguageModule("prg");
	}

	protected function initTree() {
		$this->tree = new ilObjTrainingProgrammeTreeExplorerGUI($this->ref_id, $this->modal_id, "prg_tree", $this, 'view');
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
			case "save":
			case "cancel":
			case "getContainerSelectionExplorer":
				$content = $this->$cmd();
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeTreeGUI: ".
					"Command not supported: $cmd");
		}

		ilAsyncOutputHandler::handleAsyncOutput($content);
	}

	protected function view() {
		ilAccordionGUI::addJavaScript();
		ilAsyncPropertyFormGUI::initJs(true, './Modules/TrainingProgramme/templates/js/');

		$this->tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");

		$output = $this->tree->getHTML();
		$output .= $this->getModals();

		return $output;
	}

	public function cancel() {
		return ilAsyncOutputHandler::encodeAsyncResponse();
	}

	public function save_tree_order() {

	}

	public function create_new_ref() {

	}

	protected function getContainerSelectionExplorer($convert_to_string = true) {
		$create_leaf_form = new ilContainerSelectionExplorer($this->ctrl->getLinkTarget($this, 'create_new_ref', '', true));
		$create_leaf_form->setId("select_course_explorer");

		$ref_expand = ROOT_FOLDER_ID;
		if(isset($_GET['ref_repexpand'])) {
			$ref_expand = (int) $_GET['ref_repexpand'];
		}

		$create_leaf_form->setExpand($ref_expand);
		$create_leaf_form->setExpandTarget($this->ctrl->getLinkTarget($this,'getContainerSelectionExplorer'));
		$create_leaf_form->setAsynchExpanding(true);
		$create_leaf_form->setTargetGet('target_id');
		$create_leaf_form->setClickable('crs', true);
		$create_leaf_form->setTargetType('crs');
		$create_leaf_form->setOutput(0);

		if($convert_to_string)
			return $create_leaf_form->getOutput();
		else
			return $create_leaf_form;
	}

	protected function getCreationForm() {
		$tmp_obj = new ilObjTrainingProgrammeGUI();

		$create_node_form = $tmp_obj->getAsyncCreationForm();
		$create_node_form->setTitle("");
		$this->ctrl->setParameterByClass("ilobjtrainingprogrammegui", "new_type", "prg");
		$create_node_form->setFormAction($this->ctrl->getFormActionByClass("ilobjtrainingprogrammegui", "save"));

		if($create_node_form->isSubmitted()) {
			$create_node_form->setValuesByPost();
			$create_node_form->checkInput();
		}

		return $create_node_form;
	}

	protected function create() {
		$accordion = new ilAccordionGUI();

		$content_new_node = $this->getCreationForm()->getHTML();

		$accordion->addItem($this->lng->txt('prg_create_new_node'), $content_new_node);

		$content_new_leaf = $this->tpl->getMessageHTML($this->lng->txt('prg_please_select_a_course_for_creating_a_leaf'));
		$content_new_leaf .= $this->getContainerSelectionExplorer();

		$accordion->addItem($this->lng->txt('prg_create_new_leaf'), $content_new_leaf);

		$content = $accordion->getHTML();

		if($this->ctrl->isAsynch()) {
			$output_handler = new ilAsyncOutputHandler();
			$output_handler->setHeading($this->lng->txt("async_".$this->ctrl->getCmd()));
			$output_handler->setContent($content);
			$output_handler->terminate();
		}
	}

	protected function getModals() {
		$settings_modal = ilModalGUI::getInstance();
		$settings_modal->setId($this->modal_id);
		$settings_modal->setType(ilModalGUI::TYPE_LARGE);

		$this->ctrl->clearParameters($this);
		$this->tpl->addOnLoadCode('$("#'.$this->modal_id.'").training_programme_modal();');

		return $settings_modal->getHTML();
	}

	protected function getToolbar() {
		$save_order_btn = ilLinkButton::getInstance();
		$save_order_btn->setUrl('javascript: return false;');
		$save_order_btn->setCaption($this->lng->txt('save_tree_order'));

		$this->toolbar->addButtonInstance($save_order_btn);
	}


}