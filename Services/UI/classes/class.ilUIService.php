<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 */
	public function __construct(\Psr\Http\Message\ServerRequestInterface $request)
	{
		$this->_deps = new ilUIServiceDependencies(new ilUIFilterRequestAdapter($request));
	}

	/**
	 * @return ilUIFilterService
	 */
	public function filter(): ilUIFilterService
	{
		return new ilUIFilterService($this, $this->_deps);
	}
}