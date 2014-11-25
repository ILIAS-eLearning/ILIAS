<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Base class for filters in reports:
*
* Does the following things:
*   - lets report creator define filters in a declarative way
*   - renders html output from filters
*   - creates strings to be appended as GET parameters to links in report table headers
*   - creates where-parts for sql queries based on filter declaration
*   - grabs current filter configuration from GET and POST 
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

abstract class catFilterType {
	abstract public function getId();
	abstract public function checkConfig($a_conf);
	abstract public function render($a_tpl, $a_conf, $a_pars);
	abstract public function sql($a_conf, $a_pars);
	abstract public function get($a_pars);
	abstract public function _default($a_conf);
	abstract public function preprocess_post($a_post);
}

/*function errorHandler($errno, $errstr, $errfile, $errline) {
	debug_print_backtrace();
	return false;
}

set_error_handler("errorHandler");*/

class catFilter {
	protected static $filter_types = array();
	
	public static function addFilterType($a_id, $a_impl) {
		catFilter::$filter_types[$a_id] = $a_impl;
	}
	
	protected $static_conditions;
	protected $filters;
	protected $parameters;
	protected $get_string;
	protected $compiled;
	protected $action;
	protected $action_title;
	protected $calendar_util_inited;
	protected $post_var_prefix;
	protected $encoded_params;
	
	protected function __construct() {
		global $lng;
		global $ilDB;
		
		$this->lng = &$lng;
		$this->db = &$ilDB;
		
		$this->static_conditions = array();
		$this->filters = array();
		$this->parameters = array();
		$this->get_string = null;
		$this->compiled = false;
		$this->template = null;
		$this->action = null;
		$this->action_title = null;
		$this->post_var_prefix = "filter_";
		
		$this->encoded_params = null;
		$this->calendar_util_inited = false;
	}
	
	// Just a wrapper around the constructor to enable
	// creation of filters in fluid syntax.
	static public function create() {
		return new catFilter();
	}

	// magic for easy filter creation
	public function __call($a_name, $a_args) {
		if (array_key_exists($a_name, catFilter::$filter_types)) {
			$this->checkNotCompiled();
			$this->checkNameDoesNotExist($a_name);
			
			// I think this is necessary to have indexes from 0 to n
			$conf = array($a_name);
			foreach($a_args as $value) {
				$conf[] = $value;
			}
			
			$type = catFilter::$filter_types[$a_name];
			$this->filters[$a_args[0]] = $type->checkConfig($conf);
			return $this;
		}

		throw new Exception(" Call to undefined method catFilter::".$a_name);
	}

	// different types of filters
	
	// add a static condition to where
	public function static_condition($a_where) {
		$this->static_conditions[] = $a_where;
		return $this;
	}
	
	// set action to be called on submit
	public function action($a_action, $a_title = null) {
		$this->checkNotCompiled();
		if ($this->action !== null) {
			throw new Exception("catFilter::action: action is already set.");
		}
		
		if ($a_title === null) {
			$a_title = $this->lng->txt("gev_filter");
		}
		
		$this->action = $a_action;
		$this->action_title = $a_title;
		return $this;
	}

	// Compile the filter, no changes allowed afterwards.
	//
	// Initalizes parameters of filter from POST with fallback to GET and
	// default.
	public function compile() {
		if ($this->action === null) {
			throw new Exception("catFilter::compile: no action set.");
		}
		
		$this->compiled = true;
	
		$this->loadSearchParameters();
		
		return $this;
	}

	// Load search parameters, first try post, then try get, then use default
	protected function loadSearchParameters() {
		$get_params = array();
		if (array_key_exists($this->getGETName(), $_GET)) {
			$get_params = unserialize(base64_decode($_GET[$this->getGETName()]));
		}
		
		foreach($this->filters as $filter) {
			$name = $this->getName($filter);
			$postvar = $this->getPostVar($filter);
			if (array_key_exists($postvar, $_POST)) {
				$this->parameters[$name] = $this->preprocess_post($filter, $_POST[$postvar]);
			}
			else if(array_key_exists($name, $get_params)) {
				$this->parameters[$name] = $get_params[$name];
			}
			else {
				$this->parameters[$name] = $this->_default($filter);
			}
		}
	}

