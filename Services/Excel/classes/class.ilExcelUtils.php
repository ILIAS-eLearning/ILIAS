<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Utilities for Microsoft Excel Import/Export
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
*/

class ilExcelUtils
{
	function _convert_text($a_text, $a_target = "has been removed")
	{
		return strip_tags($a_text); // #14542

		/* utf-8 is supported
		$a_text = preg_replace("/<[^>]*?>/", "", $a_text);
		return utf8_decode($a_text);
		 */
	}

} // END class.ilExcelUtils.php
?>
