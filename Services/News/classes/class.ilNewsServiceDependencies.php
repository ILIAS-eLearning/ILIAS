<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServiceNews
 */
class ilNewsServiceDependencies
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * Constructor
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng, ilSetting $settings)
	{
		$this->lng = $lng;
		$this->settings = $settings;
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

	/**
	 * Get settings object
	 *
	 * @return ilSetting
	 */
	public function settings()
	{
		return $this->settings;
	}
}