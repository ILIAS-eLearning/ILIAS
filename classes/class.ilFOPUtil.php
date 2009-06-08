<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
