<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * View definition page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordViewViewdefinitionConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
        // config
		$this->setPreventHTMLUnmasking(true);
		$this->setEnableInternalLinks(false);
		$this->setEnableWikiLinks(false);						
		$this->setEnableActivation(false);
	}
	
}

?>
