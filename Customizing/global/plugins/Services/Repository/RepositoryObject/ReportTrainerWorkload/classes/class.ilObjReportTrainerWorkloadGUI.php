<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
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

	public static function transformResultRowXLSX($rec) {
		return self::transformResultRow($rec);
	}
}