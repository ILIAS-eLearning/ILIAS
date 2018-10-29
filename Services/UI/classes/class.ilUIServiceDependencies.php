<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * UI service dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIServiceDependencies
{
	/**
	 * @var ilUIFilterRequestAdapter
	 */
	protected $request_adapter;

	/**
	 * @var ilUIFilterServiceSessionGateway
	 */
	protected $session;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * Constructor
	 * @param ilUIFilterRequestAdapter $request
	 * @param ilUIFilterServiceSessionGateway|null $session
	 */
	public function __construct(\ILIAS\DI\UIServices $ui, ilUIFilterRequestAdapter $request, ilUIFilterServiceSessionGateway $session = null)
	{
		$this->ui = $ui;
		$this->request_adapter = $request;
		$this->session = (is_null($session))
			? new ilUIFilterServiceSessionGateway()
			: $session;
	}

	/**
	 * @return \ILIAS\DI\UIServices
	 */
	public function ui()
	{
		return $this->ui;
	}

	/**
	 * @return ilUIFilterRequestAdapter
	 */
	public function getRequest(): ilUIFilterRequestAdapter
	{
		return $this->request_adapter;
	}

	/**
	 * @return ilUIFilterServiceSessionGateway
	 */
	public function getSession(): ilUIFilterServiceSessionGateway
	{
		return $this->session;
	}

}