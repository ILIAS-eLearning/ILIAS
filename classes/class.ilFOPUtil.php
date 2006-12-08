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
* utility functions for xml-fo
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilFOPUtil
{

	/**
	* get fop command
	*/
	function getFOPCmd()
	{
		return PATH_TO_FOP;
	}

	/**
	* convert fo file to pdf file
	*
	* @param	string		$a_from				source fo file
	* @param	string		$a_to				target pdf file
	*/
	function makePDF($a_from, $a_to)
	{
		$saved = getenv("JAVACMD");        // save old value
		putenv("JAVACMD=".PATH_TO_JAVA);

		$fop_cmd = ilFOPUtil::getFOPCmd()." -fo ".
			$a_from." -pdf ".$a_to;
//echo $fop_cmd."<br>:";

		//error_reporting(E_ALL);

		/* Add redirection so we can get stderr. */
		$handle = popen($fop_cmd.' 2>&1', 'r');
echo "'$handle'; " . gettype($handle) . "\n";
		$read = fread($handle, 2096);
echo $read;
		pclose($handle);

		$ret = exec($fop_cmd, $arr, $r2);
echo $ret.$r2;
echo ":";
var_dump($arr);
		putenv("JAVACMD=$saved");
	}

} // END class.ilFOPUtil
?>
