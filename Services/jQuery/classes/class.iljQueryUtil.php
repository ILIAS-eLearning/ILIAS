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
	private static $ver = "1_11_1"; 
	private static $ui_ver = "1_11_1";
	private static $maphilight_ver = "14_03_20";
	private static $min = "-min";
	
	/**
	 * Init jQuery
	 */
	static function initjQuery($a_tpl = null)
	{
		global $tpl;
		
		if ($a_tpl == null)
		{
			$a_tpl = $tpl;
		}

		$a_tpl->addJavaScript(self::getLocaljQueryPath(), true, 1);
		
		// adding jquery-migrate for >= 1.9.x
		$major = explode("_", self::$ver);
		$major = $major[0]*100+$major[1];
		if($major >= 109)
		{
			$path = str_replace("jquery", "jquery-migrate", self::getLocaljQueryPath());
			
			// this will enable console error logging!
			if(DEVMODE)
			{
				$path = str_replace(self::$min, "", $path);
			}
			
			$a_tpl->addJavaScript($path, true, 1);
		}
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

 	//
 	// Maphilight plugin
 	//
 	
 	/**
	 * Init maphilight
	 */
	static function initMaphilight()
	{
		global $tpl;
		
		 $tpl->addJavaScript(self::getLocalMaphilightPath(), true, 1);
	}

 	 /**
	 * Get local path of maphilight file 
	 */
	function getLocalMaphilightPath()
	{
		return "./Services/jQuery/js/maphilight_".self::$maphilight_ver."/maphilight.js";
 	}

}
?>