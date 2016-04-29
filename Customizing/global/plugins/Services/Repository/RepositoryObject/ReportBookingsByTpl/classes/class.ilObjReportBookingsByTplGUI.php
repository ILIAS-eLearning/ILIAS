<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportBookingsByTplGUI : ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportBookingsByTplGUI : ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportBookingsByTplGUI : ilCommonActionDispatcherGUI
*/
class ilObjReportBookingsByTplGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xrbt';
	}
	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	protected function render() {
		$this->gTpl->setTitle(null);
		return 	($this->title !== null ? $this->title->render() : "")
				. ($this->object->deliverFilter() !== null ? $this->object->deliverFilter()->render() : "")
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderSumTable()
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderTable();
	}

	private function renderSumTable(){
		$table = new catTableGUI($this, "showContent");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$sum_table = $this->object->deliverSumTable();
		$table->setRowTemplate(
			$sum_table->row_template_filename, 
			$sum_table->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		$cnt = 1;
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);
		foreach ($sum_table->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->object->plugin->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}
		$callback = get_class($this).'::transformResultRow';
		$table = $this->object->insertSumData($table,$callback);

		$this->enableRelevantParametersCtrl();
		$return = $table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}

	public static function transformResultRow($rec) {

		foreach($rec as &$data) {
			if((string)$data === "0") {
				$data = '-';
			}
		}
		return parent::transformResultRow($rec);
	}
}