<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
* Internal Link: Repository Item Selector Explorer
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilIntLinkRepItemExplorerGUI extends ilRepositorySelectorExplorerGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");
		
		// #14587 - ilRepositorySelectorExplorerGUI::__construct() does NOT include side blocks!
		$list = $this->getTypeWhiteList();
		$list[] = "poll";
		$this->setTypeWhiteList($list);
	}


	/**
	 * get onclick event handling
	 */
	function getNodeOnClick($a_node)
	{
		return "return il.IntLink.addInternalLink('[iln ".$a_node['type']."=&quot;".$a_node['child']."&quot;]','[/iln]', event);";
	}

}
?>
