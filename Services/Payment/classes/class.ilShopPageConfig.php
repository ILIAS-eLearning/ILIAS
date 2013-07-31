<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Shop page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesPayment
 */
class ilShopPageConfig extends ilPageConfig
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		parent::__construct();
		$this->setEnablePCType("Map", true);
		
//		$page_gui->setEnabledRepositoryObjects(false);
//		$page_gui->setEnabledFileLists(true);
//		$page_gui->setEnabledMaps(true);
//		$page_gui->setEnabledPCTabs(true);

	}
	
}

?>
