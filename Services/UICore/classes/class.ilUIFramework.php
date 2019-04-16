<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * UI framework utility class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUICore
 */
class ilUIFramework
{

	const BOWER_BOOTSTRAP_JS = "libs/bower/bower_components/bootstrap/dist/js/bootstrap.min.js";


	/**
	 * Get javascript files
	 *
	 * @return array array of files
	 */
	public static function getJSFiles()
	{
		return array( "./" . self::BOWER_BOOTSTRAP_JS );
	}

	/**
	 * Get javascript files
	 *
	 * @return array array of files
	 */
	static function getCssFiles()
	{
		return array("./libs/bower/bower_components/Yamm3/yamm/yamm.css");
	}


	/**
	 * Init
	 *
	 * @param ilGlobalTemplateInterface $a_tpl template object
	 */
	static function init(ilGlobalTemplateInterface $a_tpl = null)
	{
		global $DIC;

		if ($a_tpl == null) {
			$a_tpl = $DIC["tpl"];
		}

		foreach (ilUIFramework::getJSFiles() as $f) {
			$a_tpl->addJavaScript($f, true, 1);
		}
		foreach (ilUIFramework::getCssFiles() as $f) {
			$a_tpl->addCss($f);
		}
	}

}
