<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course searching GUI for Generali, admin view
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/


//require_once("Services/GEV/Desktop/classes/class.gevCourseHighlightsGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevUserSelectorGUI.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");

//require_once("Services/GEV/Desktop/classes/class.gevCourseSearchGUI.php");
require_once("Services/GEV/Desktop/classes/class.ilAdminSearchTableGUI.php");

class ilAdminSearchGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->user_id = $ilUser->getId();
		$this->user = &$ilUser;
		$this->user_utils = gevUserUtils::getInstanceByObj($ilUser);
		$this->search_form = null;

		$this->target_user_id = $ilUser->getId();
		$this->ctrl->setParameter($this, "target_user_id", $this->target_user_id);

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		
		$in_search = $cmd == "search";
		
		return $this->render($in_search);
	}

	public function render($a_in_search = false) {
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
						if (!$value || $value == $this->lng->txt("gev_crs_srch_all")) {
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
					if ($postvar == "period") {
						$start = $_GET["start"];
						$end = $_GET["end"];
						if ($start && $end) {
							$search_opts["period"] = array(
								"start" => $start,
								"end" => $end
								);
						}
					}
					else {
						$val = $_GET[$postvar];
						if ($val) {
							$search_opts[$postvar] = $val;
						}
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
			// special treatment for period is needed since it is an array.
			// when i try to serialize that array, ilias seems to remove '"'
			// which makes deserialisation fail
			if ($key == "period") {
				$this->ctrl->setParameter($this, "start", urlencode($value["start"]));
				$this->ctrl->setParameter($this, "end", urlencode($value["end"]));
			}
			else {
				$this->ctrl->setParameter($this, $key, urlencode($value));
			}
		}

		$crs_tbl = new ilAdminSearchTableGUI($search_opts, $this->target_user_id, $this);
		$crs_tbl->setTitle(!$a_in_search?"gev_crs_srch_title":"gev_crs_srch_results")
				->setSubtitle( $this->target_user_id == $this->user_id
							 ? "gev_crs_srch_my_table_desc"
							 : "gev_crs_srch_theirs_table_desc"
							 )
				->setImage("GEV_img/ico-head-search.png")
				//->setCommand("gev_crs_srch_limit", "www.google.de"); // TODO: set this properly
				//->setCommand("gev_crs_srch_limit", "javascript:gevShowSearchFilter();"); // TODO: set this properly
				->setCommand("gev_crs_srch_limit", "-"); // TODO: set this properly

		return $this->renderSearch()
			 . $crs_tbl->getHTML()
			 ;
	}
	
	public function renderSearch() {
		$form = $this->getSearchForm();
		
		return $form->getHTML();
	}
	
	public function getSearchForm() {
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


		$search_title = new catTitleGUI("gev_course_search", "gev_course_search_desc", "GEV_img/ico-head-search.png");
		$form->setTitle($search_title->render());

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$form->addItem($title);
		
		$custom_id = new ilTextInputGUI($this->lng->txt("gev_course_id"), "custom_id");
		$form->addItem($custom_id);
		
		$type = new ilSelectInputGUI($this->lng->txt("gev_course_type"), "type");
		$type->setOptions(gevCourseUtils::getTypeOptions());
		$form->addItem($type);
		
		$program = new ilSelectInputGUI($this->lng->txt("gev_edu_program"), "program");
		$program->setOptions(gevCourseUtils::getEducationProgramOptions());
		$form->addItem($program);
		
		$location = new ilSelectInputGUI($this->lng->txt("udf_type_venueselect"), "location");
		$location->setOptions(gevCourseUtils::getLocationOptions());
		$form->addItem($location);
		
		$period = new ilDateDurationInputGUI($this->lng->txt("time_segment"), "period");
		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$period->setStart($now);
		$one_year = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$one_year->increment(ilDateTime::YEAR, 1);
		$period->setEnd($one_year);
		$form->addItem($period);
		
		
		$options = array(
			'0' => 'alle',
			'1' => 'online',
			'2' => 'offline',
		);
		$type = new ilSelectInputGUI($this->lng->txt("gev_course_online_status"), "online_status");
		$type->setOptions($options);
		$form->addItem($type);


		$this->search_form = $form;
		return $form;
	}
}

?>