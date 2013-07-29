<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Media pool page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolPageConfig extends ilPageConfig
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
//		$this->setEnableMaps(false);
		$this->setEnableInternalLinks(false);
		$this->setPreventHTMLUnmasking(false);
	}
	
}

?>
