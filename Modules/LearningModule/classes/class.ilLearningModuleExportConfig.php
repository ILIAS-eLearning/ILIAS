<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export configuration for learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLearningModuleExportConfig extends ilExportConfig
{
	protected $master_only = false;
	protected $include_media = true;

	/**
	 * Set master language only
	 *
	 * @param bool $a_val export only master language
	 */
	function setMasterLanguageOnly($a_val, $a_include_media = true)
	{
		$this->master_only = $a_val;
		$this->include_media = $a_include_media;
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

	/**
	 * Get include media
	 *
	 * @return bool export media?
	 */
	function getIncludeMedia()
	{
		return $this->include_media;
	}

}

?>