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
	public static function initjQuery(ilGlobalTemplateInterface $a_tpl = null) {
		global $DIC;

		self::$min = "";
		if ($a_tpl == null) {
			$a_tpl = $DIC["tpl"];
		}

		$a_tpl->addJavaScript(self::getLocaljQueryPath(), true, 0);
		$a_tpl->addJavaScript('./libs/bower/bower_components/jquery-migrate/jquery-migrate.min.js', true, 0);
	}


	/**
	 * inits and adds the jQuery-UI JS-File to the global template
	 * (see included_components.txt for included components)
	 */
	public static function initjQueryUI($a_tpl = null) {
		global $DIC;

		if ($a_tpl == null) {
			$a_tpl = $DIC["tpl"];
		}

		// Important: jQueryUI has to be included before(!) the bootstrap JS file
		$a_tpl->addJavaScript(self::getLocaljQueryUIPath(), true, 0);
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
		global $DIC;

		$tpl = $DIC["tpl"];

		$tpl->addJavaScript(self::getLocalMaphilightPath(), true, 1);
	}


	/**
	 * Get local path of maphilight file
	 */
	public static function getLocalMaphilightPath() {
		return "./libs/bower/bower_components/maphilight/jquery.maphilight.min.js";
	}
}
