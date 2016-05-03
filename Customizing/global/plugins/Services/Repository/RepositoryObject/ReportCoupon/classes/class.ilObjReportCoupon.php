<?php

/*
SELECT hu.lastname, hu.firstname, c.coupon_code, c.coupon_value, c2.coupon_value, GROUP_CONCAT( huo.orgu_id )
FROM `coupon` c
JOIN coupon c2 ON c.coupon_code = c2.coupon_code
AND c2.coupon_last_change = c.coupon_created
JOIN hist_userorgu huo ON huo.usr_id = c.coupon_usr_id
JOIN hist_user hu ON hu.user_id = c.coupon_usr_id
WHERE c.coupon_active = 1
AND huo.hist_historic =0
*/

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

class ilObjReportCoupon extends ilObjReportBase {
	
	protected $online;
	protected $admin_mode;
	protected $relevant_parameters = array();
	protected $gUser;
	
	public function __construct($a_ref_id = 0) {
		global $ilUser;
		$this->gUser = $ilUser;
		parent::__construct($a_ref_id);

	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rcp')
				->addSetting(
					$this->s_f
						->settingBool('admin_mode', $this->plugin->txt('admin_mode'))
					);
	}

	public function initType() {
		 $this->setType("xrcp");
	}


	protected function buildQuery($query) {
		$query	->select("c.coupon_usr_id")	
				->select_raw("c.coupon_code code")
				->select_raw("c.coupon_value current")
				->select_raw("c2.coupon_value start")
				->select_raw("c2.coupon_value - c.coupon_value diff")
				->select("c.coupon_expires")
				->select_raw("FROM_UNIXTIME(c.coupon_expires,'%d.%m.%Y') expires")
				->from("coupon c")
				->join("coupon c2")
					->on("	c.coupon_code = c2.coupon_code"
						."	AND c2.coupon_last_change = c.coupon_created");
		if($this->settings['admin_mode']) {
			$query	->select("hu.firstname")
					->select("hu.lastname")
					->select_raw("GROUP_CONCAT(DISTINCT huo.orgu_title SEPARATOR ', ') as orgu")
					->select_raw("GROUP_CONCAT(DISTINCT huo.org_unit_above1 SEPARATOR ';') as above1")
					->select_raw("GROUP_CONCAT(DISTINCT huo.org_unit_above2 SEPARATOR ';') as above2")
					->left_join("hist_userorgu huo")
						->on("huo.usr_id = c.coupon_usr_id")
					->left_join("hist_user hu")
						->on("hu.user_id = c.coupon_usr_id")
					->group_by("c.coupon_code");
		}
		return $query->compile();
	}

	protected function buildFilter($filter) {
		$filter	->checkbox("active_only"
								, $this->lng->txt("gev_coupon_active_only")
								," current > 0 AND c.coupon_expires > ".$this->gIldb->quote(time(),"integer")
								," TRUE"
								, true
								)
				->dateperiod( "period"
								, $this->lng->txt("gev_date_of_issue")
								, $this->lng->txt("gev_until")
								, "c.coupon_created"
								, "c.coupon_created"
								, date("Y")."-01-01"
								, date("Y")."-12-31"
								, true
								)
				->static_condition("c.coupon_active = 1");
		if($this->settings['admin_mode']) {
			$filter	->static_condition(" (huo.hist_historic = 0 OR huo.hist_historic IS NULL) ")
					->static_condition(" (hu.hist_historic = 0 OR hu.hist_historic IS NULL) ");
		} else {
			$filter	->static_condition("c.coupon_usr_id = ".$this->gIldb->quote($this->gUser->getId(),"integer"));
		}
		$filter	->action($this->filter_action);
		return $filter->compile();
	}

	protected function getRowTemplateTitle() {
		if($this->settings['admin_mode']) {
			return "tpl.report_coupons_admin_row.html";
		}
		return "tpl.report_coupons_row.html";
	}

	protected function buildTable($table) {
		$table	->column("code","gev_coupon_bill_item_code")
				->column("start","gev_coupon_start_ammount")
				->column("diff","gev_coupon_diff_ammount")
				->column("current","gev_coupon_current_ammount")
				->column("expires","gev_coupon_expires");
		if($this->settings['admin_mode']) {
			$table	->column("firstname","firstname")
					->column("lastname","lastname")
					->column("odbd","gev_od_bd")
					->column("orgu","gev_org_unit_short");
		}
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		$order 	->defaultOrder("code", "ASC")
				;
		return $order;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}