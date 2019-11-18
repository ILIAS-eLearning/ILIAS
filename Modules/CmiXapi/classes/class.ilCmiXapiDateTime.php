<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiDateTime
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiDateTime extends ilDateTime
{
	// DateTime::RFC3339_EXTENDED resolves to Y-m-d\TH:i:s.vP
	// note the v at the end -> this works with PHP 7.3
	// but not with PHP 7.2, 7.1 and probably not with versions below

	const RFC3336_EXTENDED_FIXED_USING_u_INSTEAD_OF_v = 'Y-m-d\TH:i:s.uP';
	
	/**
	 * @return string
	 * @throws Exception
	 */
	public function toXapiTimestamp()
	{
		$phpDateTime = new DateTime();
		$phpDateTime->setTimestamp($this->get(IL_CAL_UNIX));
		
		return $phpDateTime->format(self::RFC3336_EXTENDED_FIXED_USING_u_INSTEAD_OF_v);
	}
	
	/**
	 * @param string $xapiTimestamp
	 * @return ilCmiXapiDateTime
	 * @throws ilDateTimeException
	 */
	public static function fromXapiTimestamp($xapiTimestamp)
	{
		$phpDateTime = DateTime::createFromFormat(
			self::RFC3336_EXTENDED_FIXED_USING_u_INSTEAD_OF_v, $xapiTimestamp
		);
		
		$unixTimestamp = $phpDateTime->getTimestamp();

		return new self($unixTimestamp, IL_CAL_UNIX);
	}
	
	/**
	 * @param ilDateTime $dateTime
	 * @return ilCmiXapiDateTime
	 * @throws ilDateTimeException
	 */
	public static function fromIliasDateTime(ilDateTime $dateTime)
	{
		return new self($dateTime->get(IL_CAL_UNIX), IL_CAL_UNIX);
	}
}
