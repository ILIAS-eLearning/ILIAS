<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



class ilObjReportTrDemandAdv extends ilObjReportBase {
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
}