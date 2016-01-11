<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportOrguAttGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportOrguAttGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportOrguAttGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportOrguAttGUI extends ilObjReportBaseGUI {

	public function getType() {
		return 'xroa';
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
		$table->setRowTemplate(
			$this->object->deliverSumTable()->row_template_filename, 
			$this->object->deliverSumTable()->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->object->deliverSumTable()->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->object->plugin->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}		
		$cnt = 1;
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);

		$table = $this->object->insertSumData($table);

		$this->enableRelevantParametersCtrl();
		$return = $table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_attendance_by_orgunit_row.html";
	}
}