<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeTreeExplorerGUI.php");
require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
require_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
require_once("./Services/ContainerReference/classes/class.ilContainerSelectionExplorer.php");
require_once("./Modules/TrainingProgramme/classes/helpers/class.ilAsyncPropertyFormGUI.php");
require_once('./Services/Container/classes/class.ilContainerSorting.php');
require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
require_once("./Modules/TrainingProgramme/classes/helpers/class.ilAsyncNotifications.php");

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

	protected $async_output_handler;

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
		$this->async_output_handler = new ilAsyncOutputHandler();

		$this->initTree();

		$lng->loadLanguageModule("prg");
	}

	protected function initTree() {
		$this->tree = new ilObjTrainingProgrammeTreeExplorerGUI($this->ref_id, $this->modal_id, "prg_tree", $this, 'view');

		$js_url = rawurldecode($this->ctrl->getLinkTarget($this, 'saveTreeOrder', '', true, false));
		$this->tree->addJsConf('save_tree_url', $js_url);
		$this->tree->addJsConf('save_button_id', 'save_order_button');
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
			case "delete":
			case "confirmedDelete":
			case "cancelDelete":
			case "getContainerSelectionExplorer":
			case "saveTreeOrder":
			case "createNewLeaf":

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
		ilAsyncPropertyFormGUI::initJs(true);

		$this->tpl->addJavaScript("./Services/UIComponent/Explorer/js/ilExplorer.js");

		$output = $this->tree->getHTML();
		$output .= $this->initAsyncUIElements();

		return $output;
	}

	protected function cancel() {
		return ilAsyncOutputHandler::encodeAsyncResponse();
	}

	protected function saveTreeOrder() {
		$this->checkAccess('write');

		if(!isset($_POST['tree']) || is_null(json_decode($_POST['tree']))) {
			throw new ilException("There is no tree data to save");
		}

		$sorting = ilContainerSorting::_getInstance(ilObject::_lookupObjectId($this->ref_id));
		$this->storeTreeOrder(json_decode($_POST['tree']), $sorting);


		return ilAsyncOutputHandler::encodeAsyncResponse(array('success'=>true, 'message'=>$this->lng->txt('prg_saved_order_successful')));
	}

	protected function storeTreeOrder($nodes, $container_sorting, $parent_ref_id = null) {
		$sorting_position = array();
		$position_count = 10;
		$parent_node = ($parent_ref_id == null)? ilObjTrainingProgramme::getInstanceByRefId($this->ref_id) : ilObjTrainingProgramme::getInstanceByRefId($parent_ref_id);
		foreach($nodes as $node) {
			$id = $node->attr->id;
			$id = substr($id, strrpos($id, "_")+1);
			$sorting_position[$id] = $position_count;
			$position_count+= 10;

			$node_obj = ilObjTrainingProgramme::getInstanceByRefId($id);
			$node_obj->moveTo($parent_node);

			if(isset($node->children)) {
				$this->storeTreeOrder($node->children, ilContainerSorting::_getInstance(ilObject::_lookupObjectId($id)), $id);
			}
		}
		$container_sorting->savePost($sorting_position);
	}

	protected function createNewLeaf() {
		//TODO: implement leaf creation
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

		$this->async_output_handler->setHeading($this->lng->txt("async_".$this->ctrl->getCmd()));
		$this->async_output_handler->setContent($content);
		$this->async_output_handler->terminate();
	}

	protected function delete() {
		global $ilSetting;

		$this->checkAccess("write");

		if(!isset($_GET['ref_id'], $_GET['item_ref_id'])) {
			throw new ilException("Nothing to delete!");
		}

		$element_ref_id = $_GET['ref_id'];

		$cgui = new ilConfirmationGUI();

		$msg = $this->lng->txt("info_delete_sure");

		if (!$ilSetting->get('enable_trash'))
		{
			$msg .= "<br/>".$this->lng->txt("info_delete_warning_no_trash");
		}
		$cgui->setFormAction($this->ctrl->getFormAction($this, 'confirmedDelete', '', true));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");
		$cgui->setFormName('async_form');

		$obj_id = ilObject::_lookupObjectId($element_ref_id);
		$type = ilObject::_lookupType($obj_id);
		$title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'),$obj_id);
		$alt = $this->lng->txt("icon")." ".$this->lng->txt("obj_".$type);

		$cgui->addItem("id[]", $element_ref_id, $title,
			ilObject::_getIcon($obj_id, "small", $type),
			$alt);
		$cgui->addHiddenItem('item_ref_id', $_GET['item_ref_id']);

		$content = $cgui->getHTML();

		$this->async_output_handler->setHeading($msg);
		$this->async_output_handler->setContent($content);
		$this->async_output_handler->terminate();
	}

	protected function confirmedDelete() {
		$this->checkAccess("write");

		if(!isset($_POST['id'], $_POST['item_ref_id'])) {
			throw new ilException("No item select for deletion!");
		}

		$ids = $_POST['id'];
		$current_node = $_POST['item_ref_id'];
		$result = true;
		foreach($ids as $id) {
			$obj = ilObjTrainingProgramme::getInstanceByRefId($id);

			//check if you are not deleting a parent element of the current element
			$children_of_node = ilObjTrainingProgramme::getAllChildren($obj->getRefId());
			$get_ref_ids = function($obj) { return $obj->getRefId(); };

			$children_of_node = array_map($get_ref_ids, $children_of_node);

			if($current_node != $id && $obj->getRoot() != null && !in_array($current_node, $children_of_node)) {
				if($obj->delete()) {
					$msg = $this->lng->txt("prg_deleted_safely");
				} else {
					$result = false;
				}
			} else {
				$msg = $this->lng->txt("prg_not_allowed_node_to_delete");
				$result = false;
			}
		}

		return ilAsyncOutputHandler::encodeAsyncResponse(array('success'=>$result, 'message'=>$msg));
	}

	protected function cancelDelete() {
		return ilAsyncOutputHandler::encodeAsyncResponse();
	}

	protected function initAsyncUIElements() {
		$settings_modal = ilModalGUI::getInstance();
		$settings_modal->setId($this->modal_id);
		$settings_modal->setType(ilModalGUI::TYPE_LARGE);
		$this->tpl->addOnLoadCode('$("#'.$this->modal_id.'").training_programme_modal();');

		$content =  $settings_modal->getHTML();

		$notifications = new ilAsyncNotifications();
		$notifications->addJsConfig('events', array('success'=>array('training_programme-show_success')));
		$notifications->initJs();

		return $content;
	}

	protected function getToolbar() {
		$save_order_btn = ilLinkButton::getInstance();
		$save_order_btn->setId('save_order_button');
		$save_order_btn->setUrl("javascript:void(0);");
		$save_order_btn->setOnClick("$('body').trigger('training_programme-save_order');");
		$save_order_btn->setCaption($this->lng->txt('prg_save_tree_order'));

		$this->toolbar->addButtonInstance($save_order_btn);
	}

	protected function checkAccess($permission) {
		return $this->access->checkAccess($permission, '', $this->ref_id);
	}


}