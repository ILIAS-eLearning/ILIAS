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

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

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
				->select_raw("FROM_UNIXTIME(c.coupon_expires,'%Y-%m-%d') expires")
				->from("coupon c")
				->join("coupon c2")
					->on("	c.coupon_code = c2.coupon_code"
						."	AND c2.coupon_last_change = c.coupon_created");
		if($this->getAdminMode()) {
			$query	->select("hu.firstname")
					->select("hu.lastname")
					->select_raw("GROUP_CONCAT(DISTINCT huo.orgu_title SEPARATOR ', ') as orgu")
					->select_raw("GROUP_CONCAT(DISTINCT CONCAT(huo.org_unit_above1,'/',huo.org_unit_above2) SEPARATOR ', ') as odbd")
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
		if($this->getAdminMode()) {
			$filter	->static_condition(" (huo.hist_historic = 0 OR huo.hist_historic IS NULL) ")
					->static_condition(" (hu.hist_historic = 0 OR hu.hist_historic IS NULL) ");
		} else {
			$filter	->static_condition("c.coupon_usr_id = ".$this->gIldb->quote($this->gUser->getId(),"integer"));
		}
		$filter	->action($this->filter_action);
		return $filter->compile();
	}


	protected function buildTable($table) {
		$table	->column("code","gev_coupon_bill_item_code")
				->column("start","gev_coupon_start_ammount")
				->column("diff","gev_coupon_diff_ammount")
				->column("current","gev_coupon_current_ammount")
				->column("expires","gev_coupon_expires");
		if($this->getAdminMode()) {
			$table	->column("firstname","firstname")
					->column("lastname","lastname")
					->column("odbd","gev_od_bd")
					->column("orgu","gev_org_unit_short")
					->template("tpl.report_coupons_admin_row.html","Services/ReportsRepository");
		} else {
			$table	->template("tpl.report_coupons_row.html","Services/ReportsRepository");
		}
		return $table;
	}

	protected function buildOrder($order) {
		$order 	->defaultOrder("code", "ASC")
				;
		return $order;
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rcp ".
			"(id, is_online, admin_mode) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").",".
			$this->gIldb->quote(0, "integer").
			")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rcp ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
			$this->setAdminMode($rec["admin_mode"]);
		}
	}

	public function setAdminMode($bool) {
		$this->admin_mode = (int)$bool;
	}

	public function getAdminMode() {
		return $this->admin_mode;
	}


	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rcp SET ".
			" is_online = ".$this->gIldb->quote($this->getOnline(), "integer").",".
			" admin_mode = ".$this->gIldb->quote($this->getAdminMode(), "integer").
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rcp WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->setAdminMode($this->getShowFilter());
		$new_obj->update();
	}


	public function setOnline($a_val) {
		$this->online = (int)$a_val;
	}

	public function getOnline() {
		return $this->online;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}