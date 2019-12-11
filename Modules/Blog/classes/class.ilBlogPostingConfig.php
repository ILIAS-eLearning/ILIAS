<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Blog posting page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBlogPostingConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		$this->setEnablePCType("Map", true);
		$this->setEnableInternalLinks((bool)$_GET["ref_id"]); // #15668
		$this->setPreventHTMLUnmasking(false);
		$this->setEnableActivation(true);
		
		$blga_set = new ilSetting("blga");
		$this->setPreventHTMLUnmasking(!(bool)$blga_set->get("mask", false));
	}
	
}