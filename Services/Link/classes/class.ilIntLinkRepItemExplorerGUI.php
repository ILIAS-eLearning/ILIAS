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
	 * Set "set link target" script
	 *
	 * @param <type> $a_script
	 */
	function setSetLinkTargetScript($a_script)
	{
		$this->link_target_script = $a_script;
	}

	/**
	 * Get "set link target" script
	 */
	function getSetLinkTargetScript()
	{
		return $this->link_target_script;
	}

	function getNodeHref($a_node)
	{
		if ($this->getSetLinkTargetScript() == "")
		{
			return "#";
		}
		else
		{
			$link =
				ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
					"linktype=RepositoryItem".
					"&linktarget=il__".$a_node["type"]."_".$a_node["child"]);

			return ($link);
		}
	}

	/**
	 * get onclick event handling
	 */
	function getNodeOnClick($a_node)
	{
		if ($this->getSetLinkTargetScript() == "")
		{
			return "return il.IntLink.addInternalLink('[iln ".$a_node['type']."=&quot;".$a_node['child']."&quot;]','[/iln]', event);";
		}
		return "";
	}

}
?>
