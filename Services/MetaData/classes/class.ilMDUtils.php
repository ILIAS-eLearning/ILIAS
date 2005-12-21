<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Utility class for meta data handling
*
* @author Stefan Meyer <smeyer@databay.de>
* @package ilias-core
* @version $Id$
*/
class ilMDUtils
{
	/**
	 * LOM datatype duration is a string like HH:MM:SS
	 * This function tries to parse a given string in an array of hours, minutes and seconds
	 *
	 * @param string string to parse
	 * @return array  e.g array(0,1,2) => 0 hours, 1 minute, 2 seconds or false if not parsable
	 *
	 */
	function _LOMDurationToArray($a_string)
	{
		$a_string = trim($a_string);
		$pattern = '/^(PT)?(\d{1,2}H)?(\d{1,2}M)?(\d{1,2}S)?$/i';

		if(!preg_match($pattern,$a_string,$matches))
		{
			#var_dump("<pre>",$matches,"<pre>");
			return false;
		}
		if(preg_match('/(\d+)+H/i',$a_string,$matches))
		{
			#var_dump("<pre>",$matches,"<pre>");
			$hours = $matches[1];
		}
		if(preg_match('/(\d+)M/i',$a_string,$matches))
		{
			#var_dump("<pre>",$matches,"<pre>");
			$min = $matches[1];
		}
		if(preg_match('/(\d+)S/i',$a_string,$matches))
		{
			#var_dump("<pre>",$matches,"<pre>");
			$sec = $matches[1];
		}
		
		// Hack for zero values
		if(!$hours and !$min and !$sec)
		{
			return false;
		}
		
		return array($hours,$min,$sec);
	}


}