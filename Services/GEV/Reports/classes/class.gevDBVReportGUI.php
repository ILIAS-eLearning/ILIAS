<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "DBV Report"
* for Generali
*
* @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
* @version	$Id$
*
*
*/

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");


class gevDBVReportGUI extends catBasicReportGUI{
	protected $summed_data = array();
	//static $to_sum = array("credit_points" ,"max_points");
	public function __construct() {
		
		parent::__construct();
		$viewer = 33892;
		//$to_sum = array("credit_points" ,"max_points");

		foreach ($tosum as $value) {
			$this->summed_data[$values] = 0;
		}

		$this->title = catTitleGUI::create()
						->title("gev_rep_dbv_report_title")
						->subTitle("gev_rep_dbv_report_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("odbd", "gev_od_bd")
						->column("job_number", "job_number")
						->column("training_title", "title")
						->column("dbv_hot_topic", "dbv_hot_topic")
						->column("type", "type")
						->column("date", "date")
						->column("credit_points", "credit_points")
						->column("max_credit_points", "max_credit_points")
						->template("tpl.gev_dbv_report_row.html", "Services/GEV/Reports")
						;
		$this->table_sums = catReportTable::create()
						->column("sum_credit_points", "sum_credit_points")
						->column("sum_max_credit_points", "sum_max_credit_points")
						->template("tpl.gev_dbv_report_sum_row.html", "Services/GEV/Reports")
						;
		


		

	/*	$this->order = catReportOrder::create($this->table)
						//->mapping("date", "crs.begin_date")
						//->mapping("odbd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("lastname", "ASC")
						;*/
		
		//internal ordering:
		$this->internal_sorting_numeric = array(
			'lastname'
		);
		$this->internal_sorting_fields = array_merge(
			$this->internal_sorting_numeric,
			array(
		 	  'odbd'
			));

		$this->query = catReportQuery::create()
						//->distinct()

						->select("hu.lastname")
						->select("hu.firstname")
						->select("hu.org_unit_above1")
						->select("hu.org_unit_above2")
						->select("hu.job_number")
						->select("hc.title")
						->select("hc.dbv_hot_topic")
						->select("hc.type")
						->select("hc.begin_date")
						->select("hc.end_date")
						->select_raw(
							"IF(hucs.participation_status = 'bestanden',hucs.credit_points,0) credit_points")
						->select_raw(
							"IF(hucs.participation_status = 'nicht gesetzt',hc.max_credit_points,
								hucs.credit_points) max_credit_points")
						->from("org_unit_personal oup")
						->join("object_reference ore")
							->on("oup.orgunit_id = ore.obj_id")
						->join("object_data oda")
							->on("CONCAT( 'il_orgu_employee_', ore.ref_id ) = oda.title")
						->join("rbac_ua rua")
							->on("rua.rol_id = oda.obj_id")
						->join("hist_user hu")
							->on("rua.usr_id = hu.user_id")						
						->join("hist_usercoursestatus hucs")
							->on("hu.user_id = hucs.usr_id")
						->join("hist_course hc")
							->on("hucs.crs_id = hc.crs_id")
						->compile()
						;


		$this->filter = catFilter::create()
						->static_condition("oup.usr_id = ".$this->db->quote($viewer, "integer"))
						->static_condition("oda.type = 'role'")
						->static_condition("hu.hist_historic = 0")
						->static_condition("hucs.hist_historic = 0")
						->static_condition("hc.hist_historic = 0")
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
	}

	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}

	protected function transformResultRow($rec) {
		$rec['odbd'] = $rec['org_unit_above2'] .'/' .$rec['org_unit_above1'];

		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )) {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} else {
			$date = '-';
		}
		$rec['date'] = $date;
		/*foreach (self::$tosum as $value) {
			$this->summed_data[$value] += is_numeric($rec[$value]) ? $rec[$value] : 0;
		}*/
		return $this->replaceEmpty($rec);
	}

	protected function renderView() {
		$main_table = $this->renderTable();
		//return 	$this->renderSumTable()
		//		.$main_table;
		return $main_table;
	}

	private function renderSumTable(){
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate(
			$this->table_sums->row_template_filename, 
			$this->table_sums->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table_sums->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}		

		$cnt = 1;
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);

		if(count($this->summed_data) == 0) {
			foreach(array_keys($this->table_sums->columns) as $field) {
				$this->summed_data[$field] = 0;
			}
		}

		$table->setData(array($this->summed_data));
		return $table->getHtml();
	}




}

?>