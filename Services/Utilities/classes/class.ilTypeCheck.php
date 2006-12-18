<?php
/*
+-----------------------------------------------------------------------------+
| ILIAS open source                                                           |
+-----------------------------------------------------------------------------+
| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Type checking functions.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilTypeCheck
{
	/**
	* Check input.
	*
	* @param	string		type
	* @param	mixed		value
	* @param	boolean		required
	*/
	function check($a_type, $a_value, $a_required = false,
		$a_min = "", $a_max = "")
	{
		global $lng;
		
		$ok = true;
		
		switch ($a_type)
		{
			case "varchar":
			case "text":
				if ($a_required && $a_value == "")
				{
					$err = $lng->txt("msg_input_is_required");
				}
				break;
				
			case "int":
				break;
				
			case "datetime":
				if ($a_required && $a_value == "")
				{
					$err = $lng->txt("msg_input_is_required");
				}
				break;

			case "boolean":
			case "enum":
				break;
				
			default:
				die ("ERROR: ilTypeCheck::check: Type '".$a_type."' unknown.");
				break;
		}
		
		if ($err != "")
		{
			$ok = false;
		}
		
		return array("ok" => $ok, "error" => $err);
	}
}
?>
