<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * jQuery utilities
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class iljQueryUtil
{
	private static $ver = "1_4_4";
	private static $min = "-min";
	
	/**
	 * Init jQuery
	 */
	static function initjQuery()
	{
		global $tpl;
		
		 $tpl->addJavaScript(self::getLocaljQueryPath(), true, 1);
	}
	
	/**
	 * Get local path of a YUI js file
	 */
	function getLocaljQueryPath()
	{
		return "./Services/jQuery/js/".self::$ver."/jquery".self::$min.".js";
 	}

}
?>