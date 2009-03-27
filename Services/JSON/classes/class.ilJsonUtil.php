<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


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
			include_once '../include/json.php';
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
			include_once '../include/json.php';
			$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
			return $json->decode($json_notated_string);
		}
	}

	public static function checkNativeSupport()
	{
		return function_exists('json_encode') && function_exists('json_decode');
	}
}