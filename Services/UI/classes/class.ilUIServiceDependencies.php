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
	 * Constructor
	 * @param ilLanguage $lng
	 */
	public function __construct(ilUIFilterRequestAdapter $request, ilUIFilterServiceSessionGateway $session = null)
	{
		$this->request_adapter = $request;
		$this->session = (is_null($session))
			? new ilUIFilterServiceSessionGateway()
			: $session;
	}

	/**
	 * @return ilUIFilterRequestAdapter
	 */
	public function getRequest()
	{
		return $this->request_adapter;
	}

	/**
	 * @return ilUIFilterServiceSessionGateway
	 */
	public function getSession()
	{
		return $this->session;
	}

}