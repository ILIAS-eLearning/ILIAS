<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\UIServices;

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
	 * @var UIServices
	 */
	protected $ui;

	/**
	 * Constructor
	 * @param UIServices
	 * @param ilUIFilterRequestAdapter $request
	 * @param ilUIFilterServiceSessionGateway|null $session
	 */
	public function __construct(UIServices $ui, ilUIFilterRequestAdapter $request, ilUIFilterServiceSessionGateway $session = null)
	{
		$this->ui = $ui;
		$this->request_adapter = $request;
		$this->session = (is_null($session))
			? new ilUIFilterServiceSessionGateway()
			: $session;
	}

	/**
	 * @return UIServices
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