<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIFilterService
{
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
		$this->_deps = $deps;
		$this->service = $service;
	}


	/**
	 * Get standard filter instance
	 *
	 * @param
	 * @return
	 */
	protected function standard($filter_id, $base_action, array $inputs, array $is_input_initially_rendered,
								$is_initially_activated = false, $is_initially_expanded = false): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		global $DIC;
		$ui = $DIC->ui()->factory();

		$is_input_rendered = $is_input_initially_rendered;
		$is_activated = $is_initially_activated;
		$is_expanded = $is_initially_expanded;

		// read cmdFilter from request and update session data

		if ($_REQUEST["cmdFilter"] == "expand")
		{
			ilSession::set("ui_service_filter_expanded_".$filter_id, 1);
		}

		// daten von session holen
		$is_expanded = ilSession::get("ui_service_filter_expanded");

		//Step 3: Define the filter and attach the inputs. The filter is initially activated in this case.
		$filter = $ui->input()->container()->filter()->standard(
			$base_action."&cmdFilter=toggleOn",
			$base_action."&cmdFilter=toggleOff",
			$base_action."&cmdFilter=expand",
			$base_action."&cmdFilter=collapse",
			$base_action."&cmdFilter=reset",
			$base_action."&cmdFilter=apply",
			$inputs,
			$is_input_rendered, true);

	}


}