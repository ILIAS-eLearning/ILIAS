<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\UIServices;
use \Psr\Http\Message\ServerRequestInterface;

/**
 * Filter service
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIService
{
	/**
	 * @var ilUIServiceDependencies
	 */
	protected $_deps;


	/**
	 * Constructor
	 *
	 * @param ServerRequestInterface $request
	 */
	public function __construct(ServerRequestInterface $request, UIServices $ui)
	{
		$this->_deps = new ilUIServiceDependencies($ui, new ilUIFilterRequestAdapter($request));
	}

	/**
	 * @return ilUIFilterService
	 */
	public function filter(): ilUIFilterService
	{
		return new ilUIFilterService($this, $this->_deps);
	}
}