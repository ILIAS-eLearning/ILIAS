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
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->lng = $lng;
	}

	/**
	 * Get language object
	 *
	 * @return ilLanguage
	 */
	public function language()
	{
		return $this->lng;
	}

}