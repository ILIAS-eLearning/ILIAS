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
* collection of predefind sort functions
* should only be included where you need it
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @package ilias-core
*/

/**
* sub-function to sort $languages-array by their long names
*
* Long names of languages depends on the chosen language setting by the current user
* The function is called only in this way: uasort($languages,"sortLanguagesByName");
*
* @param	array	$a	expect $languages
* @param	string	$b	the function name itself ('sortLanguagesByName')
*
* @return	boolean	true on success / false on error
*/
function sortLanguagesByName ($a, $b)
{
		return strcmp($a["name"], $b["name"]);
}

/**
* sub-function to sort an array
*
* @param	array	$a	
* @param	array	$b
*
* @return	boolean	true on success / false on error
*/
function sort_func ($a, $b)
{
	global $array_sortby,$array_sortorder;
	
	if ($array_sortorder == "ASC")
	{
		return strcasecmp($a[$array_sortby], $b[$array_sortby]);	
	}

	if ($array_sortorder == "DESC")
	{
		return strcasecmp($b[$array_sortby], $a[$array_sortby]);	
	}		
}

/**
* sortArray
*
* @param	array	array to sort
* @param	string	sort_column
* @param	string	sort_order (ASC or DESC)
*
* @return	array	sorted array
*/
function sortArray($array,$a_array_sortby,$a_array_sortorder = 0)
{
	global $array_sortby,$array_sortorder;

	$array_sortby = $a_array_sortby;
	
	if ($a_array_sortorder == "DESC")
	{
		$array_sortorder = "DESC";
	}
	else
	{
		$array_sortorder = "ASC";	
	}

	usort($array,"sort_func");
	
	return $array;
}

?>