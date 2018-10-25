<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter service
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIFilterService
{
	/**
	 * @var ilUIService
	 */
	protected $service;

	/**
	 * @var ilUIServiceDependencies
	 */
	protected $_deps;

	/**
	 * Constructor
	 * @param ilUIService $service
	 * @param ilUIServiceDependencies $deps
	 */
	public function __construct(ilUIService $service, ilUIServiceDependencies $deps)
	{
		$this->service = $service;
		$this->_deps = $deps;
	}


	/**
	 * Get standard filter instance
	 *
	 * @param
	 * @return
	 */
	public function standard($filter_id, $base_action, array $inputs, array $is_input_initially_rendered,
								$is_initially_activated = false, $is_initially_expanded = false): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		global $DIC;
		$ui = $DIC->ui()->factory();

		$is_input_rendered = $is_input_initially_rendered;
		$is_activated = $is_initially_activated;
		$is_expanded = $is_initially_expanded;

		// read cmdFilter from request and update session data

		if ($_REQUEST["cmdFilter"] == "toggleOn") {
			ilSession::set("ui_service_filter_activated_".$filter_id, true);
		}

		if ($_REQUEST["cmdFilter"] == "toggleOff") {
			ilSession::set("ui_service_filter_activated_".$filter_id, false);
		}

		if ($_REQUEST["cmdFilter"] == "expand") {
			ilSession::set("ui_service_filter_expanded_".$filter_id, true);
		}

		if ($_REQUEST["cmdFilter"] == "collapse") {
			ilSession::set("ui_service_filter_expanded_".$filter_id, false);
		}

		if ($_REQUEST["cmdFilter"] == "apply")
		{
			$_SESSION["ui"]["filter"]["rendered"][$filter_id] = [];
			foreach ($inputs as $input_id => $i)
			{
				if ($_POST["__filter_status_" . $input_id] === "1")
				{
					$_SESSION["ui"]["filter"]["rendered"][$filter_id][$input_id] = 1;
				} else
				{
					$_SESSION["ui"]["filter"]["rendered"][$filter_id][$input_id] = 0;
				}
			}
		}



		// get data from session

		if (isset($_SESSION["ui_service_filter_activated_".$filter_id]) && !empty("ui_service_filter_activated_".$filter_id)) {
			$is_activated = ilSession::get("ui_service_filter_activated_".$filter_id);
		}

		if (isset($_SESSION["ui_service_filter_expanded_".$filter_id]) && !empty("ui_service_filter_expanded_".$filter_id)) {
			$is_expanded = ilSession::get("ui_service_filter_expanded_".$filter_id);
		}


		//compose a new array because rendering of inputs has eventually changed
		//if (ilSession::get("ui_service_filter_is_input_rendered_0" . "_" . $filter_id) != null) {
		//	$is_input_rendered = array();
		//	for ($i = 0; $i <= $input_id; $i++) {
		//		$is_input_rendered[] = ilSession::get("ui_service_filter_is_input_rendered_" . $i . "_" . $filter_id);
		//	}
		//}


		// create the KS Filter


		$request = $DIC->http()->request();

		// clear session, if reset is pressed
		if ($_REQUEST["cmdFilter"] == "reset")
		{
			if (is_array($_SESSION["ui"]["filter"]["value"][$filter_id]))
			{
				unset($_SESSION["ui"]["filter"]["value"][$filter_id]);
			}
			unset($_SESSION["ui"]["filter"]["rendered"][$filter_id]);
		}

		// put data from session into filter
		$inputs_with_session_data = [];
		$is_input_initially_rendered_with_session = [];
		foreach ($inputs as $input_id => $i)
		{
			//var_dump($_SESSION["ui"]["filter"]); exit;
			// is filter rendered or not?
			$rendered = current($is_input_initially_rendered);
			if (isset($_SESSION["ui"]["filter"]["rendered"][$filter_id][$input_id]))
			{
				$rendered = (bool) $_SESSION["ui"]["filter"]["rendered"][$filter_id][$input_id];
			}
			$is_input_initially_rendered_with_session[] = $rendered;
			next($is_input_initially_rendered);

			if ($rendered && isset($_SESSION["ui"]["filter"]["value"][$filter_id][$input_id]))
			{
				$val = unserialize($_SESSION["ui"]["filter"]["value"][$filter_id][$input_id]);
				if (!is_null($val))
				{
					$i = $i->withValue($val);
				}
			}
			$inputs_with_session_data[$input_id] = $i;
		}

		$filter = $ui->input()->container()->filter()->standard(
			$base_action."&cmdFilter=toggleOn",
			$base_action."&cmdFilter=toggleOff",
			$base_action."&cmdFilter=expand",
			$base_action."&cmdFilter=collapse",
			$base_action."&cmdFilter=apply",
			$base_action."&cmdFilter=reset",
			$inputs_with_session_data,
			$is_input_initially_rendered_with_session,
			$is_activated,
			$is_expanded);

		// 2. eingabe werte in session speichern
		switch ($_REQUEST["cmdFilter"])
		{
			case "apply":
				if ($request->getMethod() == "POST")
				{
					$filter = $filter->withRequest($request);
					foreach ($filter->getInputs() as $input_id => $i)
					{
						$_SESSION["ui"]["filter"]["value"][$filter_id][$input_id] = serialize($i->getValue());
					}
				}
				break;
		}



		//var_dump($_SESSION["ui"]["filter"][$filter_id]); exit;

		return $filter;

	}

	/**
	 * Get data
	 *
	 * @param
	 * @return
	 */
	public function getData(\ILIAS\UI\Component\Input\Container\Filter\Standard $filter)
	{
		global $DIC;
		$request = $DIC->http()->request();
		$result = null;
		if (in_array($_REQUEST["cmdFilter"], ["apply", "toggleOn", "expand", "collapse"]) && $request->getMethod() == "POST") {
			$filter = $filter->withRequest($request);
			$result = $filter->getData();
		}
		return $result;
	}


}