<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "Attendance By OrgUnit"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
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

require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");


class gevAttendanceByOrgUnitGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = catTitleGUI::create()
						->title("gev_rep_attendance_by_orgunit_title")
						->subTitle("gev_rep_attendance_by_orgunit_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("org_unit", "title")
						->column("odbd", "gev_od_bd")
						//->column("above2", "above2")
						//->column("above1", "above1")
						->column("sum_employees", "sum_employees")
						
						->column("sum_booked_wbt", "sum_booked_WBT")
						->column("sum_attended_wbt", "sum_attended_WBT")
						
						->column("sum_booked", "sum_booked_nowbt")
						->column("sum_waiting", "sum_waiting")
						->column("sum_attended", "sum_attended_nowbt")
						->column("sum_excused", "sum_excused")
						->column("sum_unexcused", "sum_unexcused")
						->column("sum_exit", "sum_exit")
						
						->template("tpl.gev_attendance_by_orgunit_row.html", "Services/GEV/Reports")
						;

		$this->table_sums = catReportTable::create()
						->column("sum_employees", "sum_employees")
						->column("sum_booked_wbt", "sum_booked_WBT")
						->column("sum_attended_wbt", "sum_attended_WBT")
						->column("sum_booked", "sum_booked_nowbt")
						->column("sum_waiting", "sum_waiting")
						->column("sum_attended", "sum_attended_nowbt")
						->column("sum_excused", "sum_excused")
						->column("sum_unexcused", "sum_unexcused")
						->column("sum_exit", "sum_exit")

						->template("tpl.gev_attendance_by_orgunit_sums_row.html", "Services/GEV/Reports")
						;
		$this->summed_data = array();


		

		$this->order = catReportOrder::create($this->table)
						//->mapping("date", "crs.begin_date")
						//->mapping("odbd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("org_unit", "ASC")
						;
		
		//internal ordering:
		$this->internal_sorting_numeric = array(
			'sum_employees'
		);
		$this->internal_sorting_fields = array_merge(
			$this->internal_sorting_numeric,
			array(
		 	  'odbd'
			));



		$this->sql_sum_parts = array(

				"sum_booked" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.booking_status) = 'gebucht'
							AND LCASE(usrcrs.participation_status) = 'nicht gesetzt'
							AND crs.type != 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_booked",

					"sum_booked_wbt" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.booking_status) = 'gebucht'
							AND LCASE(usrcrs.participation_status) = 'nicht gesetzt'
							AND crs.type = 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_booked_wbt",


					"sum_waiting" => "SUM(
						CASE 
							WHEN usrcrs.booking_status = 'auf Warteliste'
							AND participation_status = 'nicht gesetzt'
						THEN 1
						END 
					) AS sum_waiting",

					"sum_attended" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'teilgenommen'
							AND crs.type != 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_attended",

					"sum_attended_wbt" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'teilgenommen'
							AND crs.type = 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_attended_wbt",


					"sum_excused" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'fehlt entschuldigt'
						THEN 1
						END 
					) AS sum_excused",


					"sum_unexcused" => " SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'fehlt ohne Absage'
						THEN 1
						END 
					) AS sum_unexcused",

					"sum_exit" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'canceled_exit'
						THEN 1
						END 
					) AS sum_exit"

			);




		$this->query = catReportQuery::create()
						//->distinct()

						->select("usr.org_unit")
						->select("usr.org_unit_above1")
						->select("usr.org_unit_above2")
						->select("usr.gender")

						->select("crs.venue")
						->select("crs.provider")

						/*->select("usrcrs.booking_status")
						->select("usrcrs.participation_status")
						->select("usr.user_id")
						->select("crs.crs_id")
						*/
						->select_raw($this->sql_sum_parts['sum_booked_wbt'])
						->select_raw($this->sql_sum_parts['sum_attended_wbt'])

						->select_raw($this->sql_sum_parts['sum_booked'])
						->select_raw($this->sql_sum_parts['sum_attended'])
						->select_raw($this->sql_sum_parts['sum_waiting'])
						->select_raw($this->sql_sum_parts['sum_excused'])
						->select_raw($this->sql_sum_parts['sum_unexcused'])
						->select_raw($this->sql_sum_parts['sum_exit'])


						->from("hist_usercoursestatus usrcrs")

						->join("hist_course crs")
							->on("usrcrs.crs_id = crs.crs_id")

						->join("hist_user usr")
							->on("usrcrs.usr_id = usr.user_id")

						->group_by("usr.org_unit")
						->compile()
						;


		$this->allowed_user_ids = $this->user_utils->getEmployees();

		$this->filter = catFilter::create()
		
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, " OR usrcrs.hist_historic IS NULL"
									)
						->multiselect( "org_unit"
									 , $this->lng->txt("gev_org_unit_short")
									 //, array("usr.org_unit", "org_unit_above1", "org_unit_above2")
									 , array("usr.org_unit")
									 , $this->user_utils->getOrgUnitNamesWhereUserIsSuperior()
									 , array()
									 )
						->multiselect("edu_program"
									 , $this->lng->txt("gev_edu_program")
									 , "edu_program"
									 , gevCourseUtils::getEduProgramsFromHisto()
									 , array()
									 )
						->multiselect("type"
									 , $this->lng->txt("gev_course_type")
									 , "type"
									 , gevCourseUtils::getLearningTypesFromHisto()
									 , array()
									 )
						->multiselect("template_title"
									 , $this->lng->txt("crs_title")
									 , "template_title"
									 , gevCourseUtils::getTemplateTitleFromHisto()
									 , array()
									 )
						->multiselect("participation_status"
									 , $this->lng->txt("gev_participation_status")
									 , "participation_status"
									 , gevCourseUtils::getParticipationStatusFromHisto()
									 , array()
									 )
						->multiselect("booking_status"
									 , $this->lng->txt("gev_booking_status")
									 , "booking_status"
									 , catFilter::getDistinctValues('booking_status', 'hist_usercoursestatus')
									 , array()
									 )
						->multiselect("venue"
									 , $this->lng->txt("gev_venue")
									 , "venue"
									 , catFilter::getDistinctValues('venue', 'hist_course')
									 , array()
									 )
						->multiselect("provider"
									 , $this->lng->txt("gev_provider")
									 , "provider"
									 , catFilter::getDistinctValues('provider', 'hist_course')
									 , array()
									 )

						->multiselect("gender"
									 , $this->lng->txt("gender")
									 , "gender"
									 , array('f', 'm')
									 , array()
									 )



						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))

						->static_condition(" usrcrs.hist_historic = 0")
						->static_condition(" usr.hist_historic = 0")
						->static_condition(" crs.hist_historic = 0")
										  
						  
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
		
		$tmpsql = "SELECT COUNT( * ) AS oumembers FROM hist_user"
				." WHERE org_unit = '" .$rec['org_unit'] ."'"
				." AND hist_historic = 0";
		$tmpres = $this->db->query($tmpsql);
		$tmprec = $this->db->fetchAssoc($tmpres);

		$rec['sum_employees'] = intval($tmprec['oumembers']);
		
		//$rec['sum_employees'] = 'many';

		foreach(array_keys($this->table_sums->columns) as $field) {
			if (! array_key_exists($field, $this->summed_data)) {
				$this->summed_data[$field] = 0;
			}
			
			$this->summed_data[$field] +=  intval($rec[$field]);
		}
			
		return $this->replaceEmpty($rec);
	}




	protected function renderView() {
		$main_table = $this->renderTable();
		return 	$this->renderSumTable()
				.$main_table;
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
