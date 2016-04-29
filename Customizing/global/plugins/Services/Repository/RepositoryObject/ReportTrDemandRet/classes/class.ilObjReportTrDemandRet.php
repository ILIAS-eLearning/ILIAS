<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrDemandRet extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $is_local;

	public function initType() {
		$this->setType("xtdr");
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_tr_demand_ret_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildOrder($order) {
		return $order
					->defaultOrder("tpl_title", "ASC")
					->mapping("tpl_title",array("tpl_title","title","begin_date"));
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rtdr')
				->addSetting($this->s_f
								->settingBool('is_local', $this->plugin->txt('report_is_local'))
								);
	}

	protected function buildTable($table) {
		$table	->column('tpl_title', $this->plugin->txt('tpl_title'), true)
				->column('title', $this->plugin->txt('crs_title'), true)
				->column('type', $this->plugin->txt('crs_type'), true)
				->column('begin_date', $this->plugin->txt('crs_date'), true)
				->column('succ_participations', $this->plugin->txt('succ_participations'), true)
				->column('bookings', $this->plugin->txt('bookings'), true)
				->column('cancellations', $this->plugin->txt('cancellations'), true)
				->column('venue', $this->plugin->txt('venue'), true)
				->column('accomodation', $this->plugin->txt('accomodation'), true)
				->column('overnights', $this->plugin->txt('overnights'), true)
				->column('trainers', $this->plugin->txt('trainers'), true)
				->column('is_cancelled', $this->plugin->txt('is_cancelled'), true);
		return parent::buildTable($table);
	}

	protected function buildQuery($query) {
		$query
			->select('crs.template_obj_id')
			->select('crs.crs_id')
			->select('crs.title')
			->select('crs.type')
			->select('crs.begin_date')
			->select('crs.end_date')
			->select('crs.venue')
			->select('crs.accomodation')
			->select('crs.is_cancelled')
			->select_raw("SUM(IF(usrcrs.booking_status = 'gebucht'"
						." ,1,0)) as bookings")
			->select_raw("SUM(IF("
				.$this->gIldb->in('usrcrs.booking_status',
					array('kostenfrei storniert','kostenpflichtig storniert'),false,'text')
						." ,1,0)) as cancellations")
			->select_raw("SUM(IF(usrcrs.participation_status = 'teilgenommen'"
						." AND usrcrs.booking_status = 'gebucht',1,0)) as succ_participations")
			->select_raw("GROUP_CONCAT("
						." IF(usrcrs.function = 'Trainer',CONCAT(usr.firstname,' ',usr.lastname) ,NULL)"
						." SEPARATOR ', ') as trainers")
			->select_raw("SUM(IF(usrcrs.overnights IS NOT NULL"
						." AND usrcrs.overnights > 0 "
						." ,usrcrs.overnights,0)) as overnights")
			->from('hist_course crs')
				->left_join('hist_usercoursestatus usrcrs')
					->on(' usrcrs.crs_id = crs.crs_id AND usrcrs.hist_historic = 0 ')
				->left_join('hist_user usr')
					->on('usr.user_id = usrcrs.usr_id '
						.' AND usr.hist_historic = 0 ')
			->group_by('crs.crs_id')
			->compile();
		return $query;
	}

	protected function buildFilter($filter) {
		$local_condition = (string)$this->settings['is_local'] === "1"
			? $this->gIldb->in('crs.template_obj_id',array_unique($this->getSubtreeCourseTemplates()),false,'integer') 
			: 'TRUE';
		/*require_once 'Services/Object/classes/class.ilObject.php';
		$template_obj_filter_options = array();
		foreach ($template_obj_ids as $crs_id) {
			$template_obj_filter_options[$crs_id] = ilObject::_lookupTitle($crs_id);
		}*/
		$filter
			->dateperiod( 	  "period"
							, $this->plugin->txt("period")
							, $this->plugin->txt("until")
							, "crs.begin_date"
							, "crs.begin_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							, false
							)
		/*	->multiselect(	  'templates'
							, 'templates'
							, 'tpl.crs_id'
							, $template_obj_filter_options
							, array()
							, ""
							, 200
							, 160
							, 'integer'
							, 'asc'
							, true
							)*/
			->multiselect_custom( 'cancelled' 
								, $this->plugin->txt("filter_cancelled")
								, array("crs.is_cancelled  = 'Ja'" 
										=> $this->plugin->txt('crs_is_cancelled'),
										"crs.is_cancelled  != 'Ja' OR crs.is_cancelled IS NULL" 
										=> $this->plugin->txt('crs_is_not_cancelled'))
								, array()
								, ''
								, 200
								, 160
								)
			->multiselect(	   "training_type"
							 , $this->plugin->txt("training_type")
							 , 'crs.type'
							 , array('Webinar','Präsenztraining','Virtuelles Training')
							 , array()
							 , ""
							 , 200
							 , 160					
							)
			->static_condition("(crs.end_date < ".$this->gIldb->quote(date('Y-m-d'),'text')
								." OR crs.is_cancelled = 'Ja' )")
			->static_condition('crs.hist_historic = 0')
			->static_condition("crs.is_template = 'Nein'")
			->static_condition("crs.begin_date != '0000-00-00'")
			->static_condition($this->gIldb->in('crs.type',array('Webinar','Präsenztraining','Virtuelles Training'),false,'text'))
			->action($this->filter_action)
			->compile();
		return $filter;
	}

	public function buildQueryStatement() {
		$local_condition = (string)$this->settings['is_local'] === "1"
			? $this->gIldb->in('tpl.crs_id',array_unique($this->getSubtreeCourseTemplates()),false,'integer')
			: " tpl.is_template = 'Ja' ";
		
		$query ='SELECT tpl.title as tpl_title, base.* FROM hist_course tpl JOIN '
				.'('.$this->query->sql()."\n "
				. $this->queryWhere()."\n "
				. $this->query->sqlGroupBy()."\n"
				. $this->queryHaving()."\n"
				. ') as base'."\n"
				.' ON tpl.crs_id = base.template_obj_id'."\n"
				.' WHERE '.$local_condition
				.' 	AND tpl.hist_historic = 0 '
				.'	AND '.$this->gIldb->in('tpl.type',array('Webinar','Präsenztraining','Virtuelles Training'),false,'text')
				.' '.$this->queryOrder();
		return $query;
	}

	protected function getSubtreeCourseTemplates() {
		$query = 	'SELECT obj_id FROM adv_md_values_text amd_val '
					.'	WHERE '.$this->gIldb->in('obj_id',
							$this->getSubtreeTypeIdsBelowParentType('crs','cat'),false,'integer')
					.'		AND field_id = '.$this->gIldb->quote(
												gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE)
												,'integer')
					.'		AND value = '.$this->gIldb->quote('Ja','text');
		$return = array();
		$res = $this->gIldb->query($query);
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['obj_id'];
		}
		return $return;
	}
}