<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';



class ilObjReportBill extends ilObjReportBase {
	
	const REPORT_MODE_GEV = 0;
	const REPORT_MODE_VFS = 1;
	const REPORT_MODE_ADMIN = 2;

	static $config =
		array( 	self::REPORT_MODE_GEV => 	
					array("label" => "GEV","tpl" => "tpl.gev_billing_row_gev.html")
				,self::REPORT_MODE_VFS => 	
					array("label" => "VFS","tpl" => "tpl.gev_billing_row_vfs.html")
				,self::REPORT_MODE_ADMIN => 
					array("label" => "Admin","tpl" => "tpl.gev_billing_row_admin.html")
			);


	protected $online;
	protected $report_mode;
	protected $show_filter;
	protected $is_vfs = 0;
	protected $is_gev = 0;
	protected $relevant_parameters = array();
	

	public function initType() {
		 $this->setType("xrbi");
	}


	protected function createLocalReportSettings() {
		$options = array();
		foreach (self::$config as $key => $label) {
			$options[$key] = $label["label"];
		}
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rbi')
				->addSetting($this->s_f
								->settingListInt('report_mode', $this->plugin->txt('report_mode'))
								->setOptions($options)
							);

	}

	protected function buildQuery($query) {
		return null;
	}

	protected function buildFilter($filter) {
		$filter	->dateperiod( "period"
							, $this->lng->txt("gev_period")
							, $this->lng->txt("gev_until")
							, "usrcrs.begin_date"
							, "usrcrs.end_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							)
				->dateperiod( "created"
							, $this->lng->txt("gev_created_since")
							, $this->lng->txt("gev_created_till")
							, "bill.bill_finalized_date"
							, "bill.bill_finalized_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							, true
							)
				->static_condition("bill.bill_final = 1")
				->action($this->filter_action)
				->compile()
				;
		return $filter;
	}

	protected function fetchData(callable $callback){
		$created = $this->filter->get("created");
		if ($created["end"]->get(IL_CAL_UNIX) < $created["start"]->get(IL_CAL_UNIX) ) {
			return array();
		}
		
		//fetch retrieves the data 
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
		$data = array();


		//when ordering the table, watch out for date!
		//_table_nav=date:asc:0
		//btw, what is the third parameter?
		if(isset($_GET['_table_nav'])){
			$this->external_sorting = true; //set to false again, 
											//if the field is not relevant

			$table_nav_cmd = split(':', $_GET['_table_nav']);
			
			if ($table_nav_cmd[1] == "asc") {
				$direction = " ASC";
			}
			else {
				$direction = " DESC";
			}
			
			switch ($table_nav_cmd[0]) { //field
				case 'date':
					$direction = strtoupper($table_nav_cmd[1]);
					$sql_order_str = " ORDER BY crs.begin_date ";
					$sql_order_str .= $direction;
					break;
				
				//append more fields, simply for performance...

				default:
					$this->external_sorting = true;
					$sql_order_str = " ORDER BY ".$this->gIldb->quoteIdentifier($table_nav_cmd[0])." ".$direction;
					break;
			}
		}
		$query = $this->buildQueryStatement()
					. $sql_order_str;


		$bill_link_icon = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_bill.png").'" />';

		$res = $this->gIldb->query($query);

		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
		}

		return $data;
	}

