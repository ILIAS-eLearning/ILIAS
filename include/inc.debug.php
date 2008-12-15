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
* debugging functions
* 
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-develop
*/

/**
* shortcut for var_dump
* @access	public
* @param	mixed	any number of parameters
*/
function vd()
{
	$numargs = func_num_args();

	if ($numargs == 0)
	{
		return false;
	}
	
	$arg_list = func_get_args();
	$num = 1;

	
	foreach ($arg_list as $arg)
	{
		echo "<pre>variable ".$num.":<br/>";
		var_dump($arg);
		echo "</pre><br/>";
		$num++;
	}
}

function pr($var,$name = '')
{
	if($name != '') $name .= ' = ';
	echo '<pre>'.$name.print_r($var,true).'</pre>';
}

?>