<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Container page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesContainer
 */
class ilContainerPageConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		global $ilSetting;

		$this->setEnableInternalLinks(true);
		$this->setIntLinkHelpDefaultType("RepositoryItem");
		$this->setEnablePCType("FileList", false);
		$this->setEnablePCType("Map", true);
		$this->setEnablePCType("Resources", true);
		$this->setMultiLangSupport(true);
		$this->setSinglePageMode(true);
	}
	
}

?>
