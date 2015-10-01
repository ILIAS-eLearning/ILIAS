<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingBuildingBlockAdminTableGUI.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class gevDecentralTrainingBuildingBlockAdminGUI {
	const NEW_UNIT = "new";
	const EDIT_UNIT = "edit";
	const MAX_TEXTAREA_LENGTH = 500;

	protected $obj_id = null;

	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilLog, $ilDB;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->log = $ilLog;
		$this->db = $ilDB;
		$this->search_form = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$in_search = $cmd == "search";
		$in_search = true;

		switch($cmd) {
			case "delete":
				$this->renderConfirm();
				break;
			case "deleteBuildingBlock":
				$this->deleteBuildingBlock($_POST["obj_id"]);
				$this->render($in_serach);
				break;
			case "add":
				$this->newBuildingBlock();
				break;
			case "edit":
				$this->editBuildingBlock();
				break;
			case "update":
				$this->updateBuildingBlock();
				break;
			case "save":
				$this->saveBuildingBlock();
				break;
			default:
				$this->render($in_search);
		}
		
	}

	private function deleteBuildingBlock($a_obj_id) {
		gevBuildingBlockUtils::deleteBuildingBlock($a_obj_id);

		return;
	}

	private function renderConfirm() {

		$this->determineObjId();

		include_once "./Services/User/classes/class.ilUserUtil.php";
		include_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this, "assignMembers"));
		$confirm->setHeaderText($this->lng->txt("gev_dec_building_block_delete_confirm"));
		$confirm->setConfirm($this->lng->txt("confirm"), "deleteBuildingBlock");
		$confirm->setCancel($this->lng->txt("cancel"), "view");
		
		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		$bu_utils = gevBuildingBlockUtils::getInstance($this->obj_id);
		$bu_utils->loadData();

		$confirm->addItem("obj_id",
				$this->obj_id,
				$bu_utils->getTitle()
			);
		
		$this->tpl->setContent($confirm->getHTML());
	}

	protected function render($a_in_search = false) {
		$spacer = new catHSpacerGUI();

		$spacer_out = $spacer->render();
		
		$form = $this->getSearchForm();

		if ($a_in_search) {
			// search params are passed via form post
			if (isset($_POST["cmd"])) {
				$form->setValuesByPost();
				if ($form->checkInput()) {
					$search_opts = $form->getInputs();
					// clean empty or "all"-options
					foreach($search_opts as $key => $value) {
						if (!$value) {
							unset($search_opts[$key]);
						}
					}
				}
				else {
					$search_opts = array();
				}
			}
			// search params are passed via get in table nav links 
			else {
				$search_opts = array();
				foreach ($form->getItems() as $item) {
					$postvar = $item->getPostVar();
					// special detreatment for period, see below
					
					$val = $_GET[$postvar];
					if ($val) {
						$search_opts[$postvar] = $val;
					}
				}
			}
			
			// click on table nav should lead to search again.
			$this->ctrl->setParameter($this, "cmd", "search");
		}
		else {
			$search_opts = array();
		}

		// this is needed to pass the search parameter via the sorting
		// links of the table.
		foreach( $search_opts as $key => $value) {
			$this->ctrl->setParameter($this, $key, urlencode($value));
		}
		
		$crs_tbl = new gevDecentralTrainingBuildingBlockAdminTableGUI($search_opts, $this);
		
		$crs_tbl->setTitle("gev_dec_building_block_title")
				->setSubtitle("gev_dec_building_block_sub_title")
				->setImage("GEV_img/ico-head-search.png")
				//->setCommand("gev_crs_srch_limit", "-")
				->addCommandButton("add",$this->lng->txt("add")); // TODO: set this properly

		$this->tpl->setContent($this->renderSearch().$crs_tbl->getHTML());
	}

	protected function renderSearch() {
		$form = $this->getSearchForm();
		
		return $form->getHTML();
	}
	
	protected function getSearchForm() {
		if ($this->search_form !== null) {
			return $this->search_form;
		}
		
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		


		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_search_form.html", "Services/GEV/Desktop");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton("search", $this->lng->txt("search"));
		
		$form->setId('gevCourseSearchForm');
		
		global $tpl;
		// http://www.jacklmoore.com/colorbox/
		$tpl->addJavaScript("Services/CaTUIComponents/js/colorbox-master/jquery.colorbox-min.js");


		$search_title = new catTitleGUI("gev_block_unit_search", "gev_block_unit_search_desc", "GEV_img/ico-head-search.png");
		$form->setTitle($search_title->render());

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$form->addItem($title);

		$content = new ilTextInputGUI($this->lng->txt("gev_dec_building_block_content"), "content");
		$content->setInfo($this->lng->txt("gev_block_unit_like_search"));
		$form->addItem($content);
	
		$learn_dest = new ilTextInputGUI($this->lng->txt("gev_dec_building_block_learn_dest"), "learning_dest");
		$learn_dest->setInfo($this->lng->txt("gev_block_unit_like_search"));
		$form->addItem($learn_dest);

		$is_wp_relevant = new ilSelectInputGUI($this->lng->txt("gev_dec_building_block_is_wp_relevant"), "is_wp_relevant");
		$option = array("-1"=>"-", "ja"=>"Ja", "nein"=>"Nein");
		$is_wp_relevant->setOptions($option);
		$form->addItem($is_wp_relevant);

		$is_active = new ilSelectInputGUI($this->lng->txt("gev_dec_building_block_active"), "is_active");
		$option2 = array("-1"=> "-", "na"=>"Ja", "nein"=>"Nein");
		$is_active->setOptions($option2);
		$form->addItem($is_active);

		$this->search_form = $form;
		return $form;
	}

	protected function determineObjId() {
		if(isset($_GET["obj_id"])) {
			$this->obj_id = $_GET["obj_id"];
		}

		if(isset($_POST["obj_id"])) {
			$this->obj_id = $_POST["obj_id"];
		}
	}

	protected function newBuildingBlock($a_form = null) {
		$this->determineObjId();

		$form = ($a_form === null) ? $this->initForm(self::NEW_UNIT) : $a_form;
		$this->tpl->setContent($form->getHtml());
	}

	protected function editBuildingBlock($a_form = null) {
		$this->determineObjId();

		$form = ($a_form === null) ? $this->initForm(self::EDIT_UNIT) : $a_form;
		$this->tpl->setContent($form->getHtml());
	}

	protected function initForm($a_mode) {
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$amd_utils = gevAMDUtils::getInstance();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this));

		if($a_mode == self::EDIT_UNIT) {
			require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
			$bu_utils = gevBuildingBlockUtils::getInstance($this->obj_id);
			$bu_utils->loadData();

			$vals = array(
					 "obj_id" => $bu_utils->getId()
					,"title" => $bu_utils->getTitle()
					,"content" => $bu_utils->getContent()
					,"learning_dest" => $bu_utils->getLearningDestination()
					,"is_wp_relevant" => $bu_utils->isWPRelevant()
					,"active" => $bu_utils->isActive()
					,"training_categories" => $bu_utils->getTrainingCategories()
					,"gdv_topic" => $bu_utils->getGDVTopic()
					,"topic" => $bu_utils->getTopic()
					,"dbv_topic" => $bu_utils->getDBVTopic()
					,"move_to_course" => $bu_utils->getMoveToCourseText()
				);

			$form_gui->setTitle($this->lng->txt("gev_dec_building_block_edit"));

			$tmplt_id = new ilHiddenInputGUI("obj_id");
			$tmplt_id->setValue($vals["obj_id"]);
			$form_gui->addItem($tmplt_id);

		}else {
			$vals = array(
					 "obj_id" => ""
					,"title" => ""
					,"content" => ""
					,"learning_dest" => ""
					,"is_wp_relevant" => false
					,"active" => false
					,"move_to_course" => "Ja"
				);
			$tmplt_id = new ilHiddenInputGUI("obj_id");
			$form_gui->addItem($tmplt_id);

			$form_gui->setTitle($this->lng->txt("gev_dec_building_block_new"));
		}

		/*************************
		* INFOS
		*************************/
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($this->lng->txt("gev_dec_building_block_base_data"));
		$form_gui->addItem($sec_l);

		$title = new ilTextInputGUI($this->lng->txt("gev_dec_building_block_create_title"), "frm_title");
		$title->setRequired(true);
		$title->setValue($vals["title"]);
		$title->setSize(50);
		$title->setMaxLength(100);
		$form_gui->addItem($title);

		$content = new ilTextAreaInputGUI($this->lng->txt("gev_dec_building_block_content"), "frm_content");
		$content->setValue($vals["content"]);
		$content->setRows(3);
		$content->setCols(48);
		$content->setRequired(true);
		$form_gui->addItem($content);

		$learn_dest = new ilTextAreaInputGUI($this->lng->txt("gev_dec_building_block_learn_dest"), "frm_learn_dest");
		$learn_dest->setValue($vals["learning_dest"]);
		$learn_dest->setRows(3);
		$learn_dest->setCols(48);
		$learn_dest->setRequired(true);
		$form_gui->addItem($learn_dest);

		/*************************
		* INHALT
		*************************/
		$content_section = new ilFormSectionHeaderGUI();
		$content_section->setTitle($this->lng->txt("gev_dec_training_content"));
		$form_gui->addItem($content_section);

		$training_cat = $amd_utils->getOptions(gevSettings::CRS_AMD_TOPIC);
		$cbx_group_training_cat = new ilCheckBoxGroupInputGUI($this->lng->txt("gev_dec_training_training_category"),"frm_training_category");
		$cbx_group_training_cat->setRequired(true);

		foreach($training_cat as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			$cbx_group_training_cat->addOption($option);
		}

		if($vals["training_categories"]) {
			$cbx_group_training_cat->setValue($vals["training_categories"]);
		}
		$form_gui->addItem($cbx_group_training_cat);

		$topic_options = gevBuildingBlockUtils::getAllPossibleTopics();
		$topic = new ilSelectInputGUI($this->lng->txt("gev_dec_training_topic"),"frm_topic");
		$options = array("" => "-") + $topic_options;
		$topic->setOptions($options);
		$topic->setRequired(true);
		if($vals["topic"]){
			$topic->setValue($vals["topic"]);
		}
		$form_gui->addItem($topic);

		$topic_options = $amd_utils->getOptions(gevSettings::CRS_AMD_DBV_HOT_TOPIC);
		$dbv_topic = new ilSelectInputGUI($this->lng->txt("gev_dec_training_dbv_topic"),"frm_dbv_topic");
		$options = array("" => "-") + $topic_options;
		$dbv_topic->setOptions($options);
		$dbv_topic->setRequired(true);
		if($vals["dbv_topic"]){
			$dbv_topic->setValue($vals["dbv_topic"]);
		}
		$form_gui->addItem($dbv_topic);

		/*************************
		* BEWERTUNG
		*************************/
		$rating_section = new ilFormSectionHeaderGUI();
		$rating_section->setTitle($this->lng->txt("gev_dec_training_rating"));
		$form_gui->addItem($rating_section);

		$gdv_topic_options = $amd_utils->getOptions(gevSettings::CRS_AMD_GDV_TOPIC);
		$gdv_topic = new ilSelectInputGUI($this->lng->txt("gev_dec_training_gdv_topic"),"frm_gdv_topic");
		$options = array("" => "-") + $gdv_topic_options;
		$gdv_topic->setOptions($options);
		if($vals["gdv_topic"]){
			$gdv_topic->setValue($vals["gdv_topic"]);
		}
		$form_gui->addItem($gdv_topic);

		/*************************
		* AKTIVE
		*************************/
		$rating_section = new ilFormSectionHeaderGUI();
		$rating_section->setTitle($this->lng->txt("gev_dec_activate"));
		$form_gui->addItem($rating_section);

		$active = new ilCheckboxInputGUI($this->lng->txt("gev_dec_building_block_active"), "frm_active");
		$active->setChecked($vals["active"]);
		$active->setInfo($this->lng->txt("gev_dec_building_block_active_desc"));
		$form_gui->addItem($active);

		/*************************
		* ÜBERNAHME IN KURS?
		*************************/
		$move_to_course_optins = gevBuildingBlockUtils::getMoveToCourseOptions();
		$move_to_course = new ilSelectInputGUI($this->lng->txt("gev_dec_training_move_to_course"),"frm_move_to_course");
		$move_to_course->setOptions($move_to_course_optins);
		if($vals["move_to_course"]){
			$move_to_course->setValue($vals["move_to_course"]);
		}
		$form_gui->addItem($move_to_course);


		if($this->obj_id !== null && $this->obj_id != "") {
			$form_gui->addCommandButton("update", $this->lng->txt("save"));
		} else {
			$form_gui->addCommandButton("save", $this->lng->txt("save"));
		}

		$form_gui->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form_gui;
	}

	protected function updateBuildingBlock() {
		$this->determineObjId();
		$form = $this->initForm(self::NEW_UNIT);

		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->editBuildingBlock($form);
		}

		if(!$this->checkContentAndTargetInputLength($form)) {
			return $this->editBuildingBlock($form);
		}

		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		$bu_utils = gevBuildingBlockUtils::getInstance($this->obj_id);

		$bu_utils->setTitle($form->getInput("frm_title"));
		$bu_utils->setContent($form->getInput("frm_content"));
		$bu_utils->setLearningDestination($form->getInput("frm_learn_dest"));
		$bu_utils->setIsActice($form->getInput("frm_active"));

		$bu_utils->setGDVTopic($form->getInput("frm_gdv_topic"));
		$bu_utils->setIsWPRelevant(($bu_utils->getGDVTopic() != ""));

		$bu_utils->setTraingCategories($form->getInput("frm_training_category"));
		$bu_utils->setTopic($form->getInput("frm_topic"));
		$bu_utils->setDBVTopic($form->getInput("frm_dbv_topic"));
		$bu_utils->setMoveToCourse(($form->getInput("frm_move_to_course") == "Ja") ? 1 : 0);

		$bu_utils->update();

		$this->render($in_search);
	}

	protected function saveBuildingBlock() {
		$this->determineObjId();
		$form = $this->initForm(self::NEW_UNIT);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->newBuildingBlock($form);
		}

		if(!$this->checkContentAndTargetInputLength($form)) {
			return $this->newBuildingBlock($form);
		}

		$newId = $this->db->nextId("dct_building_block");
		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		$bu_utils = gevBuildingBlockUtils::getInstance($newId);

		$bu_utils->setTitle($form->getInput("frm_title"));
		$bu_utils->setContent($form->getInput("frm_content"));
		$bu_utils->setLearningDestination($form->getInput("frm_learn_dest"));
		$bu_utils->setIsActice($form->getInput("frm_active"));

		$bu_utils->setGDVTopic($form->getInput("frm_gdv_topic"));
		$bu_utils->setIsWPRelevant(($bu_utils->getGDVTopic() != ""));

		$bu_utils->setTraingCategories($form->getInput("frm_training_category"));
		$bu_utils->setTopic($form->getInput("frm_topic"));
		$bu_utils->setDBVTopic($form->getInput("frm_dbv_topic"));
		$bu_utils->setMoveToCourse(($form->getInput("frm_move_to_course") == "Ja") ? 1 : 0);

		$bu_utils->save();

		$this->render($in_search);
	}

	protected function isTextToLong($text) {
		if(strlen($text) > self::MAX_TEXTAREA_LENGTH) {
			return true;
		}

		return false;
	}

	protected function checkContentAndTargetInputLength($form) {

		$content_to_long = $this->isTextToLong($form->getInput("frm_content"));
		if($content_to_long) {
			$content = $form->getItemByPostVar("frm_content");
			$content->setAlert(sprintf($this->lng->txt("gev_dec_training_text_to_long"),self::MAX_TEXTAREA_LENGTH));
		}

		$target_to_long = $this->isTextToLong($form->getInput("frm_learn_dest"));
		if($target_to_long) {
			$target = $form->getItemByPostVar("frm_learn_dest");
			$target->setAlert(sprintf($this->lng->txt("gev_dec_training_text_to_long"),self::MAX_TEXTAREA_LENGTH));
		}

		if($target_to_long || $content_to_long) {
			return false;
		}

		return true;
	}
}

?>