	// Get search parameters as string for GET.
	public function encodeSearchParamsForGET() {
		if ($this->encoded_params === null) {
			$this->encoded_params = base64_encode(serialize($this->parameters));
		}
		
		return $this->encoded_params;
	}
	
	// Get the name to be used to append parameters to get
	public function getGETName() {
		return "filter_params";
	}
	
	// Get the filters and current parameters as SQL-query part
	// to be append after WHERE in SQL-statement.
	public function getSQL() {
		$stmt = " TRUE ";

		foreach ($this->static_conditions as $cond) {
			$stmt .= "\n AND " . $cond;
		}

		foreach ($this->filters as $conf) {
			$type = $this->getType($conf);
			$pars = $this->getParameters($conf);
			$stmt .= "\n   AND " . $type->sql($conf, $pars) . " ";
		}
		
		return $stmt;
	}
	
	// Get the value of a filter parameter
	public function get($a_name) {
		if (!array_key_exists($a_name, $this->filters)) {
			throw new Exception("catFilter::get: Unknown filter ".$a_name);
		}
		
		$filter = $this->filters[$a_name];
		$type = $this->getType($filter);
		return $type->get($this->getParameters($filter));
	}
	
	// Render the filter to HTML output
	public function render() {
		$this->checkCompiled("render");
		
		require_once("Services/UICore/classes/class.ilTemplate.php");
		
		$tpl = new ilTemplate("tpl.cat_filter.html", true, true, "Services/GEV/Reports");
		
		foreach ($this->filters as $conf) {
			$type = $this->getType($conf);
			$tpl->setCurrentBlock($type->getId());
			$postvar = $this->getPostVar($conf);
			$tpl->setVariable("POST_VAR", $postvar);
			$type->render($tpl, $postvar, $conf, $this->getParameters($conf));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("FILTER", $this->action_title);
		
		return $tpl->get();
	}
	
	// get default value for filter
	protected function _default($a_conf) {
		$type = $this->getType($a_conf);
		return $type->_default($a_conf);
	}
	
	// preprocess post variables
	protected function preprocess_post($a_conf, $a_post) {
		$type = $this->getType($a_conf);
		return $type->preprocess_post($a_post);
	}
	
	protected function getPostVar($a_conf) {
		return $this->post_var_prefix . $this->getName($a_conf);
	}
	
	protected function getType($a_conf) {
		return catFilter::$filter_types[$a_conf[0]];
	}
	
	protected function getName($a_conf) {
		return $a_conf[1];
	}
	
	protected function getDefault($a_conf) {
		return $a_conf[4];
	}
	
	protected function getParameters($a_conf) {
		return $this->parameters[$this->getName($a_conf)];
	}
	
	static public function quoteDBId($a_id) {
		global $ilDB;
		$spl = explode(".", $a_id);
		$ret = array();
		foreach ($spl as $id) {
			$ret[] = $ilDB->quoteIdentifier($id);
		}
		return implode(".", $ret);
	}
	
	// Methods for sanity checks.
	protected function checkNotCompiled() {
		if ($this->compiled) {
			throw new Exception("catFilter::checkCompiled: Don't modify a filter you already compiled.");
		}
	}

	protected function checkCompiled($a_what) {
		if (!$this->compiled) {
			throw new Exception("catFilter::checkCompiled: Don't ".$a_what." a filter you did not compile.");
		}
	}


	protected function checkNameDoesNotExist($a_name) {
		if (array_key_exists($a_name, $this->filters)) {
			throw new Exception("catFilter::checkNameExists: Name ".$a_name." already used.");
		}
	}
}


class catDatePeriodFilterType {
	const ID = "dateperiod";
	
	public function getId() {
		return catDatePeriodFilterType::ID;
	}
	
	public function checkConfig($a_conf) {
		if (count($a_conf) !== 8) {
			// one parameter less, since type is encoded in first parameter but not passed by user.
			throw new Exception ("catDatePeriodFilterType::checkConfig: expected 7 parameters for dateperiod.");
		}
		return $a_conf;
	}
	
