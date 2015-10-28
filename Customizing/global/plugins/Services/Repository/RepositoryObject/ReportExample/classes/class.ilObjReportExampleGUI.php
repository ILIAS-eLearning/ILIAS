<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportExampleGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportExampleGUI extends ilObjReportBaseGUI {
	protected $form;

	protected function afterConstructor() {
		parent::afterConstructor();
		$this->settings_form = new ilPropertyFormGUI();
		$this->settings_form->setFormAction($this->gCtrl->getLinkTarget($this, "saveSettings"));
	}



	public function getType() {
		return 'xrts';
	}


	protected function renderSettings() {
		$is_online = new ilCheckboxInputGUI('online','online');
		$is_online->setValue(1);
		$is_online->setChecked(0);
		if($this->object->getOnline()) {
			$is_online->setChecked(1);
		}
		$this->settings_form->addItem($is_online);

		$show_filter = new ilCheckboxInputGUI('filter','filter');
		$show_filter->setValue(1);
		$show_filter->setChecked(0);

		if($this->object->getShowFilter()) {
			$show_filter->setChecked(1);
		}
		$this->settings_form->addItem($show_filter);

		$this->settings_form->addCommandButton("saveSettings", $this->gLng->txt("save"));
		$this->gTpl->setContent($this->settings_form->getHtml());
	}

	protected function saveSettings() {

		$this->object->setOnline($_POST["online"]);


		$this->object->setShowFilter($_POST["filter"]);

		$this->object->doUpdate();
		$this->object->update();
		$this->renderSettings();
	}


}