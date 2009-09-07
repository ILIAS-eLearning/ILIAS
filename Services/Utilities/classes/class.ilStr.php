<?php
/*
+-----------------------------------------------------------------------------+
| ILIAS open source                                                           |
+-----------------------------------------------------------------------------+
| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Multi byte sensitive string functions
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*/
class ilStr
{
	function subStr($a_str, $a_start, $a_length = NULL)
	{
		if (function_exists("mb_substr"))
		{
			return mb_substr($a_str, $a_start, $a_length, "UTF-8");
		}
		else
		{
			return substr($a_str, $a_start, $a_length);
		}
	}
	
	function strPos($a_haystack, $a_needle, $a_offset = NULL)
	{
		if (function_exists("mb_strpos"))
		{
			return mb_strpos($a_haystack, $a_needle, $a_offset, "UTF-8");
		}
		else
		{
			return strpos($a_haystack, $a_needle, $a_offset);
		}		
	}
	
	function strLen($a_string)
	{
		if (function_exists("mb_strlen"))
		{
			return mb_strlen($a_string, "UTF-8");
		}
		else
		{
			return strlen($a_string);
		}		
	}

	function strToLower($a_string)
	{
		if (function_exists("mb_strtolower"))
		{
			return mb_strtolower($a_string, "UTF-8");
		}
		else
		{
			return strtolower($a_string);
		}		
	}

	function strToUpper($a_string)
	{
		if (function_exists("mb_strtoupper"))
		{
			return mb_strtoupper($a_string, "UTF-8");
		}
		else
		{
			return strtoupper($a_string);
		}		
	}
	
	/**
	* Compare two strings
	*/
	function strCmp($a, $b)
	{
//echo "<br>-$a-$b-".strcoll(ilStr::strToUpper($a), ilStr::strToUpper($b))."-";
		return (strcoll(ilStr::strToUpper($a), ilStr::strToUpper($b)) > 0);
	}
	
	/**
	 * Shorten text to the given number of bytes.
	 * If the character is cutted within a character
	 * the invalid character will be shortened, too.
	 * 
	 * E.g: shortenText('€€€',4) will return '€'
	 * 
	 * @param string $a_string
	 * @param int $a_start_pos
	 * @param int $a_num_bytes
	 * @param string $a_encoding [optional]
	 * @return string
	 */
	public static function shortenText($a_string,$a_start_pos,$a_num_bytes,$a_encoding = 'UTF-8')
	{
		return mb_strcut($a_string, $a_start_pos, $a_num_bytes, $a_encoding);		
	}

	

} // END class.ilUtil
?>
