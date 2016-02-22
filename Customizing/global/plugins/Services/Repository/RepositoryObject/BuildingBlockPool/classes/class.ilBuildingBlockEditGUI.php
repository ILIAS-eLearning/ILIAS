<?php

class ilBuildingBlockEditGUI {
	protected $obj_id;
	protected $mode;
	protected $parent_obj;

	const EDIT_UNIT = "edit";
	const NEW_UNIT = "new";
	const UPDATE_UNIT = "update";
	const SAVE_UNIT = "save";
	const DELETE_UNIT = "delete";
	const MAX_TEXTAREA_LENGTH = 500;

	public function __construct($obj_id, $mode, $parent_obj) {
		global $ilCtrl, $lng;

		$this->obj_id = $obj_id;
		$this->mode = $mode;
		$this->parent_obj = $parent_obj;
		$this->gCtrl = $ilCtrl;
		$this->gLng = $lng;
	}

	public function getHtml($form_gui = null) {
		
		if($form_gui === null) {
			$form_gui = $this->initForm();
		}
		
		return $form_gui->getHtml();
	}

	protected function initForm() {
		require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		$amd_utils = gevAMDUtils::getInstance();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form_gui = new ilPropertyFormGUI();
		$this->gCtrl->setParameter($this->parent_obj,"bb_id",$this->obj_id);
		$form_gui->setFormAction($this->gCtrl->getFormAction($this->parent_obj));
		$this->gCtrl->setParameter($this->parent_obj,"bb_id",null);

		if($this->mode == self::EDIT_UNIT) {
			require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
			$bu_utils = gevBuildingBlockUtils::getInstance($this->obj_id);
			$bu_utils->loadData();

			$vals = array(
					 "obj_id" => $bu_utils->getId()
					,"title" => $bu_utils->getTitle()
					,"content" => $bu_utils->getContent()
					,"target" => $bu_utils->getTarget()
					,"is_wp_relevant" => $bu_utils->isWPRelevant()
					,"active" => $bu_utils->isActive()
					,"training_categories" => $bu_utils->getTrainingCategories()
					,"gdv_topic" => $bu_utils->getGDVTopic()
					,"topic" => $bu_utils->getTopic()
					,"dbv_topic" => $bu_utils->getDBVTopic()
					,"move_to_course" => $bu_utils->getMoveToCourseText()
				);

			$form_gui->setTitle($this->gLng ->txt("gev_dec_building_block_edit"));

			$tmplt_id = new ilHiddenInputGUI("obj_id");
			$tmplt_id->setValue($vals["obj_id"]);
			$form_gui->addItem($tmplt_id);

		}else {
			$vals = array(
					 "obj_id" => ""
					,"title" => ""
					,"content" => ""
					,"target" => ""
					,"is_wp_relevant" => false
					,"active" => false
					,"move_to_course" => "Ja"
				);
			$tmplt_id = new ilHiddenInputGUI("obj_id");
			$form_gui->addItem($tmplt_id);

			$form_gui->setTitle($this->gLng ->txt("gev_dec_building_block_new"));
		}

		/*************************
		* INFOS
		*************************/
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($this->gLng ->txt("gev_dec_building_block_base_data"));
		$form_gui->addItem($sec_l);

		$title = new ilTextInputGUI($this->gLng ->txt("gev_dec_building_block_create_title"), "frm_title");
		$title->setRequired(true);
		$title->setValue($vals["title"]);
		$title->setSize(50);
		$title->setMaxLength(100);
		$form_gui->addItem($title);

		$content = new ilTextAreaInputGUI($this->gLng ->txt("gev_dec_building_block_content"), "frm_content");
		$content->setValue($vals["content"]);
		$content->setRows(3);
		$content->setCols(48);
		$form_gui->addItem($content);

		$target = new ilTextAreaInputGUI($this->gLng ->txt("gev_dec_building_block_target"), "frm_target");
		$target->setValue($vals["target"]);
		$target->setRows(3);
		$target->setCols(48);
		$form_gui->addItem($target);

		/*************************
		* INHALT
		*************************/
		$content_section = new ilFormSectionHeaderGUI();
		$content_section->setTitle($this->gLng ->txt("gev_dec_training_content"));
		$form_gui->addItem($content_section);

		$training_cat = $amd_utils->getOptions(gevSettings::CRS_AMD_TOPIC);
		$cbx_group_training_cat = new ilCheckBoxGroupInputGUI($this->gLng ->txt("gev_dec_training_training_category"),"frm_training_category");

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
		$topic = new ilSelectInputGUI($this->gLng ->txt("gev_dec_training_topic"),"frm_topic");
		$options = array("" => "-") + $topic_options;
		$topic->setOptions($options);
		$topic->setRequired(true);
		if($vals["topic"]){
			$topic->setValue($vals["topic"]);
		}
		$form_gui->addItem($topic);

		$topic_options = $amd_utils->getOptions(gevSettings::CRS_AMD_DBV_HOT_TOPIC);
		$dbv_topic = new ilSelectInputGUI($this->gLng ->txt("gev_dec_training_dbv_topic"),"frm_dbv_topic");
		$options = array("" => "-") + $topic_options;
		$dbv_topic->setOptions($options);
		if($vals["dbv_topic"]){
			$dbv_topic->setValue($vals["dbv_topic"]);
		}
		$form_gui->addItem($dbv_topic);

		/*************************
		* BEWERTUNG
		*************************/
		$rating_section = new ilFormSectionHeaderGUI();
		$rating_section->setTitle($this->gLng ->txt("gev_dec_training_rating"));
		$form_gui->addItem($rating_section);

		$gdv_topic_options = $amd_utils->getOptions(gevSettings::CRS_AMD_GDV_TOPIC);
		$gdv_topic = new ilSelectInputGUI($this->gLng ->txt("gev_dec_training_gdv_topic"),"frm_gdv_topic");
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
		$rating_section->setTitle($this->gLng ->txt("gev_dec_activate"));
		$form_gui->addItem($rating_section);

		$active = new ilCheckboxInputGUI($this->gLng ->txt("gev_dec_building_block_active"), "frm_active");
		$active->setChecked($vals["active"]);
		$active->setInfo($this->gLng ->txt("gev_dec_building_block_active_desc"));
		$form_gui->addItem($active);

		/*************************
		* ÜBERNAHME IN KURS?
		*************************/
		$move_to_course_options = gevBuildingBlockUtils::getMoveToCourseOptions();
		$move_to_course = new ilSelectInputGUI($this->gLng ->txt("gev_dec_training_move_to_course"),"frm_move_to_course");
		$move_to_course->setOptions($move_to_course_options);
		if($vals["move_to_course"]){
			$move_to_course->setValue($vals["move_to_course"]);
		}
		$form_gui->addItem($move_to_course);


		if($this->obj_id !== null && $this->obj_id != "") {
			$form_gui->addCommandButton("updateBuildingBlock", $this->gLng ->txt("save"));
		} else {
			$form_gui->addCommandButton("saveBuildingBlock", $this->gLng ->txt("save"));
		}

		$form_gui->addCommandButton("cancelBuildingBlock", $this->gLng ->txt("cancel"));

		return $form_gui;
	}

