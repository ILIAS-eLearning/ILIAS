<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course searching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/CourseSearch/classes/class.gevCourseHighlightsGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevUserSelectorGUI.php");
//require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/CourseSearch/classes/class.gevCourseSearchTableGUI.php");
require_once("Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");

class gevCourseSearchGUI {
	public function __construct($a_target_user_id = null) {
		global $lng, $ilCtrl, $tpl, $ilUser;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gUser = $ilUser;

		$this->gUser_id = $ilUser->getId();
		
		//$this->crs_srch = gevUserUtils::getInstanceByObj($ilUser);
		$this->crs_srch = gevCourseSearch::getInstance($ilUser->getId());
		$this->search_form = null;

		$this->active_tab = $_GET["active_tab"] ? $_GET["active_tab"] : gevCourseSearch::TAB_TO_SHOW_ADVICE;

		if ($a_target_user_id === null) {
			if ($this->crs_srch->hasUserSelectorOnSearchGUI()) {
				$this->target_user_id = $_POST["target_user_id"]
									  ? $_POST["target_user_id"]
									  : (   $_GET["target_user_id"]
									  	  ? $_GET["target_user_id"]
									  	  : $ilUser->getId()
									  	);
			}
			else {
				$this->target_user_id = $ilUser->getId();
			}
		}
		else {
			$this->target_user_id = $a_target_user_id;
		}
		
		$this->gCtrl->setParameter($this, "target_user_id", $this->target_user_id);

		$this->gTpl->getStandardTemplate();
		}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();
		$in_search = $cmd == "search";

		if(isset($_GET["cmdSearch"]) && $_GET["cmdSearch"]) {
			$in_search = true;
		}

		if($cmd == "clearSearch") {
			$in_search = false;
		}

		return $this->render($in_search);
	}

	public function render($a_in_search = false) {
		$spacer = new catHSpacerGUI();

		if ($this->crs_srch->hasUserSelectorOnSearchGUI()) {
			$user_selector = new gevUserSelectorGUI($this->target_user_id);
			$users = array_merge( array(array("usr_id" => $this->gUser_id
											 , "firstname" => $this->gUser->getFirstname()
											 , "lastname" => $this->gUser->getLastname()
											 )
									   )
								, $this->crs_srch->getEmployeesForCourseSearch()
								);
			$user_selector->setUsers($users)
						  ->setCaption("gev_crs_srch_usr_slctr_caption")
						  ->setAction($this->gCtrl->getLinkTargetByClass("gevCourseSearchGUI"));
			$usrsel = $user_selector->render() . $spacer->render();
		}
		else {
			$usrsel = "";
		}

		$hls = new gevCourseHighlightsGUI($this->target_user_id);

		$spacer_out = $spacer->render();
		
		$form = $this->getSearchForm();
		if ($a_in_search) {
			// search params are passed via form post
			if (isset($_POST["cmd"]) && !$_GET["cmdSearch"]) {
				$form->setValuesByPost();
				if ($form->checkInput()) {
					$search_opts = $form->getInputs();
					// clean empty or "all"-options
					foreach($search_opts as $key => $value) {
						if (!$value || $value == $this->gLng->txt("gev_crs_srch_all")) {
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
			$this->gCtrl->setParameter($this, "cmd", "search");
		}
		else {
			$search_opts = array();
		}

		foreach ($search_opts as $key => $value) {
			if (is_string($value)) {
				$search_opts[$key] = trim($value);
			}
		}


		// this is needed to pass the search parameter via the sorting
		// links of the table.
		foreach( $search_opts as $key => $value) {
			// special treatment for period is needed since it is an array.
			// when i try to serialize that array, ilias seems to remove '"'
			// which makes deserialisation fail
			if ($key == "period") {
				$this->gCtrl->setParameter($this, "start", urlencode($value["start"]));
				$this->gCtrl->setParameter($this, "end", urlencode($value["end"]));
			}
			else {
				$this->gCtrl->setParameter($this, $key, urlencode($value));
			}
		}

		//ADD Course Type depending on active Tab
		$search_opts = $this->crs_srch->addSearchForTypeByActiveTab($search_opts, $this->active_tab);
		$this->gCtrl->setParameter($this, "active_tab", $this->active_tab);
		$this->gCtrl->setParameter($this, "cmdSearch", $a_in_search);

		$crs_tbl = new gevCourseSearchTableGUI($search_opts, $this->target_user_id, $this, $this->active_tab,$a_in_search);
		$crs_tbl->setTitle(!$a_in_search?"gev_crs_srch_title":"gev_crs_srch_results")
				->setSubtitle( ($this->target_user_id == $this->gUser_id 
								|| $this->gUser_id == 0 )// Someone is viewing the offers for agents as anonymus.
							 ? "gev_crs_srch_my_table_desc"
							 : "gev_crs_srch_theirs_table_desc"
							 )
				->setImage("GEV_img/ico-head-search.png");

		if($a_in_search) {
			$crs_tbl->setClearSearch("gev_crs_src_clear_search",$this->gCtrl->getLinkTargetByClass("gevCourseSearchGUI", "clearSearch"));
		}

		return $usrsel
			 . ( ($hls->countHighlights() > 0 && !$a_in_search)
			   ?   $hls->render()
			 	 . $spacer->render()
			   : ""
			   )
			 . $this->renderSearch()
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
		$this->gCtrl->setParameter($this, "active_tab", $this->active_tab);
		$form->setFormAction($this->gCtrl->getFormAction($this));
		$form->addCommandButton("search", $this->gLng->txt("search"));
		
		$form->setId('gevCourseSearchForm');
		
		global $tpl;
		// http://www.jacklmoore.com/colorbox/
		$tpl->addJavaScript("Services/CaTUIComponents/js/colorbox-master/jquery.colorbox-min.js");


		$search_title = new catTitleGUI("gev_course_search", "gev_course_search_desc", "GEV_img/ico-head-search.png");
		$form->setTitle($search_title->render());

		$title = new ilTextInputGUI($this->gLng->txt("title"), "title");
		$form->addItem($title);
		
		$custom_id = new ilTextInputGUI($this->gLng->txt("gev_course_id"), "custom_id");
		$form->addItem($custom_id);
		
		/*$type = new ilSelectInputGUI($this->gLng->txt("gev_course_type"), "type");
		$type->setOptions(gevCourseUtils::getTypeOptions());
		$form->addItem($type);*/
		
		$categorie = new ilSelectInputGUI($this->gLng->txt("gev_course_categorie"), "categorie");
		$categorie->setOptions(gevCourseUtils::getCategorieOptions());
		$form->addItem($categorie);
		
		$target_group = new ilSelectInputGUI($this->gLng->txt("gev_target_group"), "target_group");
		$target_group->setOptions(gevCourseUtils::getTargetGroupOptions());
		$form->addItem($target_group);

		$location = new ilSelectInputGUI($this->gLng->txt("udf_type_venueselect"), "location");
		$location->setOptions(gevCourseUtils::getLocationOptions());
		$form->addItem($location);
		
		/*$provider = new ilSelectInputGUI($this->gLng->txt("udf_type_providerselect"), "provider");
		$provider->setOptions(gevCourseUtils::getProviderOptions());
		$form->addItem($provider);*/
		
		$period = new ilDateDurationInputGUI($this->gLng->txt("time_segment"), "period");
		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$period->setStart($now);
		$one_year = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$one_year->increment(ilDateTime::YEAR, 1);
		$period->setEnd($one_year);
		$form->addItem($period);
		
		$this->search_form = $form;
		return $form;
	}
}