	public function buildQueryStatement() {
		if(self::REPORT_MODE_VFS === (int)$this->settings['report_mode']) {
			$this->is_vfs = 1;
		}
		if(self::REPORT_MODE_GEV === (int)$this->settings['report_mode']) {
			$this->is_gev = 1;
		}
		if(self::REPORT_MODE_ADMIN === (int)$this->settings['report_mode']) {
			$this->is_vfs = 1;
			$this->is_gev = 1;
		}
		$is_vfs_range = ($this->is_vfs xor $this->is_gev) ? array($this->is_vfs) : 
			(($this->is_vfs && $this->is_gev) ? array(0 ,1) : array());
		$filter_vfs = 
			"(SELECT f_usr_row.crs_id, f_usr_row.usr_id, f_usr.is_vfs FROM "
			."	(SELECT MAX(f_usr.row_id) as row_id, f_usrcrs.crs_id, f_usrcrs.usr_id "
			."		FROM hist_usercoursestatus f_usrcrs "
			."		JOIN hist_user f_usr "
			."			ON f_usr.user_id = f_usrcrs.usr_id AND f_usr.created_ts < f_usrcrs.created_ts "
			."		GROUP BY f_usrcrs.crs_id, f_usrcrs.usr_id) AS f_usr_row "
			."JOIN hist_user f_usr ON f_usr.row_id = f_usr_row.row_id WHERE ".$this->gIldb->in("is_vfs", $is_vfs_range, false, "integer")." ) AS filter_vfs ";
		$filter_vfs = " JOIN ".$filter_vfs." ON filter_vfs.crs_id = usrcrs.crs_id AND filter_vfs.usr_id = usrcrs.usr_id";

		$query = 	 "SELECT  bill.bill_number as billnumber"
					."		, IF(usrcrs.participation_status='fehlt entschuldigt' OR usrcrs.booking_status='kostenpflichtig storniert',
								 'fehlt entschuldigt/kostenpflichtig storniert', usrcrs.participation_status) as participation_status"
					."		, ROUND(SUM(IF(item.billitem_context_id = bill.bill_context_id, item.billitem_pta, 0)), 2) as fee_pretax"
					."		, ROUND(SUM(IF(item.billitem_context_id = bill.bill_context_id, item.billitem_pta, 0)) * bill.bill_vat/100, 2) as fee_tax"
					."		, ROUND(SUM(IF(item.billitem_context_id = bill.bill_context_id, item.billitem_pta, 0)) * (1 + bill.bill_vat/100), 2) as fee_posttax"
					."		, ROUND(SUM(IF(item.billitem_context_id IS NULL, item.billitem_pta, 0)), 2) as coupon_pretax"
					."		, ROUND(SUM(IF(item.billitem_context_id IS NULL, item.billitem_pta, 0)) * bill.bill_vat/100, 2) as coupon_tax"
					."		, ROUND(SUM(IF(item.billitem_context_id IS NULL, item.billitem_pta, 0)) * (1 + bill.bill_vat/100), 2) as coupon_posttax"
					."		, ROUND(SUM(item.billitem_pta), 2) as amount_pretax"
					."		, ROUND(SUM(item.billitem_pta) * bill.bill_vat/100, 2) as amount_tax"
					."		, ROUND(SUM(item.billitem_pta) * (1 + bill.bill_vat/100), 2) as amount_posttax"
					."		, bill.bill_cost_center as cost_center"
					."      , DATE_FORMAT(FROM_UNIXTIME(bill.bill_finalized_date), '%d.%m.%Y') as bill_finalized_date"
					."		, usr.firstname as firstname"
					."		, usr.lastname as lastname"
					." 		, usr.gender as gender"
					."		, usr.org_unit as org_unit"
					."		, crs.title as title";
		if($this->is_vfs ) {
			$query .="      , usr.adp_number";
		}
		$query .=	"       , filter_vfs.is_vfs"
					."		, crs.custom_id as custom_id"
					."		, crs.begin_date as begin_date"
					."		, crs.end_date as end_date"
					."		, crs.venue as venue"
					." FROM  bill "
					." INNER JOIN hist_course crs ON crs.crs_id = bill.bill_context_id AND crs.hist_historic = 0"
					." INNER JOIN hist_user usr ON usr.user_id = bill.bill_usr_id AND usr.hist_historic = 0"
					." INNER JOIN hist_usercoursestatus usrcrs ON usrcrs.usr_id = bill.bill_usr_id AND usrcrs.crs_id = bill.bill_context_id AND usrcrs.hist_historic = 0";
		$query .=	$filter_vfs;
		$query .=	" RIGHT JOIN billitem item ON bill.bill_pk = item.bill_fk"
					. $this->queryWhere()
					." GROUP BY bill.bill_number "
					. $sql_order_str;
		return $query;
	}

	protected function getRowTemplateTitle() {
		return self::$config[$this->settings["report_mode"]]["tpl"];
	}

	protected function buildTable($table) {
		if(self::REPORT_MODE_VFS === (int)$this->settings['report_mode']) {
			$this->is_vfs = 1;
		}
		if(self::REPORT_MODE_GEV === (int)$this->settings['report_mode']) {
			$this->is_gev = 1;
		}
		if(self::REPORT_MODE_ADMIN === (int)$this->settings['report_mode']) {
			$this->is_vfs = 1;
			$this->is_gev = 1;
		}
		$table 	->column("billnumber", "gev_bill_number")
				->column("participation_status", "status")
				->column("fee_pretax", "gev_training_fee_pretax_report")
				->column("fee_tax", "gev_tax")
				->column("fee_posttax", "gev_training_fee_posttax_report")
				->column("coupon_pretax", "gev_coupon_pretax")
				->column("coupon_tax", "gev_tax")
				->column("coupon_posttax", "gev_coupon_posttax")
				->column("amount_pretax", "gev_bill_amount_pretax")
				->column("amount_tax", "gev_tax")
				->column("amount_posttax", "gev_bill_amount_posttax")
				->column("cost_center", "gev_charged_agency")
				->column("bill_finalized_date", "create_date")
				->column("lastname", "lastname")
				->column("firstname", "firstname")
				->column("gender", "gender");
		if($this->is_vfs) {
			$table->column("adp_number", "gev_adp_number_vfs");
		}
		$table 	->column("org_unit", "gev_org_unit_short")
				->column("title", "gev_event_title")
				->column("custom_id", "gev_number_of_measure")
				->column("date", "gev_training_date")
				->column("venue", "gev_venue");
		if(self::REPORT_MODE_ADMIN === (int)$this->report_mode) {
			$table->column("assigment","gev_company_title");
		}
		$table	->column("bill_link", "", false, "", true);
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		return $order;
	}

	//_process_ will modify record entries
	// xls means: only for Excel-Export
	// date is the key in data-array 
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}