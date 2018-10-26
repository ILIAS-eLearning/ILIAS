<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter service. Wraps around KS filter container.
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIFilterService
{
	// command constants
	const CMD_TOGGLE_ON = "toggleOn";
	const CMD_TOGGLE_OFF = "toggleOff";
	const CMD_EXPAND = "expand";
	const CMD_COLLAPSE = "collapse";
	const CMD_APPLY = "apply";
	const CMD_RESET = "reset";


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
		$this->session = $deps->getSession();
		$this->request = $deps->getRequest();
	}


	/**
	 * Get standard filter instance
	 *
	 * @param string $filter_id
	 * @param string $base_action
	 * @param array $inputs
	 * @param array $is_input_initially_rendered
	 * @param bool $is_activated
	 * @param bool $is_expanded
	 * @return \ILIAS\UI\Component\Input\Container\Filter\Standard
	 */
	public function standard($filter_id, $base_action, array $inputs, array $is_input_initially_rendered,
							 $is_activated = false, $is_expanded = false): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		global $DIC;
		$ui = $DIC->ui()->factory();

		// write expand, activation, rendered inputs info to session
		$this->writeFilterStatusToSession($filter_id, $inputs);

		// handle the reset command
		$this->handleReset($filter_id);

		// determine activation/expand status
		$is_activated = $this->session->isActivated($filter_id, $is_activated);
		$is_expanded = $this->session->isExpanded($filter_id, $is_expanded);

		// put data from session into filter
		$inputs_with_session_data = [];
		$is_input_initially_rendered_with_session = [];
		foreach ($inputs as $input_id => $i)
		{
			// rendering information
			$rendered =
				$this->session->isRendered($filter_id, $input_id, current($is_input_initially_rendered));
			$is_input_initially_rendered_with_session[] = $rendered;
			next($is_input_initially_rendered);

			// values
			$val = $this->session->getValue($filter_id, $input_id);
			if ($rendered && !is_null($val))
			{
				$i = $i->withValue($val);
			}
			$inputs_with_session_data[$input_id] = $i;
		}

		// get the filter
		$filter = $ui->input()->container()->filter()->standard(
			$this->request->getAction($base_action, self::CMD_TOGGLE_ON),
			$this->request->getAction($base_action, self::CMD_TOGGLE_OFF),
			$this->request->getAction($base_action, self::CMD_EXPAND),
			$this->request->getAction($base_action, self::CMD_COLLAPSE),
			$this->request->getAction($base_action, self::CMD_APPLY),
			$this->request->getAction($base_action, self::CMD_RESET),
			$inputs_with_session_data,
			$is_input_initially_rendered_with_session,
			$is_activated,
			$is_expanded);

		// handle apply command
		$filter = $this->handleApply($filter_id, $filter);

		return $filter;

	}

	/**
	 * Get data
	 *
	 * @param \ILIAS\UI\Component\Input\Container\Filter\Standard $filter
	 * @return array|null
	 */
	public function getData(\ILIAS\UI\Component\Input\Container\Filter\Standard $filter)
	{
		$result = null;
		if (in_array($this->request->getFilterCmd(),
				[self::CMD_APPLY, self::CMD_TOGGLE_ON, self::CMD_EXPAND, self::CMD_COLLAPSE]) && $this->request->isPost()) {
			$filter = $this->request->getFilterWithRequest($filter);
			$result = $filter->getData();
		}
		return $result;
	}

	/**
	 * Write filter status to session (filter activated/expanded, inputs being rendered or not)
	 * @param string $filter_id
	 * @param array $inputs
	 */
	protected function writeFilterStatusToSession($filter_id, $inputs)
	{
		if ($this->request->getFilterCmd() == self::CMD_TOGGLE_ON) {
			$this->session->writeActivated($filter_id, true);
		}

		if ($this->request->getFilterCmd() == self::CMD_TOGGLE_OFF) {
			$this->session->writeActivated($filter_id, false);
		}

		if ($this->request->getFilterCmd() == self::CMD_EXPAND) {
			$this->session->writeExpanded($filter_id, true);
		}

		if ($this->request->getFilterCmd() == self::CMD_COLLAPSE) {
			$this->session->writeExpanded($filter_id, false);
		}

		if ($this->request->getFilterCmd() == self::CMD_APPLY)
		{
			foreach ($inputs as $input_id => $i)
			{
				if ($this->request->isInputRendered($input_id))
				{
					$this->session->writeRendered($filter_id, $input_id, true);
				}
				else
				{
					$this->session->writeRendered($filter_id, $input_id, false);
				}
			}
		}
	}

	/**
	 * Handle reset command
	 *
	 * @param string $filter_id
	 */
	protected function handleReset(string $filter_id)
	{
		// clear session, if reset is pressed
		if ($this->request->getFilterCmd() == self::CMD_RESET)
		{
			$this->session->reset($filter_id);
		}
	}


	/**
	 * Handle apply command
	 *
	 * @param string $filter_id
	 * @param \ILIAS\UI\Component\Input\Container\Filter\Standard $filter
	 * @return \ILIAS\UI\Component\Input\Container\Filter\Standard
	 */
	protected function handleApply(string $filter_id, \ILIAS\UI\Component\Input\Container\Filter\Standard $filter): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		if ($this->request->getFilterCmd() == self::CMD_APPLY &&
			$this->request->isPost())
		{
			$filter = $this->request->getFilterWithRequest($filter);
			foreach ($filter->getInputs() as $input_id => $i)
			{
				$this->session->writeValue($filter_id, $input_id, $i->getValue());
			}
		}
		return $filter;
	}



}