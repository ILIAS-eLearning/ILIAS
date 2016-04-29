<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
 * Factory to build filters guis.
 */
class FilterGUIFactory {

	/**
	 * Get the gui of Dateperiod Filter
	 *
	 * @param	Filter		$filter
	 * @param	string		$path 
	 * @return	FilterGUI
	 */
	public function dateperiod_gui(Filters\DatePeriod $filter, $path) {
		require_once ("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterDatePeriodGUI.php");
		return new \catFilterDatePeriodGUI($filter, $path);
	}

	/**
	 * Get the gui of Option Filter
	 *
	 * @param	Filter		$filter
	 * @param	string		$path 
	 * @return	FilterGUI
	 */
	public function option_gui(Filters\Option $filter, $path) {
		require_once ("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterOptionGUI.php");
		return new \catFilterOptionGUI($filter, $path);
	}

	/**
	 * Get the gui of Multiselect Filter
	 *
	 * @param	Filter		$filter
	 * @param	string		$path 
	 * @return	FilterGUI
	 */
	public function multiselect_gui(Filters\Multiselect $filter, $path) {
		require_once ("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterMultiselectGUI.php");
		return new \catFilterMultiselectGUI($filter, $path);
	}

	/**
	 * Get the gui of Singleselect Filter
	 *
	 * @param	Filter		$filter
	 * @param	string		$path 
	 * @return	FilterGUI
	 */
	public function singleselect_gui(Filters\Singleselect $filter, $path) {
		require_once ("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterSingleselectGUI.php");
		return new \catFilterSingleselectGUI($filter, $path);
	}

	/**
	 * Get the gui of Text Filter
	 *
	 * @param	Filter		$filter
	 * @param	string		$path 
	 * @return	FilterGUI
	 */
	public function text_gui(Filters\Text $filter, $path) {
		require_once ("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterTextGUI.php");
		return new \catFilterTextGUI($filter, $path);
	}

	/**
	 * Get the gui of OneOf Filter
	 *
	 * @param	Filter		$filter
	 * @param	string		$path 
	 * @return	FilterGUI
	 */
	public function one_of_gui(Filters\OneOf $filter, $path) {
		require_once ("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterOneOfGUI.php");
		return new \catFilterOneOfGUI($filter, $path);
	}
}