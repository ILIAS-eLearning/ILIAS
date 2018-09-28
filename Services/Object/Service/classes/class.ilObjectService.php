<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object service
 *
 * @author killing@leifos.de
 * @ingroup ServiceObject
 */
class ilObjectService
{
	/**

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

	/**
	 * Factory for learning history entries
	 *
	 * @return ilObjectCommonSettings
	 */
	public function commonSettings()
	{
		return new ilObjectCommonSettings($this);
	}
}