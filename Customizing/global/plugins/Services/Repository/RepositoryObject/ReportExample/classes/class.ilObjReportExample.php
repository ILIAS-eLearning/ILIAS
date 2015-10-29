<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

class ilObjReportExample extends ilObjReportBase {
	
	protected $online;
	protected $show_filter;
	protected $relevant_parameters = array();
	
	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}

	public function initType() {
		 $this->setType("xrts");
	}

	protected function buildQuery($query) {
		$query 	->select('obj_id')
				->select('type')
				->select('title')
				->from('object_data')
				->compile()
				;
		return $query;
	}

	protected function buildFilter($filter) {
		$filter	->multiselect( "type"
							 , "Obj.Type"
							 , array("type")
							 , $this->getObjectTypes()
							 , array()
							 , ""
							 , 300
							 , 160
							 )
				->action($this->filter_action)
				->compile()
				;
		return $filter;
	}

	public function deliverFilter() {
		return $this->getShowFilter() ? $this->filter : null;
	}

	protected function buildTable($table) {
		$table 	->column("obj_id","obj_id")
				->column("type", "type")
				->column("title", "title")
				->template("tpl.gev_report_test_row.html", "Services/ReportsRepository")
				;
		return $table;
	}

	protected function buildOrder($order) {
		$order 	->defaultOrder("obj_id", "ASC")
				;
		return $order;
	}

	protected function getObjectTypes() {
		$sql = "SELECT DISTINCT type FROM object_data";
		$res = $this->gIldb->query($sql);
		$return = array();
		while( $rec = $this->gIldb->fetchAssoc($res) ) {
			$return[] = $rec["type"];
		}
		return $return;
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rts ".
			"(id, is_online, show_filter) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").",".
			$this->gIldb->quote(0, "integer").
			")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rts ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
			$this->setShowFilter($rec["show_filter"]);
		}
	}

	public function setShowFilter($bool) {
		$this->show_filter = (int)$bool;
	}

	public function getShowFilter() {
		return $this->show_filter;
	}


	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rts SET ".
			" is_online = ".$this->gIldb->quote($this->getOnline(), "integer").",".
			" show_filter = ".$this->gIldb->quote($this->getShowFilter(), "integer").
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rts WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->setShowFilter($this->getShowFilter());
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