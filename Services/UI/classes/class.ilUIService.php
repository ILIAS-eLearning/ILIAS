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
	 * @var \Psr\Http\Message\ServerRequestInterface
	 */
	protected $request;

	/**
	 * Constructor
	 * @param ilLanguage $lng
	 */
	public function __construct(\Psr\Http\Message\ServerRequestInterface $request)
	{
		$this->_deps = new ilUIServiceDependencies(new ilUIFilterRequestAdapter($request));
	}

	/**
	 * @inheritdoc
	 */
	public function filter(): ilUIFilterService
	{
		return new ilUIFilterService($this, $this->_deps);
	}
}