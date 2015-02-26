<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "WBDErrors"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

class gevWBDErrorsGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();


		$this->title = catTitleGUI::create()
						->title("gev_rep_wbd_errors")
						->subTitle("gev_rep_wbd_errors_desc")
						->image("GEV_img/ico-head-rep-billing.png")
						;


		$this->table = catReportTable::create()
						->column("ts", "ts")
						->column("action", "action")
						->column("internal", "internal")
						->column("user_id", "usr_id")
						->column("course_id", "crs_id")
						->column("login", "login")
						->column("firstname", "firstname")
						->column("lastname", "lastname")
						->column("title", "title")
						->column("begin_date", "begin_date")
						->column("end_date", "end_date")
						->column("reason", "reason")
						->column("reason_full", "text")
						->column("resolve", "resolve")

						->template("tpl.gev_wbd_errors_row.html", "Services/GEV/Reports")
						;


		$this->order = catReportOrder::create($this->table)
						//->mapping("date", "crs.begin_date")
						->defaultOrder("ts", "DESC")
						;
		

		$this->query = catReportQuery::create()
						->distinct()
						->select("err.usr_id")
						->select("err.crs_id")
						->select("err.internal")
						->select("err.reason")
						->select("err.reason_full")
						->select("err.ts")
						->select("err.action")
						
						->select("ud.login")
						->select("ud.firstname")
						->select("ud.lastname")
						->select("crs.title")
						->select("usrcrs.begin_date")
						->select("usrcrs.end_date")
						
						
						->from("wbd_errors err")
						
						->left_join("hist_user usr")
							->on("err.usr_id = usr.user_id AND usr.hist_historic = 0")
						->left_join("hist_course crs")
							->on("err.crs_id = crs.crs_id AND crs.hist_historic = 0")
						->left_join("hist_usercoursestatus usrcrs")
							->on("err.usr_id = usrcrs.usr_id AND err.crs_id = usrcrs.crs_id AND usrcrs.hist_historic = 0")
						
						->left_join("usr_data ud")
							->on("err.usr_id = ud.usr_id")

						->compile()
						;



		$this->filter = catFilter::create()
						->static_condition("err.resolved = 0")
						->checkbox( "too_old"
								  , $this->lng->txt("gev_wbd_errors_show_too_old_as_well")
								  , "TRUE"
								  , "reason != 'TOO_OLD'"
								  , true
								  )
				
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;

	}
	
	protected function userIsPermitted () {
		return $this->user_utils->isAdmin();
	}

	protected function executeCustomCommand($a_cmd) {
		switch ($a_cmd) {
			default:
				return null;
		}
	}


	protected function transformResultRow($rec) {

		$link_usr = '<a href="#">'
			.$rec['usr_id']
			.'</a>';
		$rec['usr_id'] = $link_usr;


		if($rec['crs_id'] > 0) {
			$link_crs = '<a href="#">'
				.$rec['crs_id']
				.'</a>';
			$rec['crs_id'] = $link_crs;
		} 


		$resolve = '<a href="#">resolve</a>';
		$rec['resolve'] = $resolve;


		return $rec;
	}
	
	
}

?>
