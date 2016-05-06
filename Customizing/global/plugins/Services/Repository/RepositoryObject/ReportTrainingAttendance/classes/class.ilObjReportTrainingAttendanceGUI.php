<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';

/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainingAttendanceGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainingAttendanceGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportTrainingAttendanceGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrainingAttendanceGUI extends ilObjReportBaseGUI {
	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		if ($this->loadFilterSettings()) {
			$a_title->setCommand( $this->plugin->txt("create_new_report")
								, $this->gCtrl->getLinkTarget($this, "newReport"));
		}
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public function afterConstructor() {
		parent::afterConstructor();
		$this->filter_settings = false;
	}

	public function performCustomCommand($cmd) {
		if ($cmd == "newReport") {
			$this->flushFilterSettings();
			$this->gCtrl->redirect($this, "showContent");
		}
		return false;
	}

	const FILTER_SESSION_VAR = "xrta_filter";

	public function loadFilterSettings() {
		if ($this->filter_settings !== false) {
			return $this->filter_settings;
		}

		$tmp = ilSession::get(self::FILTER_SESSION_VAR);
		if ($tmp !== null) {
			$this->filter_settings =  unserialize($tmp);
		}
		else {
			$this->filter_settings = null;
		}

		return $this->filter_settings;
	}

	public function saveFilterSettings($settings) {
		ilSession::set(self::FILTER_SESSION_VAR, serialize($settings));
		$this->filter_settings = $settings;
	}

	public function flushFilterSettings() {
		ilSession::clear(self::FILTER_SESSION_VAR);
		$this->filter_settings = null;
	}

	public function getType() {
		return 'xrta';
	}

	protected function render() {
		$fs = $this->loadFilterSettings();
		$this->gTpl->setTitle(null);

		$res = ($this->title !== null ? $this->title->render() : "");

		if ($fs === null) {
			$res .= $this->renderFilter();
		}
		else {
			// TODO: this is really dirty. we need to make this better when
			// enhancing report plugins
			$this->object->filter_settings = $this->loadFilterSettings();
			$res .= ($this->spacer !== null ? $this->spacer->render() : "")
					. $this->renderFilterInfo()
					. ($this->spacer !== null ? $this->spacer->render() : "")
					. $this->renderTable();
		}

		return $res;
	}

	protected function renderFilterInfo() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		$form = new ilPropertyFormGUI();
		$form->setTitle(null);
		$form->setFormAction(null);

		$settings = call_user_func_array(array($this->object->filter(), "content"), $this->filter_settings);
		$crs_utils = gevCourseUtils::getInstance($settings["template_obj_id"]);

		$begin = new ilDate($settings["start"]->format("Y-m-d"), IL_CAL_DATE);
		$end = new ilDate($settings["end"]->format("Y-m-d"), IL_CAL_DATE);

		$vals = array(
			  array($this->plugin->txt("template_choice_label")
					, $crs_utils->getTitle()." (".$crs_utils->getType().")"
					)
			, array( $this->plugin->txt("dateperiod_choice_label")
					, ilDatePresentation::formatPeriod($begin, $end)
					)
			, array( $this->plugin->txt("org_units")
					, implode(", ", array_map(function($id) {
								$orgus = $this->object->getOrguOptions();
								return $orgus[$id];
						}, $settings["orgu_ids"] ? $settings["orgu_ids"] : array()))
					)
			, array( $this->plugin->txt("roles")
					, implode(", ", array_map(function($id) {
								$roles = $this->object->getRoleOptions();
								return $roles[$id];
						}, $settings["role_ids"] ? $settings["role_ids"] : array()))
					)
			);

		foreach ($vals as $val) {
			if (!$val[1]) {
				continue;
			}
			$field = new ilNonEditableValueGUI($val[0], "", true);
			$field->setValue($val[1]);
			$form->addItem($field);
		}

		return $form->getHTML();
	}

	public static function transformResultRow($rec) {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		if ($rec["participated"] == "Ja") {
			$begin = new ilDate($rec["part_begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["part_end_date"], IL_CAL_DATE);
			$rec['participated_date'] = ilDatePresentation::formatPeriod($begin, $end);
		} else {
			$rec['participated_date'] = "-";
		}
		if ($rec["booked"] == "Ja") {
			$begin = new ilDate($rec["book_begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["book_end_date"], IL_CAL_DATE);
			$rec['booked_for_date'] = ilDatePresentation::formatPeriod($begin, $end);
		} else {
			$rec['booked_for_date'] = "-";
		}

		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		if ($rec["participated"] == "Ja") {
			$begin = new ilDate($rec["part_begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["part_end_date"], IL_CAL_DATE);
			$rec['participated_date'] = ilDatePresentation::formatPeriod($begin, $end);
		} else {
			$rec['participated_date'] = "-";
		}
		if ($rec["booked"] == "Ja") {
			$begin = new ilDate($rec["book_begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["book_end_date"], IL_CAL_DATE);
			$rec['booked_for_date'] = ilDatePresentation::formatPeriod($begin, $end);
		} else {
			$rec['booked_for_date'] = "-";
		}

		return parent::transformResultRowXLSX($rec);
	}


	protected function getPOST() {
		return $_POST;
	}

	protected function renderFilter() {
		$filter = $this->object->filter();
		$display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \Cat\Filter\TypeFactory
						);
		$post = $this->getPOST();
		if ($post["filter"] === null) {
			$post["filter"] = array();
		}

		$next = $display->getNextFilterGUI($filter, $post["filter"]);

		if ($next) {
			require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
			$form = new ilPropertyFormGUI();
			$form->setTitle($this->plugin->txt("filter_form_title"));
			$form->setFormAction($this->gCtrl->getFormAction($this));
			$form->addCommandButton("showContent", "Weiter");

			$next->fillForm($form);
			$next->addHiddenInputs($form, $post["filter"]);

			return $form->getHTML();
			
		}
		else {
			$settings = $display->buildFilterValues($filter, $post["filter"]);
			$this->saveFilterSettings($settings);
			$this->gCtrl->redirect($this, "showContent");
		}
	}

	protected function exportExcel() {
		$this->object->filter_settings = $this->loadFilterSettings();
		parent::exportExcel();
	}
}