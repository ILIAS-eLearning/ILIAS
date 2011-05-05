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
	private static $ui_ver = "1_8_12";
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
	 * Init jQuery UI (see included_components.txt for included components)
	 */
	static function initjQueryUI()
	{
		global $tpl;
		
		 $tpl->addJavaScript(self::getLocaljQueryUIPath(), true, 1);
	}
	
	/**
	 * Get local path of jQuery file
	 */
	function getLocaljQueryPath()
	{
		return "./Services/jQuery/js/".self::$ver."/jquery".self::$min.".js";
 	}

 	/**
	 * Get local path of jQuery UI file 
	 */
	function getLocaljQueryUIPath()
	{
		return "./Services/jQuery/js/ui_".self::$ui_ver."/jquery-ui.min.js";
 	}

}
?>