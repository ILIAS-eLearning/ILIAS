<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevRoleUtils.php';

class ilObjReportASTD extends ilObjReportBase {

	protected $query_class;
	protected $accomodation_cost;
	protected $hierarchy;
	protected $categories;
	protected $role_utils;
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);

		include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/ReportASTD/config/ASTD_config.php';
		$this->role_utils = gevRoleUtils::getInstance();
	}

	public function initType() {
		 $this->setType("xatd");
	}

	protected function buildQuery($query) {
		return $this->prepareQueryComponents($query);
	}

	protected function prepareQueryComponents($a_query) {
		$this->categories = array(	'astd_hours_not_self_learn' 	=>	" SUM( IF(type IN ('Virtuelles Training','Präsenztraining','Webinar'), "
																			."IF(chours IS NOT NULL AND chours != 0, chours, "
																				."IF(thours IS NOT NULL AND chours != 0, thours, "
																					."4*GREATEST(credit_points,0)/3)), 0)) "
									,'astd_hours_self_learn' 		=>	" SUM( IF(type = 'Selbstlernkurs' AND credit_points IS NOT NULL, 4*GREATEST(credit_points,0)/3, 0)) " 
									,'astd_hours_language_course'	=>	' 0 '
									,'astd_participators'			=>	' COUNT(DISTINCT usr_id)'
									,'astd_accomodation_cost'		=>	" SUM( IF( type = 'Präsenztraining' AND begin_date IS NOT NULL AND end_date IS NOT NULL, (DATEDIFF(end_date,begin_date)+1)*"
																.$this->gIldb->quote( $this->getAccomodationCost(),'float').', 0) ) '
									);

		$this->end_date = $this->filter->get('period')['end']->getUnixTime();

		foreach($this->categories as $category => $query) {
			$this->query_sum_parts[] = $query.' AS '.$category;
		}
		$this->query_sum_parts = implode(', ', $this->query_sum_parts);

		$this->query_class = get_class($a_query);
		return null;
	}

	protected function buildFilter($filter) {
		$filter	->dateperiod( "period"
								, $this->lng->txt("gev_period")
								, $this->lng->txt("gev_until")
								, "c.end_date"
								, "c.end_date"
								, date("Y")."-01-01"
								, date("Y")."-12-31"
								)
				->static_condition(" c.hist_historic = 0 ")
				->static_condition(" ucs.hist_historic = 0 ")
				->static_condition(" ucs.participation_status = 'teilgenommen' ")
				->static_condition(" u.hist_historic = 0 ")
				->static_condition(" ucs.function = 'Mitglied' ")
				->static_condition(" ur2.hist_version IS NULL ")
				->static_condition("(template.hist_historic = 0 OR template.hist_historic IS NULL)");
		$filter	->action($this->filter_action);
		return $filter->compile();
	}

	protected function buildTable($table) {

		$table		->column("astd_category","astd_category");
		foreach($this->hierarchy as $position => $roles) {
			$table	->column($position.'_f',$position.'_f')
					->column($position.'_m',$position.'_m');
		}
		$table		->template("tpl.cat_astd_row.html","Services/ReportsRepository");
		return $table;
	}


	protected function queryBaseSet($gender, $in_role_ids, $not_in_role_ids) {
		$query = call_user_func($this->query_class.'::create');
		$query	->select('ucs.usr_id')
				->select('u.gender')
				->select('c.begin_date')
				->select('c.end_date')
				->select('c.type')
				->select_raw('c.hours  chours')
				->select_raw('template.hours thours')
				->select_raw('SUM(IF(nur1.hist_historic IS NOT NULL AND nur2.hist_historic IS NULL,1,0)) AS wrong_role_count')
				->select('ucs.credit_points')
				->from('hist_usercoursestatus ucs')
				->join('hist_course c')
					->on('ucs.crs_id = c.crs_id')
				->left_join('hist_course template')
					->on('c.template_obj_id = template.crs_id')
				->join('hist_userrole ur1')
					->on('ur1.usr_id = ucs.usr_id AND ur1.action = 1'
						.' AND '.$this->gIldb->in('ur1.rol_id',$in_role_ids,false,'integer')
						.' AND ur1.created_ts < '.$this->end_date )
				->left_join('hist_userrole ur2')
					->on('ur2.usr_id = ur1.usr_id AND ur2.rol_id = ur1.rol_id '
						.' AND ur2.action = -1 AND ur2.hist_version = ur1.hist_version+1'
						.' AND ur2.created_ts < '.$this->end_date)
				->left_join('hist_userrole nur1')
					->on('nur1.usr_id = ucs.usr_id AND nur1.action = 1'
						.' AND '.$this->gIldb->in('nur1.rol_id',$not_in_role_ids,false,'integer')
						.' AND nur1.created_ts < '.$this->end_date)
				->left_join('hist_userrole nur2')
					->on('nur2.usr_id = nur1.usr_id AND nur2.rol_id = nur1.rol_id '
						.' AND nur2.action = -1 AND nur2.hist_version = nur1.hist_version+1'
						.' AND nur2.created_ts < '.$this->end_date)
				->join('hist_user u')
					->on('u.user_id = ucs.usr_id AND u.gender = '.$this->gIldb->quote($gender,'text'))
				->group_by('ucs.usr_id')
				->group_by('ucs.crs_id')
				->compile();

		return $query->sql()."\n "
			   . $this->queryWhere()."\n "
			   . $query->sqlGroupBy()."\n"
			   . " HAVING wrong_role_count = 0 OR wrong_role_count IS NULL \n"
			   . $this->queryOrder();
	}

	protected function buildOrder($order) {
		return null;
	}

	public function fetchData($callable) {
		$data = array();
		foreach ($this->categories as $category => $query) {
			$predata[$category] = array();
		}

		$not_in_role_ids = array();
		foreach ($this->hierarchy as $position => $roles) {
			$in_role_ids = array();
			foreach ($roles as $roletitle) {
				$in_role_ids[] = $this->role_utils->getRoleIdByName($roletitle);
			}
			foreach (array('f', 'm') as $gender) {
				$query_base_set = $this->queryBaseSet($gender, $in_role_ids, $not_in_role_ids );
				$query = 	'SELECT '.$this->query_sum_parts.' FROM ('
								.$query_base_set
								.') as base_set';
				$res = $this->gIldb->query($query);
				while($rec = $this->gIldb->fetchAssoc($res)) {
					foreach($rec as $category => $value) {
						$predata[$category][$position.'_'.$gender] = $value;
						$predata[$category]['astd_category'] = $category;
					}
				}

			}

			$not_in_role_ids = array_merge($in_role_ids, $not_in_role_ids);
		}
		$data =  array();
		foreach ($predata as &$rec) {
			$data[] = call_user_func($callable,$rec);
		}
		return $data;
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_astd ".
			"(id, is_online, accomodation_cost) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").",".
			$this->gIldb->quote(30, "float")
			.")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_astd ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
			$this->setAccomodationCost($rec["accomodation_cost"]);
		}
	}


	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_astd SET "
			." is_online = ".$this->gIldb->quote($this->getOnline(), "integer")
			.", accomodation_cost = ".$this->gIldb->quote($this->getAccomodationCost(), "float")
			." WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_astd WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->setAccomodationCost($this->getAccomodationCost());
		$new_obj->update();
	}

	public function setOnline($a_val) {
		$this->online = (int)$a_val;
	}

	public function getOnline() {
		return $this->online;
	}

	public function setAccomodationCost($a_val) {
		$this->accomodation_cost = str_replace(',', '.', $a_val);
	}

	public function getAccomodationCost() {
		return $this->accomodation_cost;
	}


	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}