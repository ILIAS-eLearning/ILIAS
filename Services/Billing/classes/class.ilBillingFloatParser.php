<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBillingFloatParser
{
	/**
	 * @param string $str
	 * @return float|string
	 */
	public function getFloat($str)
	{
		if(strstr($str, ","))
		{
			$str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs
			$str = str_replace(",", ".", $str); // replace ',' with '.'
		}
		if($str < 0)
		{
			return 0.00;
		}
		else
		{

			return $str;
		}
	}

	/**
	 * @param string $str
	 * @return string
	 */
	public function getFloatForCoupons($str)
	{
		if(strstr($str, ","))
		{
			$str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs
			$str = str_replace(",", ".", $str); // replace ',' with '.'
		}
		return $str;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	public function getFloatForNegative($str)
	{
		if(strstr($str, ","))
		{
			$str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs
			$str = str_replace(",", ".", $str); // replace ',' with '.'
		}
		return $str;
	}
}
