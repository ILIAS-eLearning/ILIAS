<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\BookingManager;

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class getObjectSettingsResponse
{
	/**
	 * @var \ilObjBookingServiceSettings
	 */
	protected $settings;

	/**
	 * Constructor
	 * @param \ilObjBookingServiceSettings $settings
	 */
	public function __construct(\ilObjBookingServiceSettings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Get settings
	 *
	 * @return \ilObjBookingServiceSettings
	 */
	public function getSettings(): \ilObjBookingServiceSettings
	{
		return $this->settings;
	}
}