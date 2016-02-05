<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



class ilObjReportTrDemandAdv extends ilObjReportBase {
	protected $is_local;

	protected function buildQuery($query) {
		$query
			->select('tpl.title as tpl_title')
			->select('crs.title as title')
			->select('crs.begin_date')
			->select('crs.end_date')
			->select_raw("SUM(IF(usrcrs.booking_status = 'gebucht',1,0)) as booked")
			->select_raw('crs.min_participants')
			->select_raw('crs.max_participants')
			->select_raw("SUM(IF(usrcrs.booking_status = 'auf Warteliste',1,0)) as booked_wl")
			->select_raw("GROUP_CONCAT("
						."	IF(usr.hist_historic IS NOT NULL, CONCAT(usr.firstname,' ',usr.lastname), '')"
						."	DELIMITER ', ') as trainers")
			->from('hist_course tpl')
				->join('hist_course crs')
					->on('crs.template_obj_id = tpl.crs_id')
				->left_join('hist_usercoursestatus usrcrs')
					->on('usrcrs.usr_id = orgu.usr_id AND usrcrs.hist_historic = 0')
				->left_join('hist_user usr')
					->on('usr.user_id = usrcrs.usr_id'
						."AND usrcrs.function = 'Trainer'")
				->group_by('crs.crs_id')
				->compile();
		return $query;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rtda ".
			"(id, is_online, is_local ) VALUES (".
			$this->gIldb->quote($this->getId(), "integer")
			.",".$this->gIldb->quote(0, "integer")
			.",".$this->gIldb->quote(0, "integer")
			.")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rtda ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate("UPDATE rep_robj_rtda SET "
			." is_online = ".$this->gIldb->quote($this->getOnline(), "integer")
			." WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rtda WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->update();
	}

	public function setOnline($a_val) {
		$this->online = (int)$a_val;
	}

	public function getOnline() {
		return $this->online;
	}

	public function getIslocal() {
		return $this->is_local;
	}

	public function setIslocal($value) {
		$this->is_local = $value ? 1 : 0;
	}
}