<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* base class for ReportGUIs 
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Reports/classes/class.catFilter.php");


class catBasicReportGUI {

	public function __construct() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Reports/classes/class.gevReportingPermissions.php");

		global $lng, $ilCtrl, $tpl, $ilUser, $ilDB;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->db = &$ilDB;
		$this->user = $ilUser;
		$this->user_utils = gevUserUtils::getInstance($this->user->getId());

		$this->title = null;
		$this->table = null;
		$this->query = null;
		$this->data = false;
		$this->filter = null;
		
		//watch out for sorting of special fields, i.e. dates shown as a period of time.
		//to avoid the ilTable-sorting, set this too true.
		//i.e. applies to: _table_nav=date:asc:
		$this->external_sorting = false;

		$this->permissions = gevReportingPermissions::getInstance($this->user->getId());
	}
	

	public function executeCommand() {
		$this->checkPermission();

		$cmd = $this->ctrl->getCmd();
		$res = $this->executeCustomCommand($cmd);
		if ($res !== null) {
			return $res;
		}
		
		switch ($cmd) {
			case "exportxls":
				$this->exportXLS();
				//no "break;" !
			default:
				return $this->render();
		}
	}

	protected function executeCustomCommand($a_cmd) {
		return null;
	}
	
	protected function checkPermission() {
		if( $this->userIsPermitted() ) { 
			return;
		}
		
		ilUtil::sendFailure($this->lng->txt("no_report_permission"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}

	protected function userIsPermitted () {
		return $this->user_utils->isAdmin() || $this->user_utils->isSuperior();
	}

	
	protected function render() {
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");

		$spacer = new catHSpacerGUI();
		
		return    ($this->title !== null ? $this->title->render() : "")
				. ($this->filter !== null ? $this->filter->render() : "")
				. $spacer->render()
				. $this->renderView()
				;
	}

	protected function renderView() {
		return $this->renderTable();
	}

	protected function renderTable() {
		if ($this->table === null) {
			throw new Exception("catBasicReport::renderTable: you need to define a table.");
		}

		require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");

		$this->ctrl->setParameter($this, $this->filter->getGETName(), $this->filter->encodeSearchParamsForGET());
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate(
			$this->table->row_template_filename, 
			$this->table->row_template_module
		);

		$process = array();

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table->columns as $col) {
			$table->addColumn( $col[3] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[4]
							 );
		}
		
		// TODO: This should be implemented via ORDER BY in sql.
		if ($this->table->order_field !== null) {
			$table->setOrderField($this->table->order_field);
			$table->setOrderDirection($this->table->order_direction);
		}
		
		$data = $this->getData();
		$cnt = count($data);
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);
		$table->setExternalSorting($this->external_sorting);

		$table->setData($data);

		//export-button
		$export_btn = '<a class="submit exportXlsBtn"'
					. 'href="'
					.$this->ctrl->getLinkTarget($this, "exportxls")
					.'">'
					.$this->lng->txt("gev_report_exportxls")
					.'</a>';

		return	 $export_btn
				.$table->getHTML()
				.$export_btn;
	}

	protected function exportXLS() {
		require_once "Services/Excel/classes/class.ilExcelUtils.php";
		require_once "Services/Excel/classes/class.ilExcelWriterAdapter.php";
		
		$data = $this->getData();

		$adapter = new ilExcelWriterAdapter("Report.xls", true); 
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		//available formats within the sheet
		$format_bold = $workbook->addFormat(array("bold" => 1));
		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();
		
		//init cols and write titles
		$colcount = 0;
		foreach ($this->table->columns as $col) {
			$worksheet->setColumn($colcount, $colcount, 30); //width
			$worksheet->writeString(0, $colcount, $col[3] ? $col[1] : $this->lng->txt($col[1]), $format_bold);
			$colcount++;
		}

		//write data-rows
		$rowcount = 1;
		foreach ($data as $entry) {
			$colcount = 0;
			foreach ($this->table->columns as $col) {
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

	protected function queryWhere() {
		if ($this->filter === null) {
			return " WHERE TRUE";
		}
		
		return " WHERE ".$this->filter->getSQL();
	}
	
	protected function getData(){ 
		if ($this->data == false){
			$this->data = $this->fetchData();
		}
		return $this->data;
	}

	protected function fetchData() {
		if ($this->query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		
		$query = $this->query->sql()
			   . $this->queryWhere()
			   ;
		
		$res = $this->db->query($query);
		$data = array();
		
		while($rec = $this->db->fetchAssoc($res)) {
			$data[] = $this->transformResultRow($rec);
		}
		
		return $data;
	}
	
	protected function transformResultRow($a_row) {
		return $a_row;
	}
}




class catReportTable {
	protected function __construct() {
		$this->columns = array();
		$this->row_template_filename = null;
		$this->row_template_module = null;
		$this->order_field = null;
		$this->order_direction = null;
	}
	
	public static function create() {
		return new catReportTable();
	}
	
	public function column($a_id, $a_title, $a_sql_name = false, $a_no_lng_var = false, $a_width = "") {
		$this->columns[] = array( $a_id
								, $a_title
								, ($a_sql_name === false) ? $a_sql_name : $a_id
								, $a_no_lng_var
								, $a_width
								);
		return $this;
	}
	
	public function order($a_field, $a_direction) {
		if (!in_array($a_direction, array("asc", "ASC", "desc", "DESC"))) {
			throw new Exception("catReportTable::order: Expected ASC or DESC for direction.");
		}
		
		$this->order_field = $a_field;
		$this->order_direction = $a_direction;
		
		return $this;
	}
	
	public function template($a_filename, $a_module) {
		$this->row_template_filename = $a_filename;
		$this->row_template_module = $a_module;
		return $this;
	}
}




class catReportQuery {
	protected function __construct() {
		$this->fields = array();
		$this->_from = null;
		$this->joins = array();
		$this->compiled = false;
		$this->sql_str = null;
		$this->sql_from = null;
		$this->_distinct = false;
	}
	
	public static function create() {
		return new catReportQuery();
	}
	
	public function distinct() {
		$this->_distinct = true;
		return $this;
	}
	
	public function select($a_field) {
		$this->checkNotCompiled();
		
		if (!is_array($a_field)) {
			$this->fields[] = $a_field;
		}
		else {
			$this->fields = array_merge($this->fields, $a_fields);
		}
		return $this;
	}
	
	public function from($a_table) {
		$this->checkNotCompiled();
		if ($this->_from !== null) {
			throw new Exception("catReportQuery::from: already defined.");
		}
		
		$this->_from = $a_table;
		return $this;
	}
	
	
	public function join($a_table) {
		$this->checkNotCompiled();
		return new catReportQueryOn($this, $a_table);
	}
	
	public function sql() {
		if( $this->sql_str !== null) {
			return $this->sql_str;
		}
		$this->checkCompiled("sql");

		$escp = array();
		foreach ($this->fields as $field) {
			$escp[] = catFilter::quoteDBId($field);
		}

		$this->sql_str = 
			 "SELECT "
			.($this->_distinct ? "DISTINCT " : "")
			.implode("\n\t,", $escp)
			.$this->sqlFrom()
			;
			
		return $this->sql_str;
	}
	
	public function sqlFrom() {
		if ($this->sql_from === null) {
			$this->sql_from =
				 "\n FROM ".$this->_from[0]." ".$this->_from[1]
				.implode("\n ", $this->joins);
		}
		
		return $this->sql_from;
	}
	
	public function compile() {
		$this->checkNotCompiled();
		
		if (count($this->fields) === 0) {
			throw new Exception("catReportQuery::compile: No fields defined.");
		}
		if ($this->_from === null) {
			throw new Exception("catReportQuery::compile: No FROM-table defined.");
		}

		$this->_from = $this->rectifyTableName("from", $this->_from);
		foreach($this->joins as $key => $value) {
			$tab = $this->rectifyTableName("join", $value[0]);
			$this->joins[$key] = " JOIN ".$tab[0]." ".$tab[1]." ON ".$value[1]." ";
		}
		
		$this->compiled = true;
		
		return $this;
	}
	
	protected function checkNotCompiled() {
		if ($this->compiled) {
			throw new Exception("catReportQuery::checkCompiled: Don't modify a filter you already compiled.");
		}
	}

	protected function checkCompiled($a_what) {
		if (!$this->compiled) {
			throw new Exception("catReportQuery::checkCompiled: Don't ".$a_what." a filter you did not compile.");
		}
	}
	
	protected function rectifyTableName($a_what, $name) {
		$spl = explode(" ", $name);
		if (count($spl) > 2) {
			throw new Exception("catReportQuery::rectifiyTableName: Expected ".$a_what." to contain one space at most.");
		}
		if (count($spl) == 1) {
			$spl[] = "";
		}
		
		$spl[0] = catFilter::quoteDBId($spl[0]);
		
		return $spl;
	}
}

class catReportQueryOn {
	public function catReportQueryOn($a_query, $a_table) {
		$this->query = $a_query;
		$this->table = $a_table;
	}
	
	public function on($a_condition) {
		$this->query->joins[] = array($this->table, $a_condition);
		return $this->query;
	}
}

?>