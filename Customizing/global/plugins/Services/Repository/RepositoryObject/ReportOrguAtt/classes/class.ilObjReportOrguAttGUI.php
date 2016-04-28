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
	static $od_regexp = null;
	static $bd_regexp = null;
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

	protected function settingsForm($data = null) {
		$settings_form = parent::settingsForm($data);
		$is_local = new ilCheckboxInputGUI($this->object->plugin->txt('report_is_local'),'is_local');
		$is_local->setValue(1);
		if(isset($data["is_local"])) {
			$is_local->setChecked($data["is_local"]);
		}
		$settings_form->addItem($is_local);

		$all_orgus_filter = new ilCheckboxInputGUI($this->object->plugin->txt('report_all_orgus')
			,'all_orgus_filter');
		$all_orgus_filter->setValue(1);
		if(isset($data["all_orgus_filter"])) {
			$all_orgus_filter->setChecked($data["all_orgus_filter"]);
		}
		$settings_form->addItem($all_orgus_filter);
		return $settings_form;
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

	protected function getRowTemplateTitle() {
		return "tpl.gev_attendance_by_orgunit_row.html";
	}

	public static function transformResultRow($rec) {

		foreach($rec as &$data) {
			if((string)$data === "0") {
				$data = '-';
			}
		}
		if(isset($rec['org_unit_above1'])) {
			if(!self::$od_regexp || !self::$bd_regexp ) {
				require_once './Services/ReportsRepository/config/od_bd_strings.php';
			}
			$orgu_above1 =  $rec['org_unit_above1'];
			$orgu_above2 =  $rec['org_unit_above2'];
			if (preg_match(self::$od_regexp, $orgu_above1)) {
				$od = $orgu_above1;
			} elseif(preg_match(self::$od_regexp, $orgu_above2)) {
				$od = $orgu_above2;
			} else {
				$od = '-';
			}

			if (preg_match(self::$bd_regexp, $orgu_above1)) {
				$bd = $orgu_above1;
			} elseif(preg_match(self::$bd_regexp, $orgu_above2)) {
				$bd = $orgu_above2;
			} else {
				$bd = '-';
			}
			$rec['odbd'] = $od .'/' .$bd;
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		foreach($rec as &$data) {
			if((string)$data === "0") {
				$data = '-';
			}
		}
		if(isset($rec['org_unit_above1'])) {
			if(!self::$od_regexp || !self::$bd_regexp ) {
				require_once './Services/ReportsRepository/config/od_bd_strings.php';
			}
			$orgu_above1 =  $rec['org_unit_above1'];
			$orgu_above2 =  $rec['org_unit_above2'];
			if (preg_match(self::$od_regexp, $orgu_above1)) {
				$od = $orgu_above1;
			} elseif(preg_match(self::$od_regexp, $orgu_above2)) {
				$od = $orgu_above2;
			} else {
				$od = '-';
			}

			if (preg_match(self::$bd_regexp, $orgu_above1)) {
				$bd = $orgu_above1;
			} elseif(preg_match(self::$bd_regexp, $orgu_above2)) {
				$bd = $orgu_above2;
			} else {
				$bd = '-';
			}
			$rec['odbd'] = $od .'/' .$bd;
		}

		return parent::transformResultRowXLSX($rec);
	}

	protected function getSettingsData() {
		$data = parent::getSettingsData();
		$data['is_local'] = $this->object->getIsLocal();
		$data['all_orgus_filter'] = $this->object->getAllOrgusFilter();
		return $data;
	}

	protected function saveSettingsData($data) {
		$this->object->setIsLocal($data['is_local']);
		$this->object->setAllOrgusFilter($data['all_orgus_filter']);
		parent::saveSettingsData($data);
	}
}