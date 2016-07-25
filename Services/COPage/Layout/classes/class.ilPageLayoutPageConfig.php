<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Page layout page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesStyle
 */
class ilPageLayoutPageConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		global $ilSetting;
		
		$this->setPreventHTMLUnmasking(false);
		$this->setEnableInternalLinks(false);
		$this->setEnablePCType("Question", false);
		$this->setEnablePCType("Map", false);
		$this->setEnablePCType("FileList", false);
		$this->setEnablePCType("PlaceHolder", true);
	}
	
}

?>
