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
						->column("action", "gev_wbd_errors_action")
						->column("internal", "gev_wbd_errors_internal")
						->column("user_id", "usr_id")
						->column("course_id", "crs_id")
						//->column("login", "login")
						->column("firstname", "firstname")
						->column("lastname", "lastname")
						->column("title", "title")
						->column("begin_date", "begin_date")
						->column("end_date", "end_date")
						->column("reason", "gev_wbd_errors_reason")
						->column("reason_full", "gev_wbd_errors_reason_full")
						->column("resolve", "gev_wbd_errors_resolve", 0, 0, 1)

						->template("tpl.gev_wbd_errors_row.html", "Services/GEV/Reports")
						;


		$this->order = catReportOrder::create($this->table)
						->mapping("course_id", "err.crs_id")
						->mapping("resolve", "err.ts")
						->defaultOrder("ts", "DESC")
						;
		

		$this->query = catReportQuery::create()
						->distinct()
						->select("err.id")
						->select("err.usr_id")
						->select("err.crs_id")
						->select("err.internal")
						->select("err.reason")
						->select("err.reason_full")
						->select("err.ts")
						->select("err.action")
						
						//->select("ud.login")
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
/*						->checkbox( "too_old"
								  , $this->lng->txt("gev_wbd_errors_show_too_old_as_well")
								  , "TRUE"
								  , "reason != 'TOO_OLD'"
								  , true
								  )
*/				


						->multiselect("reason"
									 , $this->lng->txt("gev_wbd_errors_reason")
									 , "reason"
									 , catFilter::getDistinctValues('reason', 'wbd_errors')
									 , array()
									 )

						->multiselect("action"
									 , $this->lng->txt("gev_wbd_errors_action")
									 , "action"
									 , catFilter::getDistinctValues('action', 'wbd_errors')
									 , array()
									 )
						->multiselect("internal"
									 , $this->lng->txt("gev_wbd_errors_internal")
									 , "internal"
									 , catFilter::getDistinctValues('internal', 'wbd_errors')
									 , array()
									 )
/*
						->multiselect("reason_full"
									 , $this->lng->txt("gev_wbd_errors_reason_full")
									 , "reason_full"
									 , catFilter::getDistinctValues('reason_full', 'wbd_errors')
									 , array()
									 )

*/

						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
		$this->relevant_parameters = array(
			$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			); 

	}
	
	protected function userIsPermitted () {
		return $this->user_utils->isAdmin();
	}

	protected function executeCustomCommand() {
		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case 'resolve':
				$err_id = $_GET['err_id'];
				require_once("Services/WBDData/classes/class.wbdErrorLog.php");
				$errlog = new wbdErrorLog();
				$errlog->resolveWBDErrorById($err_id);
				return $this->render();
				break;

			default:
				return null;
		}
	}


	protected function transformResultRow($rec) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");


		$link_change_usr = $this->ctrl->getLinkTargetByClass(
			array("iladministrationgui", "ilobjusergui"), "edit")
			.'&obj_id='.$rec['usr_id']
			.'&ref_id=7'; //ref 7: Manage user accounts here.
		$link_usr = '<a href="' .$link_change_usr.'">%s</a>';
			
		foreach (array('usr_id','firstname','lastname') as $key) {
			$rec[$key] = sprintf($link_usr, $rec[$key]);
		}


		$crs_ref_id = gevObjectUtils::getRefId($rec['crs_id']);
		if($crs_ref_id && $rec['crs_id'] > 0){
			$link_change_crs = $this->ctrl->getLinkTargetByClass(
				array("ilrepositorygui", "ilobjcoursegui"), "editInfo")
				.'&ref_id='
				.$crs_ref_id;
			$link_change_crs = '<a href="' .$link_change_crs.'">%s</a>';
		} else {
			$link_change_crs = '%s';
		}
		$rec['crs_id'] = sprintf($link_change_crs, $rec['crs_id']);



		$rec['resolve'] = '<a href="' 
			.$this->ctrl->getLinkTarget($this, "resolve")
			.'&err_id='
			.$rec['id']
			.'&'.$this->filter->getGETName().'='. $this->filter->encodeSearchParamsForGET().''
			.'">'
			.$this->lng->txt("gev_wbd_errors_resolve")
			.'</a>';

		return $rec;
	}
	
	protected function renderExportButton() {
		return '';
	}



	/*private function getDistinctValues($a_field) {
		global $ilDB;
		
		$sql = "SELECT DISTINCT $a_field FROM wbd_errors";
		$res = $ilDB->query($sql);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec[$a_field];
		}
		return $ret;
	}
	*/
}