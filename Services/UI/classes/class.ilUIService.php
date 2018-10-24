<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service
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
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->_deps = new ilUIServiceDependencies($lng);
	}

	/**
	 * @inheritdoc
	 */
	public function filter(): ilUIFilterService
	{
		return new ilUIFilterService($this, $this->_deps);
	}
}