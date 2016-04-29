<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrDemandAdv extends ilObjReportBase {
	protected $is_local;
	protected $relevant_parameters = array();

	public function initType() {
		 $this->setType("xtda");
	}
	
	protected function getRowTemplateTitle() {
		return "tpl.gev_training_utilisation_advanced_row.html";
	}

	protected function buildOrder($order) {
		return $order
					->defaultOrder("tpl_title", "ASC")
					->mapping("tpl_title",array("tpl_title","title","begin_date"))
					->mapping("booked_wl",array("waitinglist_active","booked_wl"));
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rtda')
				->addSetting($this->s_f
								->settingBool('is_local', $this->plugin->txt('report_is_local'))
								);
	}

	protected function buildTable($table) {
		$table	->column('tpl_title', $this->plugin->txt('tpl_title'), true)
				->column('title', $this->plugin->txt('crs_title'), true)
				->column('type', $this->plugin->txt('crs_type'), true)
				->column('begin_date', $this->plugin->txt('crs_date'), true)
				->column('bookings', $this->plugin->txt('bookings'), true)
				->column('min_participants', $this->plugin->txt('min_participants'), true)
				->column('min_part_achived', $this->plugin->txt('min_part_achived'), true)
				->column('bookings_left', $this->plugin->txt('bookings_left'), true)
				->column('booked_wl', $this->plugin->txt('waitinglist'), true)
				->column('booking_dl', $this->plugin->txt('booking_dl'), true)
				->column('trainers', $this->plugin->txt('trainers'), true);
		return parent::buildTable($table);
	}

	protected function buildQuery($query) {
		$query
			->select('crs.template_obj_id')
			->select('crs.crs_id')
			->select('crs.title')
			->select('crs.type')
			->select('crs.begin_date')
			->select_raw('DATE_SUB(crs.begin_date,INTERVAL crs.dl_booking DAY) as booking_dl')
			->select('crs.end_date')
			->select_raw("IF(crs.waitinglist_active = 'Ja',1,0) as waitinglist_active")
			->select_raw("SUM(IF(usrcrs.booking_status = 'gebucht' AND usrcrs.function = 'Mitglied',1,0)) as bookings")
			->select_raw('crs.min_participants')
			->select_raw('crs.max_participants')
			->select_raw("SUM(IF(usrcrs.booking_status = 'auf Warteliste',1,0)) as booked_wl")
			->select_raw(" GROUP_CONCAT("
						." IF(usrcrs.function = 'Trainer',CONCAT(usr.firstname,' ',usr.lastname) ,NULL)"
						." SEPARATOR ', ') as trainers")
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
		$local_condition = $this->settings['is_local'] === "1"
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
			->multiselect_custom( 'status' 
								, $this->plugin->txt("status")
								, array('min_participants > bookings' => $this->plugin->txt('cancel_danger'),
										'min_participants <= bookings' => $this->plugin->txt('no_cancel_danger'))
								, array()
								, ' OR min_participants IS NULL '
								, 200
								, 160
								, "text"
								, "asc"
								, true
								)
			->multiselect_custom( 'waiting_list' 
								, $this->plugin->txt('waiting_list_filter')
								, array("crs.waitinglist_active = 'Ja'" => $this->plugin->txt('waiting_list'),
									"crs.waitinglist_active = 'Nein'" => $this->plugin->txt('no_waiting_list'))
								, array()
								, ' '
								, 200
								, 160
								, "text"
								)
			->multiselect_custom( 'booking_over'
								, $this->plugin->txt('booking_over')
								, array($this->gIldb->quote(date('Y-m-d'),'text')." > booking_dl " 
											=> $this->plugin->txt('book_dl_over'),
										$this->gIldb->quote(date('Y-m-d'),'text')." <= booking_dl " 
											=> $this->plugin->txt('book_dl_not_over'))
								, array()
								, ' '
								, 200
								, 160
								, "text"
								, "asc"
								,	true
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
			->static_condition('crs.begin_date >= '.$this->gIldb->quote(date('Y-m-d'),'text'))
			->static_condition("(crs.is_cancelled != 'Ja' OR crs.is_cancelled IS NULL)")
			->static_condition('crs.hist_historic = 0')
			->static_condition("crs.is_template = 'Nein'")
			->static_condition("crs.begin_date != '0000-00-00'")
			->static_condition($this->gIldb->in('crs.type',array('Webinar','Präsenztraining','Virtuelles Training'),false,'text'))
			->action($this->filter_action)
			->compile();
		return $filter;
	}

	public function buildQueryStatement() {
		$local_condition = $this->settings['is_local'] === "1"
			? $this->gIldb->in('tpl.crs_id',array_unique($this->getSubtreeCourseTemplates()),false,'integer')
			: " tpl.is_template = 'Ja' ";
		
		$query ='SELECT tpl.title as tpl_title, base.*, base.max_participants - base.bookings as bookings_left '
				.'	, IF(base.bookings >= base.min_participants OR ( base.bookings > 0 AND (base.min_participants IS NULL'
				.'		OR base.min_participants <= 0)),1,0) as min_part_achived'
				.' FROM hist_course tpl JOIN '
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

	public function getRelevantParameters() {
		return $this->relevant_parameters;
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