	public function render($a_tpl, $a_postvar, $a_conf, $a_pars) {
		require_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		
		global $lng;

		ilCalendarUtil::initJSCalendar();
		
		$a_tpl->setVariable('DP_LABEL_START', $a_label_begin);
		$a_tpl->setVariable('DP_LABEL_END', $a_label_end);
		
		$a_tpl->setVariable("DP_IMG_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$a_tpl->setVariable("DP_TXT_CALENDAR", $lng->txt("open_calendar"));

		$a_tpl->setVariable("DP_INPUT_FIELDS_START", $a_postvar."[start][date]");
		$a_tpl->setVariable('DP_DATE_FIRST_DAY',ilCalendarUserSettings::_getInstance()->getWeekStart());

		$start = new ilDate($a_pars["start"], IL_CAL_DATE);
		$end = new ilDate($a_pars["end"], IL_CAL_DATE);
		
		$start_info = $start->get(IL_CAL_FKT_GETDATE,"","UTC");
		$a_tpl->setVariable("DP_START_SELECT",
			ilUtil::makeDateSelect(
				$a_postvar."[start][date]",
				$start_info['year'], $start_info['mon'], $start_info['mday'],
				"",
				true,
				array(
					'disabled' => false,
					'select_attributes' => array('onchange' => 'ilUpdateEndDate();')
					),
				false));

		$end_info = $end->get(IL_CAL_FKT_GETDATE,"","UTC");
		$a_tpl->setVariable("DP_END_SELECT",
			ilUtil::makeDateSelect(
				$a_postvar."[end][date]",
				$end_info['year'], $end_info['mon'], $end_info['mday'],
				"",
				true,
				array(
					'disabled' => false
					),
				false));
	}
	
	public function sql($a_conf, $a_pars) {
		global $ilDB;
	
		return "    (".catFilter::quoteDBId($a_conf[5])
					  ." >= ".$ilDB->quote($a_pars["start"], "date")
			  			// this accomodates history tables
			  ."        OR ".catFilter::quoteDBId($a_conf[5])." = '0000-00-00' "
			  ."        OR ".catFilter::quoteDBId($a_conf[5])." = '-empty-'"
			  ."    )"
			  ."   AND ".catFilter::quoteDBId($a_conf[4])
			  			." <= ".$ilDB->quote($a_pars["end"], "date")
			  ;
	}
	
	public function get($a_pars) {
		return array( "start" => new ilDate($a_pars["start"], IL_CAL_DATE)
					, "end" => new ilDate($a_pars["end"], IL_CAL_DATE)
					);
	}
	
	public function _default($a_conf) {
		return array("start" => $a_conf[6], "end" => $a_conf[7]);
	}
	
	public function preprocess_post($a_post) {
		$s = $a_post["start"]["date"];
		$e = $a_post["end"]["date"];
		$form = "%04d-%02d-%02d";
		return array( "start" => sprintf($form, $s["y"], $s["m"], $s["d"])
					, "end" => sprintf($form, $e["y"], $e["m"], $e["d"])
					);
	}
}
catFilter::addFilterType(catDatePeriodFilterType::ID, new catDatePeriodFilterType());


class catCheckboxFilterType {
	const ID = "checkbox";
	
	public function getId() {
		return catCheckboxFilterType::ID;
	}
	
	public function checkConfig($a_conf) {
		if (count($a_conf) !== 5) {
			// one parameter less, since type is encoded in first parameter but not passed by user.
			throw new Exception ("catCheckboxFilterType::checkConfig: expected 4 parameters for checkbox.");
		}
		return $a_conf;
	}
	
	public function render($a_tpl, $a_postvar, $a_conf, $a_pars) {
		require_once("Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
		
		$a_tpl->setVariable("PROPERTY_CHECKED", $a_pars ? "checked" : "");
		$a_tpl->setVariable("OPTION_TITLE", $a_conf[2]);
	}
	
	public function sql($a_conf, $a_pars) {
		global $ilDB;
	
		if ($a_pars && $a_conf[3] !== null) {
			return $a_conf[3];
		}
		
		if($a_conf[4] !== null) {
			return $a_conf[4];
		}
		
		return "";
	}
	
	public function get($a_pars) {
		return $a_pars;
	}
	
	public function _default($a_conf) {
		return false;
	}
	
	public function preprocess_post($a_post) {
		return true;
	}
}
catFilter::addFilterType(catCheckboxFilterType::ID, new catCheckboxFilterType());

?>
