<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
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
					. $this->renderTable();
		}

		return $res;
	}

	public static function transformResultRow($rec) {
		if ($rec["participated"] == "Ja") {
			$rec['participated_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
			$rec['booked_for_date'] = "-";
		}
		else {
			$rec['participated_date'] = "-";
			$rec['booked_for_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
		}

		return parent::transformResultRow($rec);
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

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$is_online = new ilCheckboxInputGUI('online','online');
		$is_online->setValue(1);
		if(isset($data["online"])) {
			$is_online->setChecked($data["online"]);
		}
		$settings_form->addItem($is_online);

		$show_filter = new ilCheckboxInputGUI('filter','filter');
		$show_filter->setValue(1);
		if(isset($data["filter"])) {
			$show_filter->setChecked($data["filter"]);
		}
		$settings_form->addItem($show_filter);

		return $settings_form;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data["online"] = $this->object->getOnline();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setOnline($data["online"]);
		parent::saveSettingsData($data);
	}
}