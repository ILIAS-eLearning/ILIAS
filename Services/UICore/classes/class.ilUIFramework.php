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
	/**
	 * Get javascript files
	 *
	 * @return array array of files
	 */
	function getJSFiles()
	{
		return array("./Services/UICore/lib/bootstrap-3.2.0/dist/js/bootstrap.min.js");
	}

	/**
	 * Get javascript files
	 *
	 * @return array array of files
	 */
	function getCssFiles()
	{
		return array("./Services/UICore/lib/yamm3/yamm/yamm.css");
	}

	/**
	 * Init
	 *
	 * @param ilTemplate $a_tpl template object
	 */
	function init($a_tpl = null)
	{
		global $tpl;

		if ($a_tpl == null)
		{
			$a_tpl = $tpl;
		}

		foreach (ilUIFramework::getJSFiles() as $f)
		{
			$a_tpl->addJavaScript($f, true, 1);
		}
		foreach (ilUIFramework::getCssFiles() as $f)
		{
			$a_tpl->addCss($f);
		}
	}

}

?>