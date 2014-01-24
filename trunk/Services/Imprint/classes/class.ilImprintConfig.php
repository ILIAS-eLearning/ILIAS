<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Imprint page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesImprint
 */
class ilImprintConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		$this->setPreventHTMLUnmasking(true);
		$this->setEnableInternalLinks(false);
		$this->setEnableWikiLinks(false);						
		$this->setEnableActivation(true);
	}
	
}

?>