	public function update() {
		$form = $this->initForm();
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->getHtml($form);
		}

		if(!$this->checkContentAndTargetInputLength($form)) {
			return $this->getHtml($form);
		}

		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		$bu_utils = gevBuildingBlockUtils::getInstance($this->obj_id);

		$bu_utils->setTitle($form->getInput("frm_title"));
		$bu_utils->setContent($form->getInput("frm_content"));
		$bu_utils->setTarget($form->getInput("frm_target"));
		$bu_utils->setIsActive($form->getInput("frm_active"));

		$bu_utils->setGDVTopic($form->getInput("frm_gdv_topic"));
		$bu_utils->setIsWPRelevant(($bu_utils->getGDVTopic() != ""));

		$training_category = $form->getInput("frm_training_category");
		$training_category = ($training_category !== null) ? $training_category : array();
		$bu_utils->setTrainingCategories($training_category);
		
		$bu_utils->setTopic($form->getInput("frm_topic"));
		$bu_utils->setDBVTopic($form->getInput("frm_dbv_topic"));
		$bu_utils->setMoveToCourse(($form->getInput("frm_move_to_course") == "Ja") ? 1 : 0);

		$bu_utils->update();

		$this->gCtrl->redirect($this->parent_obj);
	}

	public function save() {
		$form = $this->initForm();
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->getHtml($form);
		}

		if(!$this->checkContentAndTargetInputLength($form)) {
			return $this->getHtml($form);
		}

		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		$bu_utils = gevBuildingBlockUtils::getInstance(null);

		$bu_utils->setTitle($form->getInput("frm_title"));
		$bu_utils->setContent($form->getInput("frm_content"));
		$bu_utils->setTarget($form->getInput("frm_target"));
		$bu_utils->setIsActive($form->getInput("frm_active"));

		$bu_utils->setGDVTopic($form->getInput("frm_gdv_topic"));
		$bu_utils->setIsWPRelevant(($bu_utils->getGDVTopic() != ""));

		$training_category = $form->getInput("frm_training_category");
		$training_category = ($training_category !== null) ? $training_category : array();
		$bu_utils->setTrainingCategories($training_category);

		$bu_utils->setTopic($form->getInput("frm_topic"));
		$bu_utils->setDBVTopic($form->getInput("frm_dbv_topic"));
		$bu_utils->setMoveToCourse(($form->getInput("frm_move_to_course") == "Ja") ? 1 : 0);
		$bu_utils->setPoolId($this->parent_obj->object->getId());

		$bu_utils->save();

		$this->gCtrl->redirect($this->parent_obj);
	}

	public function deleteConfirm() {

		include_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirm = new ilConfirmationGUI();
		$this->gCtrl->setParameter($this->parent_obj, "bb_id", $this->obj_id);
		$confirm->setFormAction($this->gCtrl->getFormAction($this->parent_obj));
		$this->gCtrl->setParameter($this->parent_obj, "bb_id", null);

		$confirm->setHeaderText($this->gLng->txt("crsbook_admin_assign_confirm"));
		$confirm->setConfirm($this->gLng->txt("confirm"), "deleteConfirmedBuildingBlock");
		$confirm->setCancel($this->gLng->txt("cancel"), "showContent");
				
		return $confirm->getHTML();
	}

	public function delete() {
		require_once ("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
		gevBuildingBlockUtils::deleteBuildingBlock($this->obj_id);

		$this->gCtrl->redirect($this->parent_obj);
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
			$content->setAlert(sprintf($this->gLng->txt("gev_dec_training_text_to_long"),self::MAX_TEXTAREA_LENGTH));
		}

		$target_to_long = $this->isTextToLong($form->getInput("frm_learn_dest"));
		if($target_to_long) {
			$target = $form->getItemByPostVar("frm_learn_dest");
			$target->setAlert(sprintf($this->gLng->txt("gev_dec_training_text_to_long"),self::MAX_TEXTAREA_LENGTH));
		}

		if($target_to_long || $content_to_long) {
			return false;
		}

		return true;
	}
}