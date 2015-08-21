<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilExportConfig.php");
/**
 * Export configuration for pages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPageExportConfig extends ilExportConfig
{
	protected $master_only = false;

	/**
	 * Set master language only
	 *
	 * @param bool $a_val export only master language
	 */
	function setMasterLanguageOnly($a_val)
	{
		$this->master_only = $a_val;
	}

	/**
	 * Get master language only
	 *
	 * @return bool export only master language
	 */
	function getMasterLanguageOnly()
	{
		return $this->master_only;
	}
}

?>