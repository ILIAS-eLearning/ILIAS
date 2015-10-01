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
require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
require_once("Services/CaTUIComponents/classes/class.catPropertyFormTplGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseBuildingBlockTableGUI.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
include_once("Services/jQuery/classes/class.iljQueryUtil.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");

class gevDecentralTrainingCourseCreatingBuildingBlock2GUI {
	const MINUTE_STEP_SIZE = 15;

	public function __construct($a_crs_obj_id, $a_crs_request_id = null, $no_changes = false, $show_cmd_buttons = true) {
		global $lng, $ilCtrl, $tpl, $ilLog, $ilUser, $ilAppEventHandler;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->log = $ilLog;
		$this->app_event_handler = $ilAppEventHandler;
		$this->search_form = null;
		$this->current_user = $ilUser;
		$this->no_changes = $no_changes;
		$this->show_cmd_buttons = $show_cmd_buttons;

		$this->usr_utils = gevUserUtils::getInstance($this->current_user->getId());

		if($a_crs_obj_id !== null) {
			$this->crs_obj_id = $a_crs_obj_id;
			$this->crs_utils = gevCourseUtils::getInstance($this->crs_obj_id);
		}

		$this->crs_ref_id = ($a_crs_obj_id === null) ? null : gevCourseUtils::getInstance($a_crs_obj_id)->getRefId();
		$this->crs_request_id = $a_crs_request_id;
		$this->dctl_utils = gevDecentralTrainingUtils::getInstance();

		$this->delete_image = '<img src="'.ilUtil::getImagePath("gev_cancel_action.png").'" />';
		//$this->edit_image = '<img src="'.ilUtil::getImagePath("GEV_img/ico-edit.png").'" />';

		$this->tpl->getStandardTemplate();
		$this->tpl->addJavaScript("Services/GEV/DecentralTrainings/js/dct_change_possible_blocks.js");
		$this->tpl->addJavaScript("Services/GEV/DecentralTrainings/js/dct_disable_mail_preview.js");
		$this->tpl->addJavaScript("Services/CaTUIComponents/js/colorbox-master/jquery.colorbox-min.js");

		iljQueryUtil::initjQuery();

	}

	public function executeCommand() {
		$this->determineObjId();
		$this->determineCrsRef();
		$this->determineCrsRequestId();
		$cmd = $this->ctrl->getCmd();
		$in_search = true;

		switch($cmd) {
			case "toAddCrsBuildingBlock":
			case "toSaveTrainingSettings":
			case "toDeleteCrsBuildingBlock":
			case "toUpdateBuildingBlock":
			case "toCancleCreation":
			case "toSaveRequest":
			case "showOpenRequests":
			case "toTEP";
			case "toChangeCourseData":
			case "toDownloadPDF":
			case "backFromBooking":
				$this->$cmd();
				break;
			default:
				$this->render();
		}
	}

	protected function render() {
		$html = $this->renderTitleAndLegend();
		if(!$this->show_cmd_buttons) {
			$html .= $this->renderPDFDownload();
		}
		$html .= $this->createMailPreview();
		$html .= $this->renderNavigation();
		$html .= $this->renderSpacer();
		$html .= $this->renderTrainingSettings();
		$html .= $this->renderSpacer("","<hr />");
		$html .= $this->renderAddBuldingBlock();
		$html .= $this->renderSpacer(20,"<hr />");
		$html .= $this->renderBlockTable();
		$html .= $this->renderSpacer();
		$html .= $this->renderNavigation();
		$html .= $this->renderIdentification();

		$this->tpl->setContent($html);
	}

	protected function renderTitleAndLegend() {
		$title = new catTitleGUI();
		$title ->setTitle("gev_dec_training_add_buildingblocks")
				->setSubtitle("gev_dec_crs_creation_building_block_sub_title")
				->setImage("GEV_img/ico-head-search.png")
				->setCommand("gev_dec_mail_preview", "-");

		$legend = new catLegendGUI();
		$legend->addItem($this->delete_image, "gev_dec_building_block_delete");
			   //->addItem($this->edit_image, "gev_dec_building_block_edit");
		$title->setLegend($legend);

		return  $title->render();
	}

	protected function renderNavigation() {
		if(!$this->show_cmd_buttons) {
			return "";
		}

		$this->ctrl->setParameterByClass("gevDecentralTrainingCourseCreatingBuildingBlock2GUI","crs_request_id",$this->crs_request_id);
		$this->ctrl->setParameterByClass("gevDecentralTrainingCourseCreatingBuildingBlock2GUI","crs_ref_id",$this->crs_ref_id);

		$form_navi = new catPropertyFormGUI();

		if($this->crs_obj_id === null && $this->crs_request_id !== null) {
			$form_navi->addCommandButton("toSaveRequest", $this->lng->txt("gev_dec_training_save_request"));
		}
		
		$form_navi->addCommandButton("toChangeCourseData", $this->lng->txt("gev_dec_training_change_settings"));

		if($this->crs_obj_id === null && $this->crs_request_id !== null) {
			$form_navi->addCommandButton("toCancleCreation", $this->lng->txt("cancel"));
		}

		if($this->crs_obj_id !== null) {
			$form_navi->addCommandButton("toTEP", $this->lng->txt("close"));
		}
		
		$form_navi->setFormAction($this->ctrl->getFormAction($this));
		$form_navi->setShowTopButtons(false);
		$form_navi->setId("dct_navi");

		$html = $form_navi->getHTML();

		$this->ctrl->clearParametersByClass("gevDecentralTrainingCourseCreatingBuildingBlock2GUI");

		return $html;
	}

	protected function renderTrainingSettings() {
		$form_training_settings = new catPropertyFormTplGUI();
		$form_training_settings->setTemplate("tpl.dct_training_settings.html","Services/GEV/DecentralTrainings");
		$form_training_settings->getTemplate()->setVariable("BTN_CMD","toSaveTrainingSettings");
		$form_training_settings->getTemplate()->setVariable("BTN_VALUE",$this->lng->txt("gev_dec_training_save_changes"));
		$form_training_settings->setFormAction($this->ctrl->getFormAction($this));
		$form_training_settings->setShowTopButtons(false);
		$form_training_settings->setId("dct_ts");

		if($this->crs_obj_id !== null) {
			$date = $this->crs_utils->getStartDate();

			$start_date = new ilDateTime($date->get(IL_CAL_DATE)." ".$this->crs_utils->getFormattedStartTime().":00",IL_CAL_DATETIME);
			$end_date = new ilDateTime($date->get(IL_CAL_DATE)." ".$this->crs_utils->getFormattedendTime().":00",IL_CAL_DATETIME);

			$training_info["start_datetime"] = $start_date;
			$training_info["end_datetime"] = $end_date;
			$training_info["date"] = $date;

			$hidden = new ilHiddenInputGUI("crs_ref_id");
			$hidden->setValue($this->crs_ref_id);
			$hidden->insert($form_training_settings->getTemplate());

			$hidden = new ilHiddenInputGUI("crs_obj_id");
			$hidden->setValue($this->crs_obj_id);
			$hidden->insert($form_training_settings->getTemplate());
		}

		if($this->crs_obj_id === null && $this->crs_request_id !== null) {
			$reguest_db = new gevDecentralTrainingCreationRequestDB();
			$request = $reguest_db->request($this->crs_request_id);

			$training_info["start_datetime"] = $request->settings()->start();
			$training_info["end_datetime"] = $request->settings()->end();
			$training_info["date"] = $request->settings()->start();

			$hidden = new ilHiddenInputGUI("crs_request_id");
			$hidden->setValue($this->crs_request_id);
			$hidden->insert($form_training_settings->getTemplate());
		}

		$date = new ilDateTimeInputGUI($this->lng->txt("date"), "date");
		$date->setShowTime(false);
		$date->setDate($training_info["date"]);
		$date->setDisabled($this->no_changes);
		$form_training_settings->getTemplate()->setVariable("DATE",$date->render());
		$form_training_settings->getTemplate()->setVariable("DATE_LABEL",$this->lng->txt("date"));
		

		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "time");
		$time->setShowDate(false);
		$time->setShowTime(true);
		$time->setStart($training_info["start_datetime"]);
		$time->setEnd($training_info["end_datetime"]);
		$time->setStartText($this->lng->txt("gev_from"));
		$time->setEndText($this->lng->txt("until"));
		$time->setDisabled($this->no_changes);
		$time->setMinuteStepSize(self::MINUTE_STEP_SIZE);

		$form_training_settings->getTemplate()->setVariable("TIME",$time->render());
		$form_training_settings->getTemplate()->setVariable("TIME_LABEL",$this->lng->txt("gev_duration"));

		

		return $form_training_settings->getHTML();
	}

	protected function renderAddBuldingBlock() {
		$form_add_building_block = new catPropertyFormTplGUI();
		$form_add_building_block->setTemplate("tpl.dct_add_building_block.html","Services/GEV/DecentralTrainings");
		$form_add_building_block->getTemplate()->setVariable("BTN_CMD","toAddCrsBuildingBlock");
		$form_add_building_block->getTemplate()->setVariable("BTN_VALUE",$this->lng->txt("gev_dec_training_add_building_block"));
		$form_add_building_block->setFormAction($this->ctrl->getFormAction($this));
		$form_add_building_block->setShowTopButtons(false);
		$form_add_building_block->setId("dct_ab");

		if($this->crs_obj_id !== null) {
			$hidden = new ilHiddenInputGUI("crs_ref_id");
			$hidden->setValue($this->crs_ref_id);
			$hidden->insert($form_add_building_block->getTemplate());

			$hidden = new ilHiddenInputGUI("crs_obj_id");
			$hidden->setValue($this->crs_obj_id);
			$hidden->insert($form_add_building_block->getTemplate());
		}

		if($this->crs_request_id !== null) {
			$hidden = new ilHiddenInputGUI("crs_request_id");
			$hidden->setValue($this->crs_request_id);
			$hidden->insert($form_add_building_block->getTemplate());
		}

		$form_add_building_block->getTemplate()->setVariable("HEADER_TOPIC",$this->lng->txt("gev_dec_training_header_topic"));
		$form_add_building_block->getTemplate()->setVariable("HEADER_BLOCK",$this->lng->txt("gev_dec_training_header_block"));
		$form_add_building_block->getTemplate()->setVariable("HEADER_INFOS",$this->lng->txt("gev_dec_training_header_infos"));

		$topic = new ilSelectInputGUI($this->lng->txt("gev_dec_training_preselect_topic"), "topic");
		$options = array(0 => "-") + gevBuildingBlockUtils::getAllInBuildingBlocksSelectedTopics();
		$topic->setOptions($options);
		$topic->setDisabled($this->no_changes);
		$form_add_building_block->getTemplate()->setVariable("TOPIC",$topic->render());
		$form_add_building_block->getTemplate()->setVariable("TOPIC_LABEL",$this->lng->txt("gev_dec_training_preselect_topic"));
		
		$blocks = gevBuildingBlockUtils::getPossibleBuildingBlocksGroupByTopic();
		$options = "";
		
		foreach ($blocks as $key => $value) {
			$tpl = new ilTemplate("tpl.select_with_group.html", true, true, "Services/GEV/DecentralTrainings");
			$tpl->setVariable("HEADER",$key);

			if($this->no_changes) {
				$form_add_building_block->getTemplate()->setVariable("DISABLE",'disabled="disabled"');
			}
			
			foreach ($value as $key => $name) {
				$tpl->setCurrentBlock("option");
				$tpl->setVariable("VALUE",$key);
				$tpl->setVariable("BLOCK_TITLE",$name);
				$tpl->setVariable("BLOCK",$name);
				$tpl->parseCurrentBlock();
			}
			$options .= $tpl->get();
		}
		$form_add_building_block->getTemplate()->setVariable("BLOCKS_LABEL",$this->lng->txt("gev_dec_training_select_building_block"));
		$form_add_building_block->getTemplate()->setVariable("BLOCKS_SELECT_NAME","blocks");
		$form_add_building_block->getTemplate()->setVariable("BLOCKS",$options);

		$content = new ilTextAreaInputGUI($this->lng->txt("gev_dec_training_content"), "content");
		$content->setDisabled(true);
		$content->setRows(5);
		$form_add_building_block->getTemplate()->setVariable("CONTENT",$content->render());
		$form_add_building_block->getTemplate()->setVariable("CONTENT_LABEL",$this->lng->txt("gev_dec_training_content"));

		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "duration");
		$time->setShowDate(false);
		$time->setShowTime(true);
		$time->setStartText($this->lng->txt("gev_from"));
		$time->setEndText($this->lng->txt("until"));
		$time->setDisabled($this->no_changes);
		$time->setMinuteStepSize(self::MINUTE_STEP_SIZE);
		$form_add_building_block->getTemplate()->setVariable("TIME",$time->render());
		$form_add_building_block->getTemplate()->setVariable("TIME_LABEL",$this->lng->txt("gev_duration"));

		$content = new ilTextAreaInputGUI($this->lng->txt("gev_targets_and_benefit"), "target");
		$content->setDisabled(true);
		$content->setRows(5);
		$form_add_building_block->getTemplate()->setVariable("DEST_AND_USE",$content->render());
		$form_add_building_block->getTemplate()->setVariable("DEST_AND_USE_LABEL",$this->lng->txt("gev_targets_and_benefit"));

		$wp = new ilTextInputGUI($this->lng->txt("gev_dec_building_wp"), "wp");
		$wp->setValue("");
		$wp->setSize(300);
		$form_add_building_block->getTemplate()->setVariable("WP",$wp->render());
		$form_add_building_block->getTemplate()->setVariable("WP_LABEL",$this->lng->txt("gev_dec_building_wp"));

		$hidden = new ilHiddenInputGUI("isWP");
		$hidden->setValue("");
		$hidden->insert($form_add_building_block->getTemplate());

		
		return $form_add_building_block->getHTML();
	}

	protected function renderBlockTable() {
		$this->ctrl->setParameterByClass("gevDecentralTrainingCourseCreatingBuildingBlock2GUI","crs_request_id",$this->crs_request_id);
		$this->ctrl->setParameterByClass("gevDecentralTrainingCourseCreatingBuildingBlock2GUI","crs_ref_id",$this->crs_ref_id);
		
		$crs_tbl = new gevDecentralTrainingCourseBuildingBlockTableGUI($this,$this->crs_ref_id,$this->crs_request_id,$this->no_changes);
		$crs_tbl->setId("list");
		$crs_tbl->addCommandButton("toUpdateBuildingBlock",$this->lng->txt("gev_dec_training_save_changes"));
		$crs_tbl->setAdvice("gev_dec_training_break_advice");
		$crs_tbl->setTopCommands(false);
		$html = $crs_tbl->getHTML(); 

		$this->ctrl->clearParametersByClass("gevDecentralTrainingCourseCreatingBuildingBlock2GUI");

		return $html;
	}

	protected function renderSpacer($height = "", $hr = "") {
		$tpl = new ilTemplate("tpl.dct_spacer.html", true, true, "Services/GEV/DecentralTrainings");
		$tpl->setVariable("HEIGHT",$height);
		$tpl->setVariable("HR",$hr);;
		return $tpl->get();
	}

	protected function renderIdentification() {
		return '<div id="dct-no_form_read"></div>';
	}

	protected function renderPDFDownload() {
		$form_pdf = new catPropertyFormGUI();
		$form_pdf->addCommandButton("toDownloadPDF", $this->lng->txt("gev_dec_training_schedule_download"));
		$form_pdf->setFormAction($this->ctrl->getFormAction($this));
		$form_pdf->setShowTopButtons(false);
		$form_pdf->setId("dct_navi");

		return $form_pdf->getHTML();
	}

	protected function determineObjId() {
		if(isset($_GET["id"])) {
			$this->obj_id = $_GET["id"];
		}

		if(isset($_POST["id"])) {
			$this->obj_id = $_POST["id"];
		}
	}

	protected function determineCrsRef() {
		if(isset($_GET["ref_id"])) {
			$this->crs_ref_id = $_GET["ref_id"];
		}

		if(isset($_POST["crs_ref_id"])) {
			$this->crs_ref_id = $_POST["crs_ref_id"];
		}

		if($this->crs_ref_id == "") {
			$this->crs_ref_id = null;
		}

		if($this->crs_ref_id !== null) {
			$this->crs_obj_id = gevObjectUtils::getObjId($this->crs_ref_id);
			$this->crs_utils = gevCourseUtils::getInstance($this->crs_obj_id);
		}
	}

	protected function determineCrsRequestId() {

		if($this->crs_request_id === null) {
			if(isset($_POST["crs_request_id"])) {
				$this->crs_request_id = $_POST["crs_request_id"];
			}

			if(isset($_GET["crs_request_id"])) {
				$this->crs_request_id = $_GET["crs_request_id"];
			}

			if($this->crs_request_id == "") {
				$this->crs_request_id = null;
			}
		}
	}

	protected function toSaveTrainingSettings() {
		$_POST["time"]["start"]["time"]["h"] = (strlen($_POST["time"]["start"]["time"]["h"]) < 2) ? 
															"0".$_POST["time"]["start"]["time"]["h"] : 
															$_POST["time"]["start"]["time"]["h"];

		$_POST["time"]["start"]["time"]["m"] = (strlen($_POST["time"]["start"]["time"]["m"]) < 2) ? 
															"0".$_POST["time"]["start"]["time"]["m"] : 
															$_POST["time"]["start"]["time"]["m"];

		$_POST["time"]["end"]["time"]["h"] = (strlen($_POST["time"]["end"]["time"]["h"]) < 2) ? 
															"0".$_POST["time"]["end"]["time"]["h"] : 
															$_POST["time"]["end"]["time"]["h"];

		$_POST["time"]["end"]["time"]["m"] = (strlen($_POST["time"]["end"]["time"]["m"]) < 2) ? 
															"0".$_POST["time"]["end"]["time"]["m"] : 
															$_POST["time"]["end"]["time"]["m"];

		$_POST["date"]["date"]["d"] = (strlen($_POST["date"]["date"]["d"]) < 2) ? 
															"0".$_POST["date"]["date"]["d"] : 
															$_POST["date"]["date"]["d"];

		$_POST["date"]["date"]["m"] = (strlen($_POST["date"]["date"]["m"]) < 2) ? 
															"0".$_POST["date"]["date"]["m"] : 
															$_POST["date"]["date"]["m"];

		$start_time = implode(":",$_POST["time"]["start"]["time"]);
		$end_time = implode(":",$_POST["time"]["end"]["time"]);
		$date2 = implode(".",$_POST["date"]["date"]);
		$date = $_POST["date"]["date"]["y"]."-".$_POST["date"]["date"]["m"]."-".$_POST["date"]["date"]["d"];

		if(!$this->checkDate($date)) {
				ilUtil::sendInfo($this->lng->txt("gev_dec_training_date_before_today"), false);
				$this->render();
				return;
		}

		if(!$this->checkTimeData($start_time,$end_time)) {
			ilUtil::sendInfo($this->lng->txt("gev_dec_training_end_before_start"), false);
			$this->render();
			return;
		}

		if(!$this->checkDuration($start_time,$end_time)) {
			ilUtil::sendInfo($this->lng->txt("gev_dec_training_crs_to_long_no_change"), false);
			$this->render();
			return;
		}

		if($this->crs_obj_id !== null) {
			$start_date = new ilDate($date,IL_CAL_DATE);
			$this->crs_utils->setStartDate($start_date);
			$this->crs_utils->setEndDate($start_date);
			$this->crs_utils->setSchedule(array($start_time ."-".$end_time));

			require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
			$crs_mails = new gevCrsAutoMails($this->crs_obj_id);
			$crs_mails->sendDeferred("invitation");

			$this->app_event_handler->raise('Modules/Course',
											'update',
											array('object' => $this->crs_utils->getCourse(),
													'obj_id' => $this->crs_utils->getId()
													)
											);
		}

		if($this->crs_obj_id === null && $this->crs_request_id !== null) {
			$reguest_db = new gevDecentralTrainingCreationRequestDB();
			$request = $reguest_db->request($this->crs_request_id);
			$start = new ilDateTime($date." ".$start_time.":00",IL_CAL_DATETIME);
			$end = new ilDateTime($date." ".$end_time.":00",IL_CAL_DATETIME);
			$request->settings()->setStart($start);
			$request->settings()->setEnd($end);

			$request->save();
		}

		$this->render();
	}

	protected function toAddCrsBuildingBlock() {
		$_POST["duration"]["start"]["time"]["h"] = (strlen($_POST["duration"]["start"]["time"]["h"]) < 2) ? 
																"0".$_POST["duration"]["start"]["time"]["h"] : 
																$_POST["duration"]["start"]["time"]["h"];

		$_POST["duration"]["start"]["time"]["m"] = (strlen($_POST["duration"]["start"]["time"]["m"]) < 2) 
																? "0".$_POST["duration"]["start"]["time"]["m"] :
																$_POST["duration"]["start"]["time"]["m"];

		$_POST["duration"]["end"]["time"]["h"] = (strlen($_POST["duration"]["end"]["time"]["h"]) < 2) ? 
																"0".$_POST["duration"]["end"]["time"]["h"] : 
																$_POST["duration"]["end"]["time"]["h"];

		$_POST["duration"]["end"]["time"]["m"] = (strlen($_POST["duration"]["end"]["time"]["m"]) < 2) ? 
																"0".$_POST["duration"]["end"]["time"]["m"] : 
																$_POST["duration"]["end"]["time"]["m"];

		$start_time = implode(":",$_POST["duration"]["start"]["time"]).":00";
		$end_time = implode(":",$_POST["duration"]["end"]["time"]).":00";
		$block = $_POST["blocks"];
		$credit_points = $_POST["wp"];

		$new_crs_bb = gevCourseBuildingBlockUtils::getInstance(gevCourseBuildingBlockUtils::getNextCrsBBlockId());
		$new_crs_bb->setStartTime($start_time);
		$new_crs_bb->setEndTime($end_time);
		$new_crs_bb->setBuildingBlock($block);
		$new_crs_bb->setCourseRequestId($this->crs_request_id);
		$new_crs_bb->setCrsId($this->crs_ref_id);
		$new_crs_bb->setCreditPoints($credit_points);

		$new_crs_bb->save();

		if($this->crs_obj_id !== NULL) {
			require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
			$crs_mails = new gevCrsAutoMails($this->crs_obj_id);
			$crs_mails->sendDeferred("invitation");
		}

		$this->render();
	}

	protected function toDeleteCrsBuildingBlock() {
		$crs_bb_id = $_GET["id"];
		$delete_crs_bb = gevCourseBuildingBlockUtils::getInstance($crs_bb_id);
		$delete_crs_bb->delete();

		$this->render();
	}

	protected function toUpdateBuildingBlock() {
		$fail_time = array();
		$show_fail = false;
		$html = "";

		foreach ($_POST as $key => $value) {
			if($key == "cmd") {
				continue;
			}

			if($this->startsWith($key,"end_date")) {
				continue;
			}

			$key_split = split("_",$key);
			$id = $key_split[2];

			$_POST["start_date_".$id]["time"]["h"] = (strlen($_POST["start_date_".$id]["time"]["h"]) < 2) ? 
																"0".$_POST["start_date_".$id]["time"]["h"] : 
																$_POST["start_date_".$id]["time"]["h"];

			$_POST["start_date_".$id]["time"]["m"] = (strlen($_POST["start_date_".$id]["time"]["m"]) < 2) 
																? "0".$_POST["start_date_".$id]["time"]["m"] :
																$_POST["start_date_".$id]["time"]["m"];

			$_POST["end_date_".$id]["time"]["h"] = (strlen($_POST["end_date_".$id]["time"]["h"]) < 2) ? 
																"0".$_POST["end_date_".$id]["time"]["h"] : 
																$_POST["end_date_".$id]["time"]["h"];

			$_POST["end_date_".$id]["time"]["m"] = (strlen($_POST["end_date_".$id]["time"]["m"]) < 2) ? 
																"0".$_POST["end_date_".$id]["time"]["m"] : 
																$_POST["end_date_".$id]["time"]["m"];

			$start_time = implode(":",$_POST["start_date_".$id]["time"]).":00";
			$end_time = implode(":",$_POST["end_date_".$id]["time"]).":00";

			if($end_time < $start_time) {
				$fail_time[] = array("start_time"=>$start_time,"end_time"=>$end_time);
			}

			gevCourseBuildingBlockUtils::updateTimesAndCreditPoints($id
																	,$start_time
																	,$end_time);
		}
		
		if(!empty($fail_time)) {
			$tpl = new ilTemplate("tpl.dct_block_time_issue.html", true, true, "Services/GEV/DecentralTrainings");
			foreach ($fail_time as $key => $value) {
				$tpl->setCurrentBlock("end_before_start");
				$tpl->setVariable("START",$value["start_time"]);
				$tpl->setVariable("END",$value["end_time"]);
				$tpl->parseCurrentBlock();
			}

			$tpl->setVariable("MESSAGE",$this->lng->txt("gev_dec_training_block_end_before_start"));
			$html .= $tpl->get();

			ilUtil::sendInfo($html, false);
			$this->render();
			return;
		}

		$fail_time = gevCourseBuildingBlockUtils::timeIssuesBlocks($this->crs_ref_id,$this->crs_request_id);
		if(!empty($fail_time)) {
			$tpl = new ilTemplate("tpl.dct_block_overlaping.html", true, true, "Services/GEV/DecentralTrainings");
			foreach ($fail_time as $key => $value) {
				$tpl->setCurrentBlock("time_overlap");
				$tpl->setVariable("START_BEFORE",$value["start_time_before"]);
				$tpl->setVariable("END_BEFORE",$value["end_time_before"]);
				$tpl->setVariable("START_END",$value["start_time_end"]);
				$tpl->setVariable("END_END",$value["end_time_end"]);
				$tpl->parseCurrentBlock();
			}

			$tpl->setVariable("MESSAGE",$this->lng->txt("gev_dec_training_block_time_overlap"));
			$html .= $tpl->get();

			ilUtil::sendInfo($html, false);
			$this->render();
			return;
		}

		if(gevCourseBuildingBlockUtils::timeIssuesCrs($this->crs_ref_id,$this->crs_request_id)) {
			ilUtil::sendInfo($this->lng->txt("gev_dec_training_blocks_time_issue_course"), true);
			$this->render();
			return;
		}

		$this->render();
	}

	protected function toCancleCreation() {
		$crs_request_id = $_GET["crs_request_id"];

		if($crs_request_id !== null) {
			require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
			$req_db = new gevDecentralTrainingCreationRequestDB();
			$request = $req_db->request($crs_request_id);
			$request->delete();
		}

		$this->ctrl->redirectByClass("ilTEPGUI");
	}

	protected function toDownloadPDF() {
		$this->crs_utils->deliverCrsScheduleList();
	}

	protected function toSaveRequest() {
		if(count(gevCourseBuildingBlockUtils::getAllCourseBuildingBlocksRaw(null,$this->crs_request_id)) > 0) {
			$fail_time = gevCourseBuildingBlockUtils::timeIssuesBlocks(null,$this->crs_request_id);
			if(!empty($fail_time)) {
				$tpl = new ilTemplate("tpl.dct_block_overlaping.html", true, true, "Services/GEV/DecentralTrainings");
				foreach ($fail_time as $key => $value) {
					$tpl->setCurrentBlock("time_overlap");
					$tpl->setVariable("START_BEFORE",$value["start_time_before"]);
					$tpl->setVariable("END_BEFORE",$value["end_time_before"]);
					$tpl->setVariable("START_END",$value["start_time_end"]);
					$tpl->setVariable("END_END",$value["end_time_end"]);
					$tpl->parseCurrentBlock();
				}

				$message = $this->lng->txt("gev_dec_training_block_time_overlap")."<br/>".$this->lng->txt("gev_dec_training_crs_will_not_created");
				$tpl->setVariable("MESSAGE",$message);
				$html .= $tpl->get();

				ilUtil::sendInfo($html, false);
				$this->render();
				return;
			}

			if(gevCourseBuildingBlockUtils::timeIssuesCrs(null,$this->crs_request_id)) {
				$message = $this->lng->txt("gev_dec_training_blocks_time_issue_course")."<br/>".$this->lng->txt("gev_dec_training_crs_will_not_created");
				ilUtil::sendInfo($message, false);
				$this->render();
				return;
			}
		}

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
		$request_db = new gevDecentralTrainingCreationRequestDB();
		$crs_request = $request_db->request((int)$this->crs_request_id);
		$crs_request->request();

		ilUtil::sendSuccess($this->lng->txt("gev_dec_training_creation_requested"), true);
		if (!$this->dctl_utils->userCanOpenNewCreationRequest()) {
			$this->ctrl->redirect($this, "showOpenRequests");
		}
		else {
			$this->ctrl->redirectByClass(array("ilTEPGUI"));
		}
	}

	protected function showOpenRequests() {
		$requests = $this->dctl_utils->getOpenCreationRequests();
		if ($this->dctl_utils->userCanOpenNewCreationRequest() && !$this->dctl_utils->userCanOpenMultipleRequests()) {
			return $this->redirectToBookingFormOfLastCreatedTraining();
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($this->current_user->getId());
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$view = $this->dctl_utils->getOpenRequestsView($requests, !$this->dctl_utils->userCanOpenNewCreationRequest());
		
		$this->tpl->setContent( $title->render()
			  . $view);
	}

	protected function redirectToBookingFormOfLastCreatedTraining() {
		$obj_id = $this->dctl_utils->lastCreatedCourseId();
		if (!$obj_id) {
			$this->ctrl->redirectByClass(array("ilTEPGUI"));
		}
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ref_id = gevObjectUtils::getRefId($obj_id);
		
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingPermissions.php");
		
		$this->ctrl->setParameter($this, "obj_id", $obj_id);
		ilCourseBookingAdminGUI::setBackTarget($this->ctrl->getLinkTarget($this, "backFromBooking"));
		$this->ctrl->setParameter($this, "obj_id", null);
		
		$this->ctrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $ref_id);
		$this->ctrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
	}

	protected function checkDate($date) {
		$now = new ilDate(date("y-m-d"),IL_CAL_DATE);
		$now = $now->getUnixTime();
		$date = new ilDate($date,IL_CAL_DATE);
		$date = $date->getUnixTime();
		
		if($date < $now) {
			return false;
		}

		return true;
	}

	protected function checkTimeData($start, $end) {
		$start = split(":",$start);
		$end = split(":",$end);

		if($end[0] < $start[0]) {
			return false;
		}

		return true;
	}

	protected function checkDuration($start, $end) {
		$start = split(":",$start);
		$end = split(":",$end);

		$h = 0;
		if($end[1] < $start[1]) {
			$h = -1;
		}

		$h = $h + ($end[0] - $start[0]);

		if($h > 12) {
			return false;
		}

		return true;
	}

	protected function noTimeIssuesBlocks($start,$end,$request_id = null, $crs_id = null) {
		if(!gevCourseBuildingBlockUtils::checkTimeIssues($start,$end,$crs_id,$request_id)) {
			return false;
		}

		return true;
	}

	protected function blockWithinCourseTime($start,$end,$request_id = null, $crs_id = null) {
		
		if($crs_id !== null) {
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$start_time = $crs_utils->getFormattedStartTime().":00";
			$end_time = $crs_utils->getFormattedEndTime().":00";

			$start_date = new ilDateTime(date("Y-m-d")." ".$start_time, IL_CAL_DATETIME);
			$end_date = new ilDateTime(date("Y-m-d")." ".$end_time, IL_CAL_DATETIME);

			$b_start_date = new ilDateTime(date("Y-m-d")." ".$start, IL_CAL_DATETIME);
			$b_end_date = new ilDateTime(date("Y-m-d")." ".$end, IL_CAL_DATETIME);
		}

		if($crs_id === null && $request_id !== null) {
			$req_db = new gevDecentralTrainingCreationRequestDB();
			$request = $req_db->request($request_id);

			$start_date = $request->settings()->start();
			$end_date = $request->settings()->end();

			$date = $start_date->get(IL_CAL_DATE);

			$b_start_date = new ilDateTime($date." ".$start, IL_CAL_DATETIME);
			$b_end_date = new ilDateTime($date." ".$end, IL_CAL_DATETIME);
		}

		if($start_date->get(IL_CAL_UNIX) > $b_start_date->get(IL_CAL_UNIX) || $end_date->get(IL_CAL_UNIX) < $b_end_date->get(IL_CAL_UNIX)) {
				return false;
		}

		return true;
	}

	protected function startsWith($haystack, $needle) {
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	protected function toTEP() {
		$this->ctrl->redirectByClass(array("ilTEPGUI"));
	}

	public function toChangeCourseData() {
		if(count(gevCourseBuildingBlockUtils::getAllCourseBuildingBlocksRaw($this->crs_ref_id,$this->crs_request_id)) > 0) {
			$fail_time = gevCourseBuildingBlockUtils::timeIssuesBlocks($this->crs_ref_id,$this->crs_request_id);
			
			if(!empty($fail_time)) {
				$tpl = new ilTemplate("tpl.dct_block_overlaping.html", true, true, "Services/GEV/DecentralTrainings");
				foreach ($fail_time as $key => $value) {
					$tpl->setCurrentBlock("time_overlap");
					$tpl->setVariable("START_BEFORE",$value["start_time_before"]);
					$tpl->setVariable("END_BEFORE",$value["end_time_before"]);
					$tpl->setVariable("START_END",$value["start_time_end"]);
					$tpl->setVariable("END_END",$value["end_time_end"]);
					$tpl->parseCurrentBlock();
				}

				$message = $this->lng->txt("gev_dec_training_block_time_overlap")."<br/>".$this->lng->txt("gev_dec_training_crs_will_not_created");
				$tpl->setVariable("MESSAGE",$message);
				$html .= $tpl->get();

				ilUtil::sendInfo($html, false);
				$this->render();
				return;
			}

			if(gevCourseBuildingBlockUtils::timeIssuesCrs($this->crs_ref_id,$this->crs_request_id)) {
				$message = $this->lng->txt("gev_dec_training_blocks_time_issue_course")."<br/>".$this->lng->txt("gev_dec_training_crs_will_not_created");
				ilUtil::sendInfo($message, false);
				$this->render();
				return;
			}
		}


		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingGUI.php");
		$gui = new gevDecentralTrainingGUI();
		$ret = $this->ctrl->forwardCommand($gui);
	}

	protected function createMailPreview() {
		$tpl = new IlTemplate("tpl.dct_mail_preview.html",true,true,"Services/GEV/DecentralTrainings");
		require_once("Services/GEV/Utils/classes/class.gevMailUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings = gevSettings::getInstance();
		$mail_utils = gevMailUtils::getInstance();
		
		$mail_tpl = $mail_utils->getMailTemplateByIdAndLanguage($settings->getDecentralTrainingMailTemplateId(),$this->lng->getLangKey());
		$tpl->setVariable("MAILTEMPLATE_PRAE",nl2br($mail_tpl));

		$mail_tpl = $mail_utils->getMailTemplateByIdAndLanguage($settings->getCSNMailTemplateId(),$this->lng->getLangKey());
		$tpl->setVariable("MAILTEMPLATE_CSN",nl2br($mail_tpl));

		$mail_tpl = $mail_utils->getMailTemplateByIdAndLanguage($settings->getWebExMailTemplateId(),$this->lng->getLangKey());
		$tpl->setVariable("MAILTEMPLATE_WEBEX",nl2br($mail_tpl));

		if($this->template_id !== null){
			$tpl_ref = gevObjectUtils::getRefId($this->template_id);
			$tpl->setVariable("CRS_TPL","crs_template_id_".$tpl_ref);
		} else {
			if($this->crs_ref_id !== null) {
				$tpl->setVariable("CRS_REF","crs_ref_id_".$this->crs_ref_id);
			}

			if($this->crs_ref_id === null && $this->crs_request_id !== null) {
				$tpl->setVariable("CRS_REQUEST","crs_request_id_".$this->crs_request_id);
			}
		}
		
		return $tpl->get();
	}

	protected function backFromBooking() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::setBackTarget(null);
		
		$this->ctrl->redirectByClass(array("ilTEPGUI"));
		return;
	}
}