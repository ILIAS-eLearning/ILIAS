<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* JSON (Javascript Object Notation) functions with backward compatibility
* for PHP version < 5.2
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*/
class ilJsonUtil
{
	public static function encode($mixed, $suppress_native = false)
	{
		if (!$suppress_native && self::checkNativeSupport())
		{
			return json_encode($mixed);
		}
		else
		{
			include_once './Services/JSON/include/json.php';
			$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
			return $json->encode($mixed);
		}
	}

	public static function decode($json_notated_string, $suppress_native = false)
	{
		if (!$suppress_native && self::checkNativeSupport())
		{
			return json_decode($json_notated_string);
		}
		else
		{
			include_once './Services/JSON/include/json.php';
			$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
			return $json->decode($json_notated_string);
		}
	}

	public static function checkNativeSupport()
	{
		return function_exists('json_encode') && function_exists('json_decode');
	}
}