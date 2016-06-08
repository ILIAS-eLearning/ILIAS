<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportBookingsByVenue extends ilObjReportBase {
	
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}

	public function initType() {
		 $this->setType("xbbv");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rbbv');
	}

	protected function buildQuery($query) {
		$this->checked_filter = $this->filter->get("show_past_events");

		$this->crs_begin_filter = $this->filter->get("period")["start"]->get(IL_CAL_DATE);
		$this->crs_end_filter = $this->filter->get("period")["end"]->get(IL_CAL_DATE);
		$query
				->select("crs.crs_id")
				->select("title")
				->select("custom_id")
				->select("tutor")
				->select("crs.begin_date")
				->select("crs.end_date")
				->select("venue")
				->select_raw('COUNT(DISTINCT ucs.usr_id) as usr_total')
				->select_raw('COUNT(acco.night) AS on_total')
				->select_raw(
					" IF(crs.begin_date < ".$this->gIldb->quote($this->crs_end_filter,"date")
					."      AND crs.end_date > ".$this->gIldb->quote($this->crs_begin_filter,"date")
					."      ,0,IF(crs.begin_date <".$this->gIldb->quote($this->crs_begin_filter,"date").",1,-1))"
					." as checked ")
				->from("hist_course crs")
				->join("object_reference oref")
					->on("oref.obj_id = crs.crs_id")
				->join("crs_settings cs")
					->on("cs.obj_id = crs.crs_id")
				->left_join("hist_usercoursestatus ucs")
					->on('crs.crs_id = ucs.crs_id AND ucs.hist_historic = 0 AND'
						.' (ucs.booking_status = '.$this->gIldb->quote('gebucht','text').' OR function = '.$this->gIldb->quote('Trainer','text').')' )
				->left_join('crs_acco acco')
					->on('acco.crs_id = crs.crs_id AND ucs.usr_id = acco.user_id')
				->group_by('crs.crs_id')
				->compile();
		return $query;
	}

	protected function buildFilter($filter) {
		$venue_names = gevOrgUnitUtils::getVenueNames();
		if (!$this->user_utils->isAdmin()) {
			$venues = $this->user_utils->getVenuesWhereUserIsMember();
			foreach($venue_names as $id => $name) {
				if (!in_array($id, $venues)) {
					unset($venue_names[$id]);
				}
			}
		}
		$filter	->checkbox("show_past_events"
								, $this->plugin->txt("filter_show_past_events")
								," checked >= 0 "
								," checked = 0 "
								, true
								)
				->dateperiod( "period"
								, $this->plugin->txt("period")
								, $this->plugin->txt("until")
								, "crs.begin_date"
								, "crs.end_date"
								, date("Y")."-01-01"
								, date("Y")."-12-31"
								, false
								," OR TRUE "
								)
				->static_condition("crs.hist_historic = 0")
				->static_condition("crs.venue != '-empty-'")
				->static_condition("crs.venue NOT LIKE 'Online%'")
				->static_condition("oref.deleted IS NULL")
				->static_condition("cs.activation_type = 1")
				->static_condition($this->gIldb->in("venue", $venue_names, false, "text"))
				->action($this->filter_action)
				->compile()
				;
		return $filter;
	}

	protected function buildTable($table) {
		$table 	->column("custom_id", $this->plugin->txt("training_id"), true)
				->column("title", $this->plugin->txt("title"), true)
				->column("venue", $this->plugin->txt("venue"), true)
				->column("date", $this->plugin->txt("date"), true)
				->column("tutor", $this->plugin->txt("il_crs_tutor"), true)
				->column("no_members", $this->plugin->txt("no_members"), true)
				->column("no_accomodations", $this->plugin->txt("no_accomodations"), true)
				->column("action", $this->plugin->txt("list"), true, "", true, false);
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		$order	->mapping("date", "crs.begin_date")
				->mapping("no_accomodations", "on_total")
				->mapping("no_members", "usr_total")
				->defaultOrder("date", "ASC");
		return $order;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_bookings_by_venue_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}