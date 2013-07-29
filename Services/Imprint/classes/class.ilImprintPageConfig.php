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
class ilImprintPageConfig extends ilPageConfig
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
		$this->setPreventHTMLUnmasking(true);
		$this->setEnableInternalLinks(false);
		$this->setEnableWikiLinks(false);						
		$this->setEnableActivation(true);
	}
	
}

?>
