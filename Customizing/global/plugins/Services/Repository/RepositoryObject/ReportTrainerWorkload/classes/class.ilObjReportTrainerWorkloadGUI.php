<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainerWorkloadGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrainerWorkloadGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xrtw';
	}

	protected function afterConstructor() {
		parent::afterConstructor();
		if($this->object->plugin) {
			$this->tpl->addCSS($this->object->plugin->getStylesheetLocation('report.css'));
		}
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);

		$annual_norm_training = new ilNumberInputGUI($this->object->plugin->txt('annual_norm_training'),'annual_norm_training');
		if(isset($data['annual_norm_training'])) {
			$annual_norm_training->setValue($data['annual_norm_training']);
		}
		$settings_form->addItem($annual_norm_training);

		$annual_norm_operation = new ilNumberInputGUI($this->object->plugin->txt('annual_norm_operation'),'annual_norm_operation');
		if(isset($data['annual_norm_operation'])) {
			$annual_norm_operation->setValue($data['annual_norm_operation']);
		}
		$settings_form->addItem($annual_norm_operation);

		$annual_norm_office = new ilNumberInputGUI($this->object->plugin->txt('annual_norm_office'),'annual_norm_office');
		if(isset($data['annual_norm_office'])) {
			$annual_norm_office->setValue($data['annual_norm_office']);
		}
		$settings_form->addItem($annual_norm_office);

		return $settings_form;
	}

	protected function renderTable() {
		$table = parent::renderTable();
		$sum_table = $this->renderSumTable();
		return $sum_table.$table;
	}

	private function renderSumTable(){
		
		$table = new catTableGUI($this, "showContent");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table_sums = $this->object->deliverSumtable();

		$table->setRowTemplate(
			$table_sums->row_template_filename, 
			$table_sums->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($table_sums->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}

		$sum_row = $this->object->fetchSumData();	
		if(count($sum_row) == 0) {
			foreach(array_keys($table_sums->columns) as $field) {
				$sum_row[$field] = 0;
			}
		}

		$table->setData(array($sum_row));
		$this->enableRelevantParametersCtrl();
		$return = $table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data["annual_norm_training"] = $this->object->getAnnualNormTraining();
		$data["annual_norm_operation"] = $this->object->getAnnualNormOperation();
		$data["annual_norm_office"] = $this->object->getAnnualNormOffice();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setAnnualNormTraining($data["annual_norm_training"]);
		$this->object->setAnnualNormOperation($data["annual_norm_operation"]);
		$this->object->setAnnualNormOffice($data["annual_norm_office"]);
		parent::saveSettingsData($data);
	}

	public static function transformResultRow($rec) {
		global $ilCtrl;
		foreach ($rec as $key => &$value) {
			if($key != 'fullname') {
				if(strpos($key,'_workload') === false) {
					$value = number_format($value,2,',','.');
				} else {
					$value = number_format($value,0,',','.');
				}
			}
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLS($rec) {
		return self::transformResultRow($rec);
	}
}