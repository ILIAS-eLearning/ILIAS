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
							, $this->plugin->txt("period")
							, $this->plugin->txt("until")
							, "usrcrs.begin_date"
							, "usrcrs.end_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							)
				->dateperiod( "created"
							, $this->plugin->txt("created_since")
							, $this->plugin->txt("created_till")
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

		$no_entry = $this->plugin->txt("table_no_entry");
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
		$table 	->column("billnumber", $this->plugin->txt("bill_number"), true)
				->column("participation_status", $this->plugin->txt("status"), true)
				->column("fee_pretax", $this->plugin->txt("training_fee_pretax"), true)
				->column("fee_tax", $this->plugin->txt("tax"), true)
				->column("fee_posttax", $this->plugin->txt("training_fee_posttax"), true)
				->column("coupon_pretax", $this->plugin->txt("coupon_pretax"), true)
				->column("coupon_tax", $this->plugin->txt("tax"), true)
				->column("coupon_posttax", $this->plugin->txt("coupon_posttax"), true)
				->column("amount_pretax", $this->plugin->txt("bill_amount_pretax"), true)
				->column("amount_tax", $this->plugin->txt("tax"), true)
				->column("amount_posttax", $this->plugin->txt("bill_amount_posttax"), true)
				->column("cost_center", $this->plugin->txt("charged_agency"), true)
				->column("bill_finalized_date", $this->plugin->txt("create_date"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("firstname", $this->plugin->txt("firstname"), true)
				->column("gender", $this->plugin->txt("gender"),true);
		if($this->is_vfs) {
			$table->column("adp_number", $this->plugin->txt("adp_number_vfs"),true);
		}
		$table 	->column("org_unit", $this->plugin->txt("org_unit_short"), true)
				->column("title", $this->plugin->txt("event_title"), true)
				->column("custom_id", $this->plugin->txt("number_of_measure"), true)
				->column("date", $this->plugin->txt("training_date"), true)
				->column("venue", $this->plugin->txt("venue"), true);
		if(self::REPORT_MODE_ADMIN === (int)$this->settings['report_mode']) {
			$table->column("affiliation", $this->plugin->txt("affiliation"), true);
		}
		$table	->column("bill_link", "", true,'',true);
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