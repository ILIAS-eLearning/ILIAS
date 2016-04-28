<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportCompanyGlobalGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportCompanyGlobalGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI,
* @ilCtrl_Calls ilObjReportCompanyGlobalGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportCompanyGlobalGUI extends ilObjReportBaseGUI {
	static $rows = array("type", "part_book", "part_user", "wp_part", "book_book", "book_user");

	protected function afterConstructor() {
		parent::afterConstructor();
		$this->gTpl->addCSS('Services/ReportsRepository/templates/css/report.css');
	}

	public function getType() {
		return 'xrcg';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-rep-billing.png");
		return $a_title;
	}

	protected function renderUngroupedTable($data) {
		$table = $this->object->deliverTable();	
		$tpl_table = new ilTemplate("tpl.cat_global_company_report.html", true, true,  $this->object->plugin->getDirectory());
		$tpl_table->setCurrentBlock('row');
			$tpl_header = new ilTemplate("tpl.cat_global_company_report_header_row.html", true, true, $this->object->plugin->getDirectory());
			$tpl_header->setCurrentBlock('meta');
			$tpl_header->setVariable('HEADER_BOOK',$this->object->plugin->txt('header_book'));
			$tpl_header->setVariable('HEADER_PART',$this->object->plugin->txt('header_part'));
			$tpl_header->parseCurrentBlock();
		$tpl_table->setVariable('ROWCLASS','tblheader');	
		$tpl_table->setVariable('ROW',$tpl_header->get());
		$tpl_table->parseCurrentBlock();

		$tpl_table->setCurrentBlock('row');
			$tpl_header = new ilTemplate("tpl.cat_global_company_report_header_row.html", true, true, $this->object->plugin->getDirectory());
			$tpl_header->setCurrentBlock('main');
			foreach($table->columns as $column) {
				$variable = strtoupper($column[0]);
				$content = $column[2] ? $column[1] : $this->object->plugin->txt($column[1]);
				$tpl_header->setVariable($variable,$content);
			}
			$tpl_header->parseCurrentBlock();
		$tpl_table->setVariable('ROWCLASS','tblheader');	
		$tpl_table->setVariable('ROW',$tpl_header->get());
		$tpl_table->parseCurrentBlock();
		$rowclass = array('tblrow1','tblrow2');
		$i = 1;
		foreach($data as $type => $row) {	
			$tpl_table->setCurrentBlock('row');
			$tpl_row = new ilTemplate("tpl.cat_global_company_report_data_row.html", true, true, $this->object->plugin->getDirectory());
			foreach($row as $column => $data) {
				$tpl_row->setVariable(strtoupper($column),$data);
			}
			$tpl_table->setVariable('ROW',$tpl_row->get());
			$tpl_table->setVariable('ROWCLASS',$rowclass[$i % 2]);	
			$tpl_table->parseCurrentBlock();
			$i++;
		}
		return $tpl_table->get();
	}

	public static function transformResultRow($rec) {
		foreach (self::$rows as $key ) {
			if(!isset($rec[$key])) {
				$rec[$key] = '0';
			}
		}
		return $rec;
	}


	/**
	* the functionlaity of xls export must be extended someday. its not nice to replace the whole method just to add some metadata at the head of the export.
	*/
	protected function exportXLSX() {
		require_once "Services/Excel/classes/class.ilExcelUtils.php";
		require_once "Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$this->object->prepareReport();

		$adapter = new ilExcelWriterAdapter("Report.xls", true); 
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		//available formats within the sheet
		$format_bold = $workbook->addFormat(array("bold" => 1));
		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();
		$worksheet->writeString(0, 1, strip_tags($this->object->plugin->txt('header_book')), $format_bold);
		$worksheet->writeString(0, 3, strip_tags($this->object->plugin->txt('header_part')), $format_bold);
		//init cols and write titles
		$colcount = 0;
		foreach ($this->object->deliverTable()->all_columns as $col) {
			if ($col[4]) {
				continue;
			}
			$worksheet->setColumn($colcount, $colcount, 30); //width
			if (method_exists($this, "_process_xls_header") && $col[2]) {
				$worksheet->writeString(1, $colcount, $this->_process_xls_header($col[1]), $format_bold);
			}
			else {
				$worksheet->writeString(1, $colcount, $col[2] ? $col[1] : $this->lng->txt($col[1]), $format_bold);
			}
			$colcount++;
		}

		//write data-rows
		$rowcount = 2;
		$callback = get_class($this).'::transformResultRow';
		foreach ($this->object->deliverData($callback) as $entry) {
			$colcount = 0;
			foreach ($this->object->deliverTable()->all_columns as $col) {
				if ($col[4]) {
					continue;
				}
				$k = $col[0];
				$v = $entry[$k];

				$method_name = '_process_xls_' .$k;
				if (method_exists($this, $method_name)) {
					$v = $this->$method_name($v);
				}
				$worksheet->write($rowcount, $colcount, $v, $format_wrap);
				$colcount++;
			}
			$rowcount++;
		}
		$workbook->close();		
	}
}