<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Wiki page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesWiki
 */
class ilWikiPageConfig extends ilPageConfig
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
		$this->setEnablePCType("Tabs", true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnableInternalLinks(true);
		$this->setEnableAnchors(true);
		$this->setEnableWikiLinks(true);
		$this->setIntLinkFilterWhiteList(true);
		$this->addIntLinkFilter("RepositoryItem");
		$this->setIntLinkHelpDefaultType("RepositoryItem");
	}
	
}

?>
