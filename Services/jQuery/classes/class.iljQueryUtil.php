<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * jQuery utilities
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 *
 */
class iljQueryUtil {

	/**
	 * @var string Suffix for minified File
	 */
	private static $min = ".min";


	/**
	 * inits and adds the jQuery JS-File to the global or a passed template
	 *
	 * @param \ilTemplate $a_tpl global $tpl is used when null
	 */
	public static function initjQuery($a_tpl = null) {
		global $tpl;

		// self::$min = DEVMODE ? "" : ".min";
		self::$min = "";
		if ($a_tpl == null) {
			$a_tpl = $tpl;
		}

		$a_tpl->addJavaScript(self::getLocaljQueryPath(), true, 1);
	}


	/**
	 * inits and adds the jQuery-UI JS-File to the global template
	 * (see included_components.txt for included components)
	 */
	public static function initjQueryUI() {
		global $tpl;

		$tpl->addJavaScript(self::getLocaljQueryUIPath(), true, 1);
	}


	/**
	 * @return string local path of jQuery file
	 */
	public static function getLocaljQueryPath() {
		return "./libs/bower/bower_components/jquery/dist/jquery" . self::$min . ".js";
	}


	/**
	 * @return string local path of jQuery UI file
	 */
	public static function getLocaljQueryUIPath() {
		return "./libs/bower/bower_components/jquery-ui/jquery-ui" . self::$min . ".js";
	}

	//
	// Maphilight plugin
	//

	/**
	 * Inits and add maphilight to the general template
	 */
	public static function initMaphilight() {
		global $tpl;

		$tpl->addJavaScript(self::getLocalMaphilightPath(), true, 1);
	}


	/**
	 * Get local path of maphilight file
	 */
	public static function getLocalMaphilightPath() {
		return "./libs/bower/bower_components/maphilight/jquery.maphilight.min.js";
	}
}
