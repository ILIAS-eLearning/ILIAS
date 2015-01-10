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
	 * Init
	 */
	function init()
	{
		$this->setEnablePCType("Map", true);
		$this->setEnablePCType("Tabs", true);
		$this->setPreventHTMLUnmasking(true);
		$this->setEnableInternalLinks(true);
		$this->setEnableAnchors(true);
		$this->setEnableWikiLinks(true);
		$this->setIntLinkFilterWhiteList(true);
		$this->addIntLinkFilter("RepositoryItem");
		$this->addIntLinkFilter("WikiPage");
		$this->setIntLinkHelpDefaultType("RepositoryItem");
		$this->setEnablePCType("AMDPageList", true);
	}
	
	/**
	 * Object specific configuration 
	 *
	 * @param int $a_obj_id object id
	 */
	function configureByObjectId($a_obj_id)
	{
		if ($a_obj_id > 0)
		{
			include_once("./Modules/Wiki/classes/class.ilObjWiki.php");
			$this->setEnablePageToc(ilObjWiki::_lookupPageToc($a_obj_id));
		}
	}

}

?>
