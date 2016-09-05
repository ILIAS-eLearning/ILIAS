<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catSelectableReportTableGUI.php';
use CaT\TableRelations as TableRelations;
use CaT\Filter as Filters;

class ilObjReportCouponNew extends ilObjReportBase {
	
	protected $online;
	protected $admin_mode;
	protected $relevant_parameters = array();
	protected $gUser;
	protected $interpreter;
	
	public function __construct($a_ref_id = 0) {
		global $ilUser;
		$this->gUser = $ilUser;
		parent::__construct($a_ref_id);
		$this->gf = new TableRelations\GraphFactory();
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, $this->gf);

	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rcpn')
				->addSetting(
					$this->s_f
						->settingBool('admin_mode', $this->plugin->txt('admin_mode'))
					);
	}

	public function initType() {
		 $this->setType("xrcn");
	}

	public function prepareTable(catSelectableReportTableGUI $table) {
		$table	->defineFieldColumn($this->plugin->txt("code"), 'code', array('code' => $this->fields['c']['coupon_code']))
				->defineFieldColumn($this->plugin->txt("start"), 'start', array('start' => $this->fields['c2']['coupon_value']))
				->defineFieldColumn($this->plugin->txt("diff"), 'diff', array('diff' => $this->tf->diffFieldsSql('diff',$this->fields['c2']['coupon_value'],$this->fields['c']['coupon_value'])))
				->defineFieldColumn($this->plugin->txt("current"), 'current', array( 'current' => $this->fields['c']['coupon_value']))
				->defineFieldColumn($this->plugin->txt("expires"),'expires',array('expires' => $this->fields['c']['coupon_expires']),true);
		if($this->settings['admin_mode']) {
			$table
				->defineFieldColumn($this->plugin->txt("name"), 'name', array('lastname' => $this->fields['hu']['lastname']
																			, 'firstname' => $this->fields['hu']['firstname']),true)
				->defineFieldColumn($this->plugin->txt("odbd"), 'odbd', array('above1' => $this->tf->groupConcatFieldSql('above1', $this->fields['huo']['org_unit_above1'],';;')
																			, 'above2' => $this->tf->groupConcatFieldSql('above2', $this->fields['huo']['org_unit_above2'],';;')),true,false)
				->defineFieldColumn($this->plugin->txt("orgu"), 'orgu', array('orgu' => $this->tf->groupConcatFieldSql('orgu', $this->fields['huo']['orgu_title'])),true);
		}
		$this->space = $table->prepareTableAndSetRelevantFields($this->space);
		return $table;
	}

	public function prepareFilter(catFilter $filter) {
		$filter	->checkbox("active_only"
								, $this->plugin->txt("coupon_active_only")
								," current > 0 AND c.coupon_expires > ".$this->gIldb->quote(time(),"integer")
								," TRUE"
								, true
								)
				->dateperiod( "period"
								, $this->plugin->txt("date_of_issue")
								, $this->plugin->txt("until")
								, "c.coupon_created"
								, "c.coupon_created"
								, date("Y")."-01-01"
								, date("Y")."-12-31"
								, true
								)
				->action($this->filter_action)
				->compile();
		$period = $filter->get('period');
		$this->space->addFilter(
			$this
				->fields['c']['coupon_created']->GE()->int((int)$period['start']->get(IL_CAL_UNIX))
				->_AND($this->fields['c']['coupon_created']->LE()->int((int)$period['end']->get(IL_CAL_UNIX))));
		if($filter->get('active_only')) {
			$this->space
				->addFilter($this->fields['c']['coupon_value']->GT()->int(0)
				->_AND($this->fields['c']['coupon_expires']->GT()->int(time())));
		}
		return $filter;
	}

	public function buildQuery( $query) {
		return;
	}

	public function buildFilter( $filter) {
		return;
	}

	public function initSpace() {
		$coupon_base = $this->tf->Table('coupon','c')
			->addField($this->tf->field('coupon_code'))
			->addField($this->tf->field('coupon_usr_id'))
			->addField($this->tf->field('coupon_value'))
			->addField($this->tf->field('coupon_expires'))
			->addField($this->tf->field('coupon_created'))
			->addField($this->tf->field('coupon_last_change'))
			->addField($this->tf->field('coupon_active'));


		$this->fields['c'] = $coupon_base->fields();
		$coupon_base->addConstraint($this->fields['c']['coupon_active']->EQ()->int(1));
		if(!$this->settings['admin_mode']) {
			$coupon_base->addConstraint($this->fields['c']['coupon_usr_id']->EQ()->int((int)$this->gUser->getId()));
		}

		$coupon_ref = $this->tf->Table('coupon','c2')
			->addField($this->tf->field('coupon_code'))
			->addField($this->tf->field('coupon_usr_id'))
			->addField($this->tf->field('coupon_value'))
			->addField($this->tf->field('coupon_expires'))
			->addField($this->tf->field('coupon_created'))
			->addField($this->tf->field('coupon_last_change'));
		$this->fields['c2'] = $coupon_ref->fields();
		
		$user = $this->tf->histUser('hu');
		$this->fields['hu'] = $user->fields();
		$user->addConstraint($this->fields['hu']['hist_historic']->EQ()->int(0));
		
		$userorgu = $this->tf->histUserOrgu('huo');
		$this->fields['huo']  = $userorgu->fields();
		$userorgu->addConstraint($this->fields['huo']['hist_historic']->EQ()->int(0)->_AND($this->fields['huo']['action']->GE()->int(0)));

		$this->space = $this->tf->TableSpace()
			->addTablePrimary($coupon_base)
			->addTablePrimary($coupon_ref)
			->addTableSecondary($user)
			->addTableSecondary($userorgu)
			->setRootTable($coupon_base)
			->addDependency($this->tf->TableJoin(
				$coupon_base,$coupon_ref
				,$this->fields['c']['coupon_code']->EQ($this->fields['c2']['coupon_code'])
					->_AND($this->fields['c2']['coupon_created']->EQ($this->fields['c2']['coupon_last_change']))))
			->addDependency($this->tf->TableLeftJoin(
				$coupon_base,$user
				,$this->fields['c']['coupon_usr_id']->EQ($this->fields['hu']['user_id'])))
			->addDependency($this->tf->TableLeftJoin(
				$coupon_base,$userorgu
				,$this->fields['c']['coupon_usr_id']->EQ($this->fields['huo']['usr_id'])))
			->groupBy($this->fields['c']['coupon_code']);
	}

	public function buildQueryStatement() {
		
		return $this->getInterpreter()->getSql($this->space->query());
	}

	protected function getInterpreter() {
		if(!$this->interpreter) {
			$this->interpreter = new TableRelations\SqlQueryInterpreter( new Filters\SqlPredicateInterpreter($this->gIldb), $this->pf, $this->gIldb);
		}
		return $this->interpreter;
	}

	public function deliverData(callable $callable) {
		$res = $this->gIldb->query($this->getInterpreter()->getSql($this->space->query()));
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = call_user_func($callable,$rec);
		}
		return $return;
	}

	protected function getRowTemplateTitle() {
		if($this->settings['admin_mode']) {
			return "tpl.report_coupons_admin_row.html";
		}
		return "tpl.report_coupons_row.html";
	}


	protected function buildOrder($order) {
		$order 	->defaultOrder("code", "ASC")
				;
		return $order;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